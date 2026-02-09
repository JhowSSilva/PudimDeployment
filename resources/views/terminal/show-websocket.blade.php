<x-layout>
    <div class="h-screen flex flex-col bg-neutral-900" 
         x-data="terminalApp({{ $server->id }}, '{{ $server->name }}')" 
         x-init="init()">
        
        <!-- Header with Tabs -->
        <div class="bg-neutral-800 border-b border-neutral-700">
            <!-- Server Tabs -->
            <div class="flex items-center px-4 gap-2 overflow-x-auto">
                <template x-for="(terminal, index) in terminals" :key="terminal.serverId">
                    <div @click="switchTerminal(index)"
                         :class="activeTerminal === index ? 'bg-neutral-700 border-primary-600' : 'bg-neutral-800/50 border-transparent hover:bg-neutral-750'"
                         class="flex items-center gap-2 px-4 py-2 border-b-2 text-sm cursor-pointer transition group min-w-fit">
                        <svg class="w-4 h-4 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                        </svg>
                        <span :class="activeTerminal === index ? 'text-white' : 'text-neutral-400'" x-text="terminal.serverName"></span>
                        <button @click.stop="closeTerminal(index)" 
                                x-show="terminals.length > 1"
                                class="ml-2 text-neutral-500 hover:text-error-400 opacity-0 group-hover:opacity-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </template>
                
                <!-- Add Server Button -->
                <a href="{{ route('terminal.index') }}"
                   class="flex items-center gap-2 px-3 py-2 text-neutral-500 hover:text-white hover:bg-neutral-700 rounded transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span class="text-xs">Add Terminal</span>
                </a>
            </div>
            
            <!-- Server Info Bar -->
            <div class="px-6 py-2 bg-neutral-800/50 flex items-center justify-between text-xs">
                <div class="flex items-center gap-4 text-neutral-400">
                    <span x-show="currentTerminal" x-text="currentTerminal?.serverName"></span>
                    <span x-show="currentTerminal" x-text="currentTerminal?.status"></span>
                </div>
                <div class="flex items-center gap-4">
                    <!-- File Transfer Button -->
                    <button @click="$dispatch('file-transfer-open'); window.currentServerId = {{ $server->id }}"
                            class="flex items-center gap-2 px-3 py-1.5 bg-neutral-700 hover:bg-neutral-600 text-neutral-300 hover:text-white rounded transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <span>Files</span>
                    </button>
                    
                    <!-- Connection Status -->
                    <div class="flex items-center gap-2">
                        <div :class="currentTerminal?.connected ? 'bg-success-500' : 'bg-error-500'" class="w-2 h-2 rounded-full"></div>
                        <span :class="currentTerminal?.connected ? 'text-success-400' : 'text-error-400'">
                            <span x-text="currentTerminal?.connected ? 'Connected (WebSocket)' : 'Disconnected'"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terminal Windows -->
        <div class="flex-1 relative">
            <template x-for="(terminal, index) in terminals" :key="terminal.serverId">
                <div x-show="activeTerminal === index" 
                     class="absolute inset-0 bg-neutral-900"
                     :id="'terminal-' + terminal.serverId"></div>
            </template>
        </div>

        <!-- Footer with shortcuts -->
        <div class="bg-neutral-800 border-t border-neutral-700 px-6 py-2 flex items-center justify-between text-xs text-neutral-400">
            <div class="flex items-center gap-4">
                <span><kbd class="px-2 py-0.5 bg-neutral-700 rounded">Ctrl + C</kbd> Interrupt</span>
                <span><kbd class="px-2 py-0.5 bg-neutral-700 rounded">Ctrl + L</kbd> Clear</span>
                <span><kbd class="px-2 py-0.5 bg-neutral-700 rounded">Ctrl + D</kbd> Exit</span>
                <span><kbd class="px-2 py-0.5 bg-neutral-700 rounded">↑/↓</kbd> History</span>
            </div>
            <div class="text-primary-600 font-semibold">SSH Terminal (WebSocket)</div>
        </div>
    </div>

    <!-- File Transfer Modal -->
    <x-file-transfer-modal />

    @push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@5.3.0/css/xterm.css" />
    <script src="https://cdn.jsdelivr.net/npm/xterm@5.3.0/lib/xterm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.8.0/lib/xterm-addon-fit.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-web-links@0.9.0/lib/xterm-addon-web-links.js"></script>
    
    <script>
        function terminalApp(serverId, serverName) {
            return {
                terminals: [],
                activeTerminal: 0,
                currentTerminal: null,
                
                init() {
                    // Create first terminal
                    this.createTerminal(serverId, serverName);
                    
                    // Setup WebSocket connection
                    this.setupEcho();
                },
                
                setupEcho() {
                    // Subscribe to private channel for this server
                    window.Echo.private(`terminal.${serverId}`)
                        .listen('.terminal.output', (e) => {
                            const terminal = this.terminals.find(t => t.serverId == serverId);
                            if (terminal && terminal.term) {
                                this.handleTerminalOutput(terminal.term, e);
                            }
                        });
                },
                
                createTerminal(serverId, serverName) {
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
                    
                    // Create terminal state
                    const terminalState = {
                        serverId,
                        serverName,
                        term,
                        fitAddon,
                        connected: true,
                        status: 'Ready',
                        currentCommand: '',
                        commandHistory: [],
                        historyIndex: -1
                    };
                    
                    this.terminals.push(terminalState);
                    this.currentTerminal = terminalState;
                    
                    // Open terminal in container (on next tick)
                    this.$nextTick(() => {
                        const container = document.getElementById(`terminal-${serverId}`);
                        if (container) {
                            term.open(container);
                            fitAddon.fit();
                            
                            // Welcome message
                            this.writeWelcomeMessage(term, serverName);
                            this.writePrompt(terminalState);
                            
                            // Setup handlers
                            this.setupTerminalHandlers(terminalState);
                        }
                    });
                    
                    // Resize handler
                    window.addEventListener('resize', () => {
                        this.terminals.forEach(t => t.fitAddon.fit());
                    });
                },
                
                writeWelcomeMessage(term, serverName) {
                    term.writeln('\x1b[1;33m╔════════════════════════════════════════════════════════════╗\x1b[0m');
                    term.writeln('\x1b[1;33m║\x1b[0m         \x1b[1;36mPudim Deployment SSH Terminal\x1b[0m                     \x1b[1;33m║\x1b[0m');
                    term.writeln('\x1b[1;33m╚════════════════════════════════════════════════════════════╝\x1b[0m');
                    term.writeln('');
                    term.writeln(`\x1b[1;32mServer:\x1b[0m   ${serverName}`);
                    term.writeln('\x1b[1;32mMode:\x1b[0m     \x1b[1;35mWebSocket (Real-time)\x1b[0m');
                    term.writeln('\x1b[1;32mStatus:\x1b[0m   \x1b[1;32mConnected ✓\x1b[0m');
                    term.writeln('');
                    term.writeln('\x1b[2mType commands below. Output streams in real-time.\x1b[0m');
                    term.writeln('');
                },
                
                writePrompt(terminalState) {
                    terminalState.term.write(`\x1b[1;36m${terminalState.serverName}\x1b[0m:\x1b[1;34m~\x1b[0m$ `);
                },
                
                setupTerminalHandlers(terminalState) {
                    terminalState.term.onData(data => {
                        const code = data.charCodeAt(0);

                        // Enter
                        if (code === 13) {
                            terminalState.term.write('\r\n');
                            if (terminalState.currentCommand.trim()) {
                                this.executeCommand(terminalState, terminalState.currentCommand.trim());
                                terminalState.commandHistory.push(terminalState.currentCommand);
                                terminalState.historyIndex = terminalState.commandHistory.length;
                            } else {
                                this.writePrompt(terminalState);
                            }
                            terminalState.currentCommand = '';
                        }
                        // Backspace
                        else if (code === 127) {
                            if (terminalState.currentCommand.length > 0) {
                                terminalState.currentCommand = terminalState.currentCommand.slice(0, -1);
                                terminalState.term.write('\b \b');
                            }
                        }
                        // Ctrl+C
                        else if (code === 3) {
                            terminalState.term.write('^C\r\n');
                            terminalState.currentCommand = '';
                            this.writePrompt(terminalState);
                        }
                        // Ctrl+L (clear)
                        else if (code === 12) {
                            terminalState.term.clear();
                            this.writePrompt(terminalState);
                        }
                        // Arrow Up (history)
                        else if (code === 27 && data === '\x1b[A') {
                            if (terminalState.historyIndex > 0) {
                                terminalState.historyIndex--;
                                terminalState.term.write('\r\x1b[K');
                                this.writePrompt(terminalState);
                                terminalState.currentCommand = terminalState.commandHistory[terminalState.historyIndex];
                                terminalState.term.write(terminalState.currentCommand);
                            }
                        }
                        // Arrow Down (history)
                        else if (code === 27 && data === '\x1b[B') {
                            if (terminalState.historyIndex < terminalState.commandHistory.length - 1) {
                                terminalState.historyIndex++;
                                terminalState.term.write('\r\x1b[K');
                                this.writePrompt(terminalState);
                                terminalState.currentCommand = terminalState.commandHistory[terminalState.historyIndex];
                                terminalState.term.write(terminalState.currentCommand);
                            } else if (terminalState.historyIndex === terminalState.commandHistory.length - 1) {
                                terminalState.historyIndex = terminalState.commandHistory.length;
                                terminalState.term.write('\r\x1b[K');
                                this.writePrompt(terminalState);
                                terminalState.currentCommand = '';
                            }
                        }
                        // Regular characters
                        else if (code >= 32 && code < 127) {
                            terminalState.currentCommand += data;
                            terminalState.term.write(data);
                        }
                    });
                },
                
                executeCommand(terminalState, command) {
                    // Send command to server via stream endpoint
                    fetch(`/servers/${terminalState.serverId}/terminal/stream`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ command })
                    }).catch(error => {
                        terminalState.term.writeln(`\r\n\x1b[1;31mError: ${error.message}\x1b[0m\r\n`);
                        this.writePrompt(terminalState);
                    });
                },
                
                handleTerminalOutput(term, event) {
                    const { output, type } = event;
                    
                    if (type === 'command') {
                        // Command echo - already shown by user input, skip repeat
                        return;
                    } else if (type === 'output') {
                        term.write(output + '\r\n');
                        this.writePrompt(this.currentTerminal);
                    } else if (type === 'error') {
                        term.writeln(`\r\n\x1b[1;31m${output}\x1b[0m\r\n`);
                        this.writePrompt(this.currentTerminal);
                    }
                },
                
                switchTerminal(index) {
                    this.activeTerminal = index;
                    this.currentTerminal = this.terminals[index];
                    
                    // Refit terminal on switch
                    this.$nextTick(() => {
                        this.terminals[index].fitAddon.fit();
                    });
                },
                
                closeTerminal(index) {
                    if (this.terminals.length === 1) return;
                    
                    // Dispose terminal
                    this.terminals[index].term.dispose();
                    
                    // Remove from array
                    this.terminals.splice(index, 1);
                    
                    // Adjust active terminal
                    if (this.activeTerminal >= index && this.activeTerminal > 0) {
                        this.activeTerminal--;
                    }
                    this.currentTerminal = this.terminals[this.activeTerminal];
                }
            };
        }
    </script>
    @endpush
</x-layout>
