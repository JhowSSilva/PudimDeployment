// SSH Terminal usando xterm.js
class SSHTerminal {
    constructor(terminalElementId) {
        this.term = new Terminal({
            cursorBlink: true,
            fontSize: 14,
            fontFamily: "'JetBrains Mono', 'Fira Code', monospace",
            theme: {
                background: '#000000',
                foreground: '#ffffff',
                cursor: '#D4A574',
                selection: 'rgba(212, 165, 116, 0.3)',
                black: '#000000',
                red: '#ff6b6b',
                green: '#51cf66',
                yellow: '#ffd43b',
                blue: '#4dabf7',
                magenta: '#cc5de8',
                cyan: '#22b8cf',
                white: '#adb5bd',
                brightBlack: '#495057',
                brightRed: '#ff8787',
                brightGreen: '#69db7c',
                brightYellow: '#ffe066',
                brightBlue: '#74c0fc',
                brightMagenta: '#da77f2',
                brightCyan: '#3bc9db',
                brightWhite: '#f8f9fa',
            },
            rows: 30,
            cols: 100,
            scrollback: 10000,
            convertEol: true,
        });

        this.fitAddon = new FitAddon.FitAddon();
        this.term.loadAddon(this.fitAddon);

        const container = document.getElementById(terminalElementId);
        this.term.open(container);
        this.fitAddon.fit();

        this.ws = null;
        this.connected = false;
        this.authenticated = false;

        this.setupTerminalListeners();
        this.setupResizeListener();
    }

    setupTerminalListeners() {
        this.term.onData((data) => {
            if (this.connected && this.ws && this.ws.readyState === WebSocket.OPEN) {
                this.sendInput(data);
            }
        });
    }

    setupResizeListener() {
        window.addEventListener('resize', () => {
            this.fitAddon.fit();
            if (this.connected) {
                this.sendResize();
            }
        });
    }

    connect(serverId, keyId = null, password = null) {
        const wsProtocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const wsHost = window.location.hostname;
        const wsPort = 8080; // Porta do WebSocket
        
        this.ws = new WebSocket(`${wsProtocol}//${wsHost}:${wsPort}`);

        this.ws.onopen = () => {
            this.writeln('\x1b[33mConectando ao servidor WebSocket...\x1b[0m');
            
            // Autenticar
            this.authenticate();
            
            // Aguardar autenticação antes de conectar SSH
            setTimeout(() => {
                this.connectSSH(serverId, keyId, password);
            }, 500);
        };

        this.ws.onmessage = (event) => {
            this.handleMessage(event.data);
        };

        this.ws.onerror = (error) => {
            this.writeln('\r\n\x1b[31mErro de conexão WebSocket\x1b[0m\r\n');
            console.error('WebSocket error:', error);
            this.updateStatus('error', 'Erro de conexão');
        };

        this.ws.onclose = () => {
            this.writeln('\r\n\x1b[33mConexão WebSocket fechada\x1b[0m\r\n');
            this.connected = false;
            this.updateStatus('disconnected', 'Desconectado');
        };
    }

    authenticate() {
        // Obter dados do usuário do Laravel
        const userId = document.querySelector('meta[name="user-id"]')?.content;
        const token = document.querySelector('meta[name="csrf-token"]')?.content;

        if (!userId || !token) {
            this.writeln('\x1b[31mErro: Dados de autenticação não encontrados\x1b[0m');
            return;
        }

        this.send({
            action: 'auth',
            user_id: userId,
            token: token
        });
    }

    connectSSH(serverId, keyId, password) {
        this.writeln('\x1b[33mEstabelecendo conexão SSH...\x1b[0m');
        
        this.send({
            action: 'connect',
            server_id: serverId,
            key_id: keyId,
            password: password
        });
    }

    handleMessage(data) {
        try {
            const message = JSON.parse(data);

            switch (message.type) {
                case 'info':
                    this.writeln(`\x1b[36m${message.message}\x1b[0m`);
                    break;

                case 'authenticated':
                    this.authenticated = true;
                    this.writeln(`\x1b[32m${message.message}\x1b[0m`);
                    break;

                case 'connected':
                    this.connected = true;
                    this.writeln(`\r\n\x1b[32m✓ ${message.message}\x1b[0m\r\n`);
                    this.updateStatus('connected', 'Conectado');
                    break;

                case 'output':
                    this.write(message.data);
                    break;

                case 'error':
                    this.writeln(`\r\n\x1b[31m✗ Erro: ${message.message}\x1b[0m\r\n`);
                    this.updateStatus('error', 'Erro');
                    break;

                case 'disconnected':
                    this.connected = false;
                    this.writeln(`\r\n\x1b[33m${message.message}\x1b[0m\r\n`);
                    this.updateStatus('disconnected', 'Desconectado');
                    break;

                default:
                    console.log('Mensagem desconhecida:', message);
            }
        } catch (e) {
            console.error('Erro ao processar mensagem:', e);
        }
    }

