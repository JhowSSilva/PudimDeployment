<?php

require __DIR__ . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\SSHTerminalHandler;

// Carregar .env do Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$port = env('WEBSOCKET_PORT', 8080);

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new SSHTerminalHandler()
        )
    ),
    $port
);

echo "========================================\n";
echo "SSH Terminal WebSocket Server\n";
echo "========================================\n";
echo "Server running on port {$port}\n";
echo "Press Ctrl+C to stop\n";
echo "========================================\n";

$server->run();
