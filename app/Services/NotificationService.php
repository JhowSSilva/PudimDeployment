<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Create a notification for a user
     */
    public function create(
        User $user,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?string $actionUrl = null,
        ?string $actionText = null,
        ?Team $team = null
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'team_id' => $team?->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
        ]);
    }

    /**
     * Create deployment notification
     */
    public function deployment(User $user, string $siteName, string $status, ?string $url = null): Notification
    {
        $title = $status === 'success' 
            ? "Deploy concluído com sucesso"
            : "Deploy falhou";
            
        $message = $status === 'success'
            ? "O site {$siteName} foi implantado com sucesso."
            : "O deploy do site {$siteName} falhou. Verifique os logs.";

        $type = $status === 'success' ? 'success' : 'error';

        return $this->create($user, $type, $title, $message, null, $url, 'Ver detalhes');
    }

    /**
     * Create security notification
     */
    public function security(User $user, string $serverName, string $threat, ?string $url = null): Notification
    {
        return $this->create(
            $user,
            'security',
            "Ameaça de segurança detectada",
            "Detectada ameaça no servidor {$serverName}: {$threat}",
            null,
            $url,
            'Ver servidor'
        );
    }

    /**
     * Create server offline notification
     */
    public function serverOffline(User $user, string $serverName, ?string $url = null): Notification
    {
        return $this->create(
            $user,
            'error',
            "Servidor offline",
            "O servidor {$serverName} está offline ou inacessível.",
            null,
            $url,
            'Ver servidor'
        );
    }

    /**
     * Create SSL expiring notification
     */
    public function sslExpiring(User $user, string $domain, int $daysLeft, ?string $url = null): Notification
    {
        return $this->create(
            $user,
            'warning',
            "Certificado SSL expirando",
            "O certificado SSL para {$domain} expira em {$daysLeft} dias.",
            null,
            $url,
            'Renovar SSL'
        );
    }

    /**
     * Create backup completed notification
     */
    public function backupCompleted(User $user, string $databaseName, ?string $size = null): Notification
    {
        $message = "Backup do banco de dados {$databaseName} concluído.";
        if ($size) {
            $message .= " Tamanho: {$size}";
        }

        return $this->create(
            $user,
            'success',
            "Backup concluído",
            $message
        );
    }

    /**
     * Get unread notifications for a user
     */
    public function getUnread(User $user, ?int $limit = null): Collection
    {
        $query = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get all notifications for a user
     */
    public function getAll(User $user, int $limit = 50): Collection
    {
        return Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId, User $user): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $user->id)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Delete old notifications
     */
    public function deleteOld(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))
            ->where('is_read', true)
            ->delete();
    }

    /**
     * Get unread count
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }
}
