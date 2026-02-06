<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            color: white;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin: 20px 0;
            color: #333;
        }
        .team-name {
            font-size: 28px;
            font-weight: bold;
            color: #14b8a6;
            margin: 20px 0;
        }
        .role-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin: 10px 0;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 10px;
        }
        .btn-secondary {
            background: #6b7280;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
        }
        .link {
            color: white;
            word-break: break-all;
            font-size: 12px;
            margin: 20px 0;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">⚡ Agile'sDeployment</div>
        
        <div class="content">
            <h1>Você foi convidado!</h1>
            
            <p><strong>{{ $invitation->inviter->name }}</strong> convidou você para participar do time:</p>
            
            <div class="team-name">{{ $invitation->team->name }}</div>
            
            @if($invitation->team->description)
                <p style="color: #6b7280;">{{ $invitation->team->description }}</p>
            @endif
            
            <p>Você foi convidado com a função de:</p>
            
            <div class="role-badge" style="background: 
                @if($invitation->role === 'admin') #fee2e2; color: #991b1b;
                @elseif($invitation->role === 'manager') #dbeafe; color: #1e40af;
                @elseif($invitation->role === 'member') #dcfce7; color: #166534;
                @else #f3f4f6; color: #374151;
                @endif">
                {{ match($invitation->role) {
                    'admin' => '🔴 Admin - Controle total',
                    'manager' => '🔵 Gerente - Gerenciar recursos',
                    'member' => '🟢 Membro - Criar recursos',
                    'viewer' => '⚪ Visualizador - Apenas visualizar',
                } }}
            </div>
            
            <p style="margin-top: 30px;">
                <a href="{{ $invitation->invite_url }}" class="btn">Aceitar Convite</a>
                <a href="{{ route('invites.reject', $invitation->token) }}" class="btn btn-secondary">Recusar</a>
            </p>
        </div>
        
        <div class="footer">
            <p>Este convite expira em {{ $invitation->expires_at->format('d/m/Y H:i') }}</p>
            <p>Se o botão não funcionar, copie e cole este link no seu navegador:</p>
            <div class="link">{{ $invitation->invite_url }}</div>
        </div>
    </div>
</body>
</html>
