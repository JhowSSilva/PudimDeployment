<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InstanceRegistrationToken;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InstanceRegistrationController extends Controller
{
    // Authenticated endpoint to create a short-lived registration token
    public function store(Request $request)
    {
        // Allow optional server fields to be included even if server isn't created yet
        $request->validate([
            'deploy_user' => 'nullable|string|max:32',
            'name' => 'nullable|string|max:255',
            'ip_address' => 'nullable|ip',
            'ssh_port' => 'nullable|integer|min:1|max:65535',
            'os' => 'nullable|string|max:64',
            'type' => 'nullable|string|max:64',
            'programming_language' => 'nullable|string|max:32',
            'language_version' => 'nullable|string|max:32',
        ]);

        $user = $request->user();
        // currentTeam() may be a relation or a property depending on middleware, normalize to model
        $team = $user?->currentTeam ?? (method_exists($user, 'currentTeam') ? $user->currentTeam() : null);
        if (is_object($team) && method_exists($team, 'first')) {
            $team = $team->first();
        }

        // create token with meta from optional server fields
        $meta = array_filter($request->only(['name', 'ip_address', 'ssh_port', 'os', 'type', 'programming_language', 'language_version']));

        $token = InstanceRegistrationToken::create([
            'token' => \Illuminate\Support\Str::random(40),
            'user_id' => $user?->id,
            'team_id' => $team?->id,
            'expires_at' => now()->addMinutes(config('servers.registration_ttl_minutes', 60 * 24)),
            'meta' => $meta ?: null,
        ]);

        $appUrl = rtrim(config('app.url'), '/');
        // route is defined under the 'servers.' named group
        $bootstrapUrl = $appUrl . route('servers.bootstrap', [], false) . '?token=' . $token->token;

        // One-line command the user runs on their instance (downloads and runs the script)
        $command = "curl -fsSL '" . $bootstrapUrl . "' | sudo bash -s -- " . ($request->input('deploy_user', 'deploy'));

        return response()->json([
            'token' => $token->token,
            'expires_at' => $token->expires_at?->toIso8601String(),
            'command' => $command,
            'bootstrap_url' => $bootstrapUrl,
            'meta' => $token->meta,
        ]);
    }

    // Serve a small bootstrap script (text/plain) that will create the sudo user, generate keys and register via API
    public function bootstrapScript(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return response('Missing token', 400);
        }

        $appUrl = rtrim(config('app.url'), '/');

        $script = <<<'BASH'
#!/usr/bin/env bash
set -euo pipefail

REG_TOKEN="%s"
DEPLOY_USER="${1:-deploy}"
API_URL="%s/api/instances/register"
SSH_PORT="${2:-22}"

# Helpers
command_exists() { command -v "$1" >/dev/null 2>&1; }

# Detect package manager
if command_exists apt-get; then
  PKG_CMD="apt-get"
  INSTALL_CMD="apt-get update -y && apt-get install -y curl jq openssh-server"
elif command_exists dnf; then
  PKG_CMD="dnf"
  INSTALL_CMD="dnf install -y curl jq openssh-server"
elif command_exists yum; then
  PKG_CMD="yum"
  INSTALL_CMD="yum install -y curl jq openssh-server"
else
  echo "Unsupported distro or package manager. Please install curl, jq and openssh-server manually." >&2
fi

if [ -n "${INSTALL_CMD:-}" ]; then
  echo "Installing prerequisites..."
  eval "$INSTALL_CMD"
fi

# Create user if missing
if id -u "$DEPLOY_USER" >/dev/null 2>&1; then
  echo "User $DEPLOY_USER already exists."
else
  echo "Creating user $DEPLOY_USER..."
  useradd -m -s /bin/bash "$DEPLOY_USER"
fi

# Ensure .ssh dir exists
mkdir -p /home/$DEPLOY_USER/.ssh
chown $DEPLOY_USER:$DEPLOY_USER /home/$DEPLOY_USER/.ssh
chmod 700 /home/$DEPLOY_USER/.ssh

# Generate key pair (ed25519)
KEY_PATH="/home/$DEPLOY_USER/.ssh/id_ed25519"
if [ -f "$KEY_PATH" ]; then
  echo "Key already exists at $KEY_PATH"
else
  echo "Generating key pair for $DEPLOY_USER..."
  sudo -u $DEPLOY_USER ssh-keygen -t ed25519 -f "$KEY_PATH" -N '' -C "pudim-$(hostname -f)"
fi

PUB_KEY=$(cat "$KEY_PATH.pub")

# Add to authorized_keys
grep -qxF "$PUB_KEY" /home/$DEPLOY_USER/.ssh/authorized_keys || echo "$PUB_KEY" >> /home/$DEPLOY_USER/.ssh/authorized_keys
chown $DEPLOY_USER:$DEPLOY_USER /home/$DEPLOY_USER/.ssh/authorized_keys
chmod 600 /home/$DEPLOY_USER/.ssh/authorized_keys

# Add sudoers entry (passwordless)
if [ ! -f /etc/sudoers.d/$DEPLOY_USER ]; then
  echo "$DEPLOY_USER ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/$DEPLOY_USER
  chmod 440 /etc/sudoers.d/$DEPLOY_USER
fi

# Determine hostname and IP
HOSTNAME=$(hostname -f || hostname)
IP=$(curl -s https://ifconfig.co || hostname -I | awk '{print $1}')

# Register with control plane
echo "Registering instance with control plane..."
RESPONSE=$(curl -sS -w "\n%{http_code}" -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -H "X-Registration-Token: $REG_TOKEN" \
  -d "{\"hostname\": \"${HOSTNAME}\", \"ip_address\": \"${IP}\", \"ssh_port\": ${SSH_PORT}, \"deploy_user\": \"${DEPLOY_USER}\", \"ssh_public_key\": \"${PUB_KEY//\"/\\\"}\" }")

# Split body and status
HTTP_STATUS=$(echo "$RESPONSE" | tail -n1)
BODY=$(echo "$RESPONSE" | sed '$d')

if [ "$HTTP_STATUS" -ge 200 ] && [ "$HTTP_STATUS" -lt 300 ]; then
  echo "Registration successful. Response:"
  echo "$BODY"
  echo ""
  echo "==== PRIVATE KEY (save it now, it will not be shown again) ===="
  cat "$KEY_PATH"
  echo "==== END PRIVATE KEY ===="
  echo "The key file is present at $KEY_PATH; please store it safely and/or move it to your workstation."
else
  echo "Registration failed (HTTP $HTTP_STATUS):"
  echo "$BODY" >&2
  exit 1
fi
BASH;

        $script = sprintf($script, $token, $appUrl);

        return response($script, 200)->header('Content-Type', 'text/plain');
    }

    // Public API endpoint called by bootstrap script
    public function register(Request $request)
    {
        $tokenHeader = $request->header('X-Registration-Token') ?? $request->input('token');

        if (!$tokenHeader) {
            return response()->json(['error' => 'missing token'], 400);
        }

        $token = InstanceRegistrationToken::where('token', $tokenHeader)->first();

        if (!$token || !$token->isValid()) {
            return response()->json(['error' => 'invalid or expired token'], 403);
        }

        // Merge incoming payload with token meta so pre-filled fields satisfy validation
        $mergedPayload = array_merge($token->meta ?? [], $request->all());

        $validator = Validator::make($mergedPayload, [
            'hostname' => 'sometimes|string|max:255',
            'ip_address' => 'required|ip',
            'ssh_port' => 'sometimes|integer|min:1|max:65535',
            'deploy_user' => 'sometimes|string|max:32',
            'ssh_public_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Merge token meta (if any) so fields from the original form pre-fill the created server
        $meta = $token->meta ?? [];

        $serverData = array_merge($meta, [
            'team_id' => $token->team_id,
            'user_id' => $token->user_id,
            'name' => $data['hostname'] ?? ($meta['name'] ?? 'manual-' . now()->format('YmdHis')),
            'ip_address' => $data['ip_address'] ?? ($meta['ip_address'] ?? null),
            'ssh_port' => $data['ssh_port'] ?? ($meta['ssh_port'] ?? 22),
            'deploy_user' => $data['deploy_user'] ?? ($meta['deploy_user'] ?? 'deploy'),
            'ssh_key_public' => $data['ssh_public_key'],
            'status' => 'online',
            'provision_status' => 'active',
        ]);

        $server = Server::create($serverData);

        // mark token used
        $token->markUsed();

        return response()->json(['server' => $server->toArray()], 201);
    }
}
