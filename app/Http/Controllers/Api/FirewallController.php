<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Services\FirewallService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FirewallController extends Controller
{
    /**
     * Configure UFW firewall
     */
    public function configure(Server $server): JsonResponse
    {
        $this->authorize('manage', $server);

        $firewall = new FirewallService($server);
        $result = $firewall->configureUFW();

        return response()->json($result);
    }

    /**
     * Add firewall rule
     */
    public function addRule(Request $request, Server $server): JsonResponse
    {
        $this->authorize('manage', $server);

        $validated = $request->validate([
            'port' => 'required|integer|min:1|max:65535',
            'protocol' => 'required|in:tcp,udp',
            'source' => 'nullable|ip',
            'comment' => 'nullable|string|max:255',
        ]);

        $firewall = new FirewallService($server);
        $result = $firewall->addRule(
            $validated['port'],
            $validated['protocol'],
            $validated['source'] ?? null,
            $validated['comment'] ?? null
        );

        return response()->json($result);
    }

    /**
     * Remove firewall rule
     */
    public function removeRule(Request $request, Server $server): JsonResponse
    {
        $this->authorize('manage', $server);

        $validated = $request->validate([
            'port' => 'required|integer',
            'protocol' => 'required|in:tcp,udp',
        ]);

        $firewall = new FirewallService($server);
        $result = $firewall->removeRule($validated['port'], $validated['protocol']);

        return response()->json($result);
    }

    /**
     * Block IP address
     */
    public function blockIp(Request $request, Server $server): JsonResponse
    {
        $this->authorize('manage', $server);

        $validated = $request->validate([
            'ip' => 'required|ip',
            'comment' => 'nullable|string|max:255',
        ]);

        $firewall = new FirewallService($server);
        $result = $firewall->blockIP($validated['ip'], $validated['comment'] ?? null);

        return response()->json($result);
    }

    /**
     * Unblock IP address
     */
    public function unblockIp(Request $request, Server $server): JsonResponse
    {
        $this->authorize('manage', $server);

        $validated = $request->validate([
            'ip' => 'required|ip',
        ]);

        $firewall = new FirewallService($server);
        $result = $firewall->unblockIP($validated['ip']);

        return response()->json($result);
    }

    /**
     * Get active firewall rules
     */
    public function getRules(Server $server): JsonResponse
    {
        $this->authorize('view', $server);

        $firewall = new FirewallService($server);
        $result = $firewall->getActivePorts();

        return response()->json($result);
    }

    /**
     * Enable Fail2ban
     */
    public function enableFail2ban(Server $server): JsonResponse
    {
        $this->authorize('manage', $server);

        $firewall = new FirewallService($server);
        $result = $firewall->enableFail2Ban();

        return response()->json($result);
    }

    /**
     * Get banned IPs from Fail2ban
     */
    public function getBannedIps(Server $server): JsonResponse
    {
        $this->authorize('view', $server);

        $firewall = new FirewallService($server);
        $result = $firewall->getBannedIPs();

        return response()->json($result);
    }

    /**
     * Get firewall status
     */
    public function getStatus(Server $server): JsonResponse
    {
        $this->authorize('view', $server);

        $firewall = new FirewallService($server);
        $result = $firewall->getStatus();

        return response()->json($result);
    }
}
