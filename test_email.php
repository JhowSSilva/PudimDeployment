<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Teste de envio de email
echo "🧪 Testando envio de email com Mailpit...\n\n";

// Buscar ou criar time
$team = \App\Models\Team::first();
if (!$team) {
    echo "❌ Nenhum time encontrado. Crie um time primeiro.\n";
    exit(1);
}

echo "✅ Time encontrado: {$team->name}\n";

// Criar convite
$invitation = \App\Models\TeamInvitation::create([
    'team_id' => $team->id,
    'invited_by' => $team->user_id,
    'email' => 'teste@exemplo.com',
    'role' => 'member'
]);

echo "✅ Convite criado: ID {$invitation->id}\n";
echo "📧 Email: {$invitation->email}\n";
echo "🔗 Link: {$invitation->invite_url}\n\n";

// Enviar email
try {
    \Illuminate\Support\Facades\Mail::to($invitation->email)
        ->send(new \App\Mail\TeamInvitationMail($invitation));
    
    echo "✅ Email enviado com sucesso!\n";
    echo "🌐 Acesse http://localhost:8025 para ver o email no Mailpit\n\n";
    echo "Token do convite: {$invitation->token}\n";
    echo "URL do convite: {$invitation->invite_url}\n";
} catch (\Exception $e) {
    echo "❌ Erro ao enviar email: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✅ Teste concluído!\n";
