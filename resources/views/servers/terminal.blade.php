<x-layout title="Terminal - {{ $server->name }}">
    <div class="min-h-screen bg-neutral-900 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-neutral-800 rounded-t-lg px-6 py-4 flex items-center justify-between border-b border-neutral-700">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('servers.index') }}" class="text-neutral-400 hover:text-white transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-white font-bold text-lg">{{ $server->name }}</h1>
                        <p class="text-neutral-400 text-sm">{{ $server->ssh_user ?? 'root' }}@{{ $server->ip_address }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="px-3 py-1 text-xs font-medium rounded-full {{ $server->status === 'online' ? 'bg-green-900 text-green-200' : 'bg-red-900 text-red-200' }}">
                        {{ $server->status }}
                    </span>
                    <button onclick="clearTerminal()" class="px-4 py-2 bg-neutral-700 hover:bg-neutral-600 text-white rounded-lg text-sm font-medium transition">
                        Limpar
                    </button>
                    <button onclick="reconnect()" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition">
                        Reconectar
                    </button>
                </div>
            </div>

            <!-- Terminal -->
            <div class="bg-black rounded-b-lg overflow-hidden" style="height: calc(100vh - 250px);">
                <div id="terminal" class="w-full h-full p-4"></div>
            </div>

            <!-- Quick Commands -->
            <div class="mt-6 bg-neutral-800 rounded-lg p-4">
                <h3 class="text-white font-semibold mb-3">Comandos Rápidos</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <button onclick="runQuickCommand('htop')" class="px-3 py-2 bg-neutral-700 hover:bg-neutral-600 text-white rounded text-sm transition">
                        htop
                    </button>
                    <button onclick="runQuickCommand('df -h')" class="px-3 py-2 bg-neutral-700 hover:bg-neutral-600 text-white rounded text-sm transition">
                        df -h
                    </button>
                    <button onclick="runQuickCommand('free -h')" class="px-3 py-2 bg-neutral-700 hover:bg-neutral-600 text-white rounded text-sm transition">
                        free -h
                    </button>
                    <button onclick="runQuickCommand('systemctl status nginx')" class="px-3 py-2 bg-neutral-700 hover:bg-neutral-600 text-white rounded text-sm transition">
                        nginx status
                    </button>
                    <button onclick="runQuickCommand('systemctl status php8.3-fpm')" class="px-3 py-2 bg-neutral-700 hover:bg-neutral-600 text-white rounded text-sm transition">
                        php-fpm status
                    </button>
                    <button onclick="runQuickCommand('tail -f /var/log/nginx/error.log')" class="px-3 py-2 bg-neutral-700 hover:bg-neutral-600 text-white rounded text-sm transition">
                        nginx errors
                    </button>
                    <button onclick="runQuickCommand('docker ps')" class="px-3 py-2 bg-neutral-700 hover:bg-neutral-600 text-white rounded text-sm transition">
                        docker ps
                    </button>
                    <button onclick="runQuickCommand('git status')" class="px-3 py-2 bg-neutral-700 hover:bg-neutral-600 text-white rounded text-sm transition">
                        git status
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- XTerm.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@5.3.0/css/xterm.css" />
    <script src="https://cdn.jsdelivr.net/npm/xterm@5.3.0/lib/xterm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.8.0/lib/xterm-addon-fit.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-web-links@0.9.0/lib/xterm-addon-web-links.js"></script>

    <script>
        // Initialize terminal
        const term = new Terminal({
            cursorBlink: true,
            fontSize: 14,
            fontFamily: 'Menlo, Monaco, "Courier New", monospace',
            theme: {
                background: '#000000',
                foreground: '#ffffff',
                cursor: '#ffffff',
                selection: '#ffffff33',
                black: '#000000',
                red: '#ff5555',
                green: '#50fa7b',
                yellow: '#f1fa8c',
                blue: '#bd93f9',
                magenta: '#ff79c6',
                cyan: '#8be9fd',
                white: '#bfbfbf',
                brightBlack: '#4d4d4d',
                brightRed: '#ff6e67',
                brightGreen: '#5af78e',
                brightYellow: '#f4f99d',
                brightBlue: '#caa9fa',
                brightMagenta: '#ff92d0',
                brightCyan: '#9aedfe',
                brightWhite: '#e6e6e6'
            },
            rows: 30,
            scrollback: 1000
        });

        const fitAddon = new FitAddon.FitAddon();
        const webLinksAddon = new WebLinksAddon.WebLinksAddon();
        
        term.loadAddon(fitAddon);
        term.loadAddon(webLinksAddon);
        term.open(document.getElementById('terminal'));
        fitAddon.fit();

        // Welcome message
        term.writeln('\x1b[1;32m╔═══════════════════════════════════════════════════════╗\x1b[0m');
        term.writeln('\x1b[1;32m║     Server Manager - Web Terminal                    ║\x1b[0m');
        term.writeln('\x1b[1;32m╚═══════════════════════════════════════════════════════╝\x1b[0m');
        term.writeln('');
        term.writeln(`Conectado ao servidor: \x1b[1;36m{{ $server->name }}\x1b[0m`);
        term.writeln(`IP: \x1b[1;33m{{ $server->ip_address }}\x1b[0m`);
        term.writeln(`Sistema: \x1b[1;35m{{ $server->os_type }} {{ $server->os_version ?? '' }}\x1b[0m`);
        term.writeln('');
        term.writeln('\x1b[1;32m$\x1b[0m ');

        let currentCommand = '';
        let commandHistory = [];
        let historyIndex = -1;

        // Handle user input
        term.onData(data => {
            switch (data) {
                case '\r': // Enter
                    term.write('\r\n');
                    if (currentCommand.trim()) {
                        executeCommand(currentCommand.trim());
                        commandHistory.unshift(currentCommand.trim());
                        if (commandHistory.length > 100) commandHistory.pop();
                    }
                    currentCommand = '';
                    historyIndex = -1;
                    break;
                    
                case '\u007F': // Backspace
                    if (currentCommand.length > 0) {
                        currentCommand = currentCommand.slice(0, -1);
                        term.write('\b \b');
                    }
                    break;
                    
                case '\u001b[A': // Up arrow
                    if (commandHistory.length > 0) {
                        historyIndex = Math.min(historyIndex + 1, commandHistory.length - 1);
                        replaceCurrentLine(commandHistory[historyIndex]);
                    }
                    break;
                    
                case '\u001b[B': // Down arrow
                    if (historyIndex > 0) {
                        historyIndex--;
                        replaceCurrentLine(commandHistory[historyIndex]);
                    } else {
                        historyIndex = -1;
                        replaceCurrentLine('');
                    }
                    break;
                    
                case '\u0003': // Ctrl+C
                    term.write('^C\r\n\x1b[1;32m$\x1b[0m ');
                    currentCommand = '';
                    break;
                    
                default:
                    if (data >= String.fromCharCode(0x20) && data <= String.fromCharCode(0x7E)) {
                        currentCommand += data;
                        term.write(data);
                    }
            }
        });

        function replaceCurrentLine(text) {
            // Clear current line
            term.write('\r\x1b[K\x1b[1;32m$\x1b[0m ');
            currentCommand = text;
            term.write(text);
        }

        function executeCommand(command) {
            term.writeln(`Executando: \x1b[1;33m${command}\x1b[0m`);
            
            fetch(`/servers/{{ $server->id }}/terminal/execute`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ command })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.output) {
                        term.writeln(data.output);
                    }
                } else {
                    term.writeln(`\x1b[1;31mErro: ${data.error}\x1b[0m`);
                }
                term.write('\x1b[1;32m$\x1b[0m ');
            })
            .catch(error => {
                term.writeln(`\x1b[1;31mErro de conexão: ${error.message}\x1b[0m`);
                term.write('\x1b[1;32m$\x1b[0m ');
            });
        }

        function runQuickCommand(command) {
            currentCommand = command;
            term.write(command + '\r\n');
            executeCommand(command);
            currentCommand = '';
        }

        function clearTerminal() {
            term.clear();
            term.write('\x1b[1;32m$\x1b[0m ');
        }

        function reconnect() {
            term.clear();
            term.writeln('\x1b[1;33mReconectando...\x1b[0m');
            term.write('\x1b[1;32m$\x1b[0m ');
        }

        // Resize terminal on window resize
        window.addEventListener('resize', () => {
            fitAddon.fit();
        });

        // Prevent page navigation on Ctrl+C, etc
        window.addEventListener('keydown', (e) => {
            if (e.ctrlKey && (e.key === 'c' || e.key === 'd')) {
                if (document.activeElement.id === 'terminal') {
                    e.preventDefault();
                }
            }
        });
    </script>
    @endpush
</x-layout>
