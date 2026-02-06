<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Show all notifications
     */
    public function index()
    {
        $notifications = $this->notificationService->getAll(Auth::user(), 100);
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get unread count (API)
     */
    public function unreadCount()
    {
        $count = $this->notificationService->getUnreadCount(Auth::user());
        return response()->json(['count' => $count]);
    }

    /**
     * Mark as read
     */
    public function markAsRead(int $id)
    {
        $success = $this->notificationService->markAsRead($id, Auth::user());
        
        if ($success) {
            return response()->json(['message' => 'Notification marked as read']);
        }
        
        return response()->json(['error' => 'Notification not found'], 404);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead()
    {
        $count = $this->notificationService->markAllAsRead(Auth::user());
        return response()->json(['message' => "{$count} notifications marked as read"]);
    }
}