    sendInput(data) {
        this.send({
            action: 'input',
            data: data
        });
    }

    sendResize() {
        this.send({
            action: 'resize',
            cols: this.term.cols,
            rows: this.term.rows
        });
    }

    send(data) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(data));
        }
    }

    disconnect() {
        if (this.ws) {
            this.send({ action: 'disconnect' });
            this.ws.close();
            this.ws = null;
        }
        this.connected = false;
        this.authenticated = false;
    }

    clear() {
        this.term.clear();
    }

    write(data) {
        this.term.write(data);
    }

    writeln(data) {
        this.term.writeln(data);
    }

    updateStatus(status, text) {
        const indicator = document.getElementById('status-indicator');
        const statusText = document.getElementById('status-text');
        const btnConnect = document.getElementById('btn-connect');
        const btnDisconnect = document.getElementById('btn-disconnect');

        if (status === 'connected') {
            indicator.className = 'h-3 w-3 rounded-full bg-green-500 animate-pulse';
            statusText.className = 'text-green-400 text-sm font-medium';
            statusText.textContent = text;
            btnConnect.disabled = true;
            btnDisconnect.disabled = false;
        } else if (status === 'error') {
            indicator.className = 'h-3 w-3 rounded-full bg-red-500';
            statusText.className = 'text-red-400 text-sm font-medium';
            statusText.textContent = text;
            btnConnect.disabled = false;
            btnDisconnect.disabled = true;
        } else {
            indicator.className = 'h-3 w-3 rounded-full bg-red-500';
            statusText.className = 'text-red-400 text-sm font-medium';
            statusText.textContent = text;
            btnConnect.disabled = false;
            btnDisconnect.disabled = true;
        }
    }
}

// Inicialização
let terminal;

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar terminal
    terminal = new SSHTerminal('terminal');
    
    terminal.writeln('\x1b[1;33m╔════════════════════════════════════════════════════════╗\x1b[0m');
    terminal.writeln('\x1b[1;33m║        Terminal SSH - Pudim Deployment               ║\x1b[0m');
    terminal.writeln('\x1b[1;33m╚════════════════════════════════════════════════════════╝\x1b[0m');
    terminal.writeln('');
    terminal.writeln('Selecione um servidor e uma chave SSH para começar.');
    terminal.writeln('');

    // Carregar servidores
    loadServers();
    
    // Carregar chaves SSH
    loadSSHKeys();

    // Botão conectar
    document.getElementById('btn-connect').addEventListener('click', function() {
        const serverId = document.getElementById('server-select').value;
        const keyId = document.getElementById('key-select').value;

        if (!serverId) {
            alert('Selecione um servidor');
            return;
        }

        if (!keyId) {
            alert('Selecione uma chave SSH');
            return;
        }

        terminal.clear();
        terminal.connect(serverId, keyId);
    });

    // Botão desconectar
    document.getElementById('btn-disconnect').addEventListener('click', function() {
        terminal.disconnect();
    });

    // Botão limpar
    document.getElementById('btn-clear').addEventListener('click', function() {
        terminal.clear();
    });
});

// Funções auxiliares
async function loadServers() {
    try {
        const response = await fetch('/api/servers');
        const data = await response.json();

        const select = document.getElementById('server-select');
        select.innerHTML = '<option value="">Selecione um servidor...</option>';

        if (data.success && data.servers) {
            data.servers.forEach(server => {
                const option = document.createElement('option');
                option.value = server.id;
                option.textContent = `${server.name} (${server.ip_address || server.public_ip})`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar servidores:', error);
    }
}

async function loadSSHKeys() {
    try {
        const response = await fetch('/api/ssh/keys');
        const data = await response.json();

        const select = document.getElementById('key-select');
        select.innerHTML = '<option value="">Selecione uma chave...</option>';

        if (data.success && data.keys) {
            data.keys.forEach(key => {
                const option = document.createElement('option');
                option.value = key.id;
                option.textContent = `${key.name} (${key.type} ${key.bits})`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar chaves SSH:', error);
    }
}
