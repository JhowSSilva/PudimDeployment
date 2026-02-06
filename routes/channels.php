<?php

use App\Models\Server;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('terminal.{serverId}', function ($user, $serverId) {
    // Verify user has access to this server
    $server = Server::findOrFail($serverId);
    
    // Check if user owns the server or has permission
    return $user->id === $server->user_id;
});
