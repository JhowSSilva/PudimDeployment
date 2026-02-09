<x-layout title="Terminal SSH">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Terminal SSH') }}
        </h2>
        <p class="text-sm text-neutral-400 mt-1">Acesso remoto aos seus servidores via SSH</p>
    </div>

    <div class="mb-4 bg-neutral-800 p-4 rounded-lg">
        <form id="connectionForm" class="flex gap-4 items-end">
            <div class="flex-1">
                <label for="server_id" class="block text-sm font-medium text-neutral-300 mb-2">Servidor</label>
                <select name="server_id" id="server_id" required
                        class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                    <option value="">Selecione um servidor</option>
                    @foreach($servers as $server)
                        <option value="{{ $server->id }}" data-ip="{{ $server->ip_address }}">
                            {{ $server->name }} ({{ $server->ip_address }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-48">
                <label for="ssh_key_id" class="block text-sm font-medium text-neutral-300 mb-2">Chave SSH</label>
                <select name="ssh_key_id" id="ssh_key_id"
                        class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                    <option value="">Padrão</option>
                    @foreach($sshKeys as $key)
                        <option value="{{ $key->id }}">{{ $key->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" id="connectBtn" class="px-6 py-2 bg-info-600 text-white rounded-lg hover:bg-info-700">
                Conectar
            </button>
        </form>
    </div>

    <div class="bg-neutral-900 rounded-lg overflow-hidden shadow-xl" id="terminalContainer" style="display: none;">
        <div class="bg-neutral-800 px-4 py-2 flex justify-between items-center border-b border-neutral-700">
            <div class="flex items-center gap-2">
                <div class="flex gap-1.5">
                    <div class="w-3 h-3 rounded-full bg-error-500"></div>
                    <div class="w-3 h-3 rounded-full bg-primary-500"></div>
                    <div class="w-3 h-3 rounded-full bg-success-500"></div>
                </div>
                <span class="text-neutral-400 text-sm ml-4" id="connectionStatus">Desconectado</span>
            </div>
            <button id="disconnectBtn" class="text-sm text-error-400 hover:text-error-300">
                Desconectar
            </button>
        </div>
        <div id="terminal" class="p-4" style="height: 600px; background: #1a1a1a;"></div>
    </div>

    <div class="mt-6 bg-neutral-800 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-white mb-4">Comandos Rápidos</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            <button class="quick-cmd px-3 py-2 bg-neutral-700 text-white text-sm rounded hover:bg-neutral-600 transition-colors" data-cmd="ls -la">
                ls -la
            </button>
            <button class="quick-cmd px-3 py-2 bg-neutral-700 text-white text-sm rounded hover:bg-neutral-600 transition-colors" data-cmd="df -h">
                df -h
            </button>
            <button class="quick-cmd px-3 py-2 bg-neutral-700 text-white text-sm rounded hover:bg-neutral-600 transition-colors" data-cmd="free -m">
                free -m
            </button>
            <button class="quick-cmd px-3 py-2 bg-neutral-700 text-white text-sm rounded hover:bg-neutral-600 transition-colors" data-cmd="top">
                top
            </button>
            <button class="quick-cmd px-3 py-2 bg-neutral-700 text-white text-sm rounded hover:bg-neutral-600 transition-colors" data-cmd="ps aux">
                ps aux
            </button>
            <button class="quick-cmd px-3 py-2 bg-neutral-700 text-white text-sm rounded hover:bg-neutral-600 transition-colors" data-cmd="systemctl status">
                systemctl status
            </button>
            <button class="quick-cmd px-3 py-2 bg-neutral-700 text-white text-sm rounded hover:bg-neutral-600 transition-colors" data-cmd="docker ps">
                docker ps
            </button>
            <button class="quick-cmd px-3 py-2 bg-neutral-700 text-white text-sm rounded hover:bg-neutral-600 transition-colors" data-cmd="tail -f /var/log/syslog">
                tail logs
            </button>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/xterm@5.3.0/lib/xterm.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@5.3.0/css/xterm.css">
    <script>
        let term;
        let socket;

        document.getElementById('connectionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const serverId = document.getElementById('server_id').value;
            const keyId = document.getElementById('ssh_key_id').value;
            
            if (!serverId) {
                alert('Por favor, selecione um servidor');
                return;
            }

            connectTerminal(serverId, keyId);
        });

        document.getElementById('disconnectBtn').addEventListener('click', function() {
            disconnectTerminal();
        });

        document.querySelectorAll('.quick-cmd').forEach(btn => {
            btn.addEventListener('click', function() {
                const cmd = this.dataset.cmd;
                if (term && socket && socket.readyState === WebSocket.OPEN) {
                    socket.send(JSON.stringify({ type: 'command', data: cmd + '\n' }));
                } else {
                    alert('Por favor, conecte-se a um servidor primeiro');
                }
            });
        });

        function connectTerminal(serverId, keyId) {
            document.getElementById('terminalContainer').style.display = 'block';
            document.getElementById('connectionStatus').textContent = 'Conectando...';

            if (!term) {
                term = new Terminal({
                    cursorBlink: true,
                    theme: {
                        background: '#1a1a1a',
                        foreground: '#10b981',
                    }
                });
                term.open(document.getElementById('terminal'));
            }

            const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            socket = new WebSocket(`${protocol}//${window.location.host}/ssh/connect?server_id=${serverId}&key_id=${keyId || ''}`);

            socket.onopen = function() {
                document.getElementById('connectionStatus').textContent = 'Conectado';
                term.write('✓ Conectado ao servidor\r\n');
            };

            socket.onmessage = function(event) {
                term.write(event.data);
            };

            socket.onerror = function(error) {
                document.getElementById('connectionStatus').textContent = 'Erro na conexão';
                term.write('\r\n✗ Erro na conexão SSH\r\n');
            };

            socket.onclose = function() {
                document.getElementById('connectionStatus').textContent = 'Desconectado';
                term.write('\r\n✗ Conexão encerrada\r\n');
            };

            term.onData(data => {
                if (socket && socket.readyState === WebSocket.OPEN) {
                    socket.send(JSON.stringify({ type: 'input', data: data }));
                }
            });
        }

        function disconnectTerminal() {
            if (socket) {
                socket.close();
            }
            document.getElementById('terminalContainer').style.display = 'none';
        }
    </script>
    @endpush
</x-layout>
