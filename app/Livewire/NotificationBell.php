<?php

namespace App\Livewire;

use App\Services\NotificationService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationBell extends Component
{
    public $unreadCount = 0;
    public $notifications = [];
    public $showDropdown = false;

    protected $listeners = ['notificationRead' => 'refreshNotifications'];

    public function mount()
    {
        $this->refreshNotifications();
    }

    public function refreshNotifications()
    {
        $service = app(NotificationService::class);
        $this->unreadCount = $service->getUnreadCount(Auth::user());
        $this->notifications = $service->getUnread(Auth::user(), 10)->toArray();
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function markAsRead($notificationId)
    {
        $service = app(NotificationService::class);
        $service->markAsRead($notificationId, Auth::user());
        $this->refreshNotifications();
        $this->dispatch('notificationRead');
    }

    public function markAllAsRead()
    {
        $service = app(NotificationService::class);
        $service->markAllAsRead(Auth::user());
        $this->refreshNotifications();
        $this->dispatch('notificationRead');
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
