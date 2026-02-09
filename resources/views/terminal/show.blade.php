<x-layout>
    <div class="h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col py-4">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('terminal.index') }}" 
                       class="p-2 rounded-lg hover:bg-neutral-700 transition-colors text-neutral-400 hover:text-neutral-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h2 class="text-xl font-bold text-neutral-100">{{ $server->name }}</h2>
                        <p class="text-sm text-neutral-400">{{ $server->ip_address }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1.5 text-sm font-semibold rounded-lg bg-success-900/30 text-success-400">
                        <span class="w-2 h-2 rounded-full bg-success-500 mr-2"></span>
                        Connected
                    </span>
                </div>
            </div>

            <!-- Terminal Container -->
            <div class="flex-1 bg-neutral-900 rounded-lg shadow-2xl overflow-hidden border border-neutral-700">
                <div class="h-full" id="terminal-container"></div>
            </div>

            <!-- Instructions -->
            <div class="mt-4 flex items-center justify-between text-sm text-neutral-400">
                <div class="flex items-center gap-4">
                    <span>
                        <kbd class="px-2 py-1 bg-neutral-200 rounded text-xs font-mono">Ctrl + C</kbd> 
                        Interromper
                    </span>
                    <span>
                        <kbd class="px-2 py-1 bg-neutral-200 rounded text-xs font-mono">Ctrl + D</kbd> 
                        Sair
                    </span>
                    <span>
                        <kbd class="px-2 py-1 bg-neutral-200 rounded text-xs font-mono">Tab</kbd> 
                        Autocompletar
                    </span>
                </div>
                <div class="text-primary-600 font-semibold">
                    SSH Terminal
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@5.3.0/css/xterm.css" />
    <script src="https://cdn.jsdelivr.net/npm/xterm@5.3.0/lib/xterm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.8.0/lib/xterm-addon-fit.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-web-links@0.9.0/lib/xterm-addon-web-links.js"></script>
    
    <script>
        // Initialize xterm.js
        const term = new Terminal({
            cursorBlink: true,
            fontSize: 14,
            fontFamily: 'Menlo, Monaco, "Courier New", monospace',
            theme: {
                background: '#171717',
                foreground: '#e5e5e5',
                cursor: '#CC9966',
                cursorAccent: '#171717',
                selection: 'rgba(204, 153, 102, 0.3)',
                black: '#000000',
                red: '#ef4444',
                green: '#22c55e',
                yellow: '#eab308',
                blue: '#3b82f6',
                magenta: '#a855f7',
                cyan: '#06b6d4',
                white: '#e5e5e5',
                brightBlack: '#737373',
                brightRed: '#f87171',
                brightGreen: '#4ade80',
                brightYellow: '#facc15',
                brightBlue: '#60a5fa',
                brightMagenta: '#c084fc',
                brightCyan: '#22d3ee',
                brightWhite: '#ffffff'
            }
        });

        const fitAddon = new FitAddon.FitAddon();
        const webLinksAddon = new WebLinksAddon.WebLinksAddon();
        
        term.loadAddon(fitAddon);
        term.loadAddon(webLinksAddon);
        
        term.open(document.getElementById('terminal-container'));
        fitAddon.fit();

        // Resize handler
        window.addEventListener('resize', () => {
            fitAddon.fit();
        });

        // Welcome message
        term.writeln('\x1b[1;33m╔════════════════════════════════════════════════════════════╗\x1b[0m');
        term.writeln('\x1b[1;33m║\x1b[0m         \x1b[1;36mPudim Deployment SSH Terminal\x1b[0m                     \x1b[1;33m║\x1b[0m');
        term.writeln('\x1b[1;33m╚════════════════════════════════════════════════════════════╝\x1b[0m');
        term.writeln('');
        term.writeln('\x1b[1;32mServer:\x1b[0m   {{ $server->name }}');
        term.writeln('\x1b[1;32mIP:\x1b[0m       {{ $server->ip_address }}');
        term.writeln('\x1b[1;32mStatus:\x1b[0m   \x1b[1;32mConnected ✓\x1b[0m');
        term.writeln('');
        term.writeln('\x1b[2mType commands below. Use Ctrl+C to interrupt.\x1b[0m');
        term.writeln('');
        
        let currentCommand = '';
        let commandHistory = [];
        let historyIndex = -1;

        // Prompt
        const writePrompt = () => {
            term.write('\x1b[1;36m{{ $server->name }}\x1b[0m:\x1b[1;34m~\x1b[0m$ ');
        };

        writePrompt();

        // Handle input
        term.onData(data => {
            const code = data.charCodeAt(0);

            // Enter
            if (code === 13) {
                term.write('\r\n');
                if (currentCommand.trim()) {
                    executeCommand(currentCommand.trim());
                    commandHistory.push(currentCommand);
                    historyIndex = commandHistory.length;
                }
                currentCommand = '';
            }
            // Backspace
            else if (code === 127) {
                if (currentCommand.length > 0) {
                    currentCommand = currentCommand.slice(0, -1);
                    term.write('\b \b');
                }
            }
            // Ctrl+C
            else if (code === 3) {
                term.write('^C\r\n');
                currentCommand = '';
                writePrompt();
            }
            // Ctrl+L (clear)
            else if (code === 12) {
                term.clear();
                writePrompt();
            }
            // Arrow Up (history)
            else if (code === 27 && data === '\x1b[A') {
                if (historyIndex > 0) {
                    historyIndex--;
                    // Clear current line
                    term.write('\r\x1b[K');
                    writePrompt();
                    currentCommand = commandHistory[historyIndex];
                    term.write(currentCommand);
                }
            }
            // Arrow Down (history)
            else if (code === 27 && data === '\x1b[B') {
                if (historyIndex < commandHistory.length - 1) {
                    historyIndex++;
                    term.write('\r\x1b[K');
                    writePrompt();
                    currentCommand = commandHistory[historyIndex];
                    term.write(currentCommand);
                } else if (historyIndex === commandHistory.length - 1) {
                    historyIndex = commandHistory.length;
                    term.write('\r\x1b[K');
                    writePrompt();
                    currentCommand = '';
                }
            }
            // Regular characters
            else if (code >= 32 && code < 127) {
                currentCommand += data;
                term.write(data);
            }
        });

        // Execute command via AJAX
        function executeCommand(command) {
            fetch('{{ route("terminal.execute", $server) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ command: command })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Write output
                    if (data.output) {
                        const lines = data.output.split('\n');
                        lines.forEach(line => {
                            term.writeln(line);
                        });
                    }
                } else {
                    term.writeln('\x1b[1;31mError: ' + (data.error || 'Command execution failed') + '\x1b[0m');
                }
                writePrompt();
            })
            .catch(error => {
                term.writeln('\x1b[1;31mConnection error: ' + error.message + '\x1b[0m');
                writePrompt();
            });
        }
    </script>
    @endpush
</x-layout>
