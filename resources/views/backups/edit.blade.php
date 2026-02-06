<x-layout>
    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-3xl font-bold text-neutral-900">Editar Backup</h2>
                    <p class="mt-1 text-sm text-neutral-600">
                        {{ $backup->name }}
                    </p>
                </div>
                <a href="{{ route('backups.index') }}" 
                   class="px-4 py-2 text-neutral-700 hover:text-neutral-900 transition">
                    Voltar
                </a>
            </div>

            <!-- Multi-tab Form -->
            <div x-data="backupForm()" class="bg-white rounded-lg shadow">
                <!-- Tabs -->
                <div class="border-b border-neutral-200">
                    <nav class="flex -mb-px" aria-label="Tabs">
                        <button @click="currentTab = 'database'" 
                                :class="currentTab === 'database' ? 'border-amber-600 text-amber-600' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'"
                                class="w-1/5 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                            <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                            </svg>
                            Database
                        </button>
                        <button @click="currentTab = 'storage'" 
                                :class="currentTab === 'storage' ? 'border-amber-600 text-amber-600' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'"
                                class="w-1/5 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                            <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                            </svg>
                            Storage
                        </button>
                        <button @click="currentTab = 'schedule'" 
                                :class="currentTab === 'schedule' ? 'border-amber-600 text-amber-600' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'"
                                class="w-1/5 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                            <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Schedule
                        </button>
                        <button @click="currentTab = 'advanced'" 
                                :class="currentTab === 'advanced' ? 'border-amber-600 text-amber-600' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'"
                                class="w-1/5 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                            <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Advanced
                        </button>
                        <button @click="currentTab = 'notifications'" 
                                :class="currentTab === 'notifications' ? 'border-amber-600 text-amber-600' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'"
                                class="w-1/5 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                            <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            Notifications
                        </button>
                    </nav>
                </div>

                <!-- Form -->
                <form action="{{ route('backups.update', $backup) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Database Tab -->
                        <div x-show="currentTab === 'database'" x-transition>
                            <h3 class="text-lg font-semibold text-neutral-900 mb-4">Configuração do Database</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-2">Nome do Backup</label>
                                    <input type="text" name="name" value="{{ $backup->name }}" required
                                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                                </div>

                                <div class="bg-neutral-50 border border-neutral-200 rounded-lg p-4">
                                    <div class="text-sm text-neutral-700">
                                        <div class="font-semibold mb-2">Database Atual:</div>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                                            </svg>
                                            <span>{{ $backup->database->name }} ({{ ucfirst($backup->database->type) }})</span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                            </svg>
                                            <span>{{ $backup->database->server->name }} - {{ $backup->database->server->ip_address }}</span>
                                        </div>
                                        <p class="text-xs text-neutral-500 mt-2">Para alterar o database, crie um novo backup</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Storage Tab -->
                        <div x-show="currentTab === 'storage'" x-transition>
                            <h3 class="text-lg font-semibold text-neutral-900 mb-4">Cloud Storage</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-2">Provider</label>
                                    <select name="storage_provider" @change="updateStorageFields($event.target.value)" x-model="storageProvider" required
                                            class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                                        @foreach(config('backup-providers.providers') as $key => $provider)
                                            <option value="{{ $key }}" {{ $backup->storage_provider === $key ? 'selected' : '' }}>
                                                {{ $provider['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 mb-2">Bucket/Container</label>
                                        <input type="text" name="storage_config[bucket]" value="{{ $backup->storage_config['bucket'] ?? '' }}"
                                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                                    </div>

                                    <div x-show="['s3', 'spaces', 'b2', 'wasabi', 'minio'].includes(storageProvider)">
                                        <label class="block text-sm font-medium text-neutral-700 mb-2">Region</label>
                                        <input type="text" name="storage_config[region]" value="{{ $backup->storage_config['region'] ?? '' }}"
                                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 mb-2">Access Key</label>
                                        <input type="text" name="storage_config[key]" value="{{ $backup->storage_config['key'] ?? '' }}"
                                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 mb-2">Secret Key</label>
                                        <input type="password" name="storage_config[secret]" placeholder="••••••••"
                                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                                        <p class="text-xs text-neutral-500 mt-1">Deixe em branco para manter o valor atual</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 mb-2">Path (opcional)</label>
                                        <input type="text" name="storage_config[path]" value="{{ $backup->storage_config['path'] ?? '' }}"
                                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Tab -->
                        <div x-show="currentTab === 'schedule'" x-transition>
                            <h3 class="text-lg font-semibold text-neutral-900 mb-4">Agendamento</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-2">Frequência</label>
                                    <select name="frequency" x-model="frequency" required
                                            class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                                        @foreach(config('backup-providers.frequencies') as $key => $label)
                                            <option value="{{ $key }}" {{ $backup->frequency === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div x-show="['daily', 'weekly', 'monthly'].includes(frequency)">
                                    <label class="block text-sm font-medium text-neutral-700 mb-2">Horário</label>
                                    <input type="time" name="time" value="{{ $backup->time }}"
                                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                                </div>

                                <div x-show="frequency === 'weekly'">
                                    <label class="block text-sm font-medium text-neutral-700 mb-2">Dia da Semana</label>
                                    <select name="day_of_week"
                                            class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                                        @foreach(['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'] as $day => $label)
                                            <option value="{{ $day }}" {{ $backup->day_of_week == $day ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div x-show="frequency === 'monthly'">
                                    <label class="block text-sm font-medium text-neutral-700 mb-2">Dia do Mês</label>
                                    <select name="day_of_month"
                                            class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                                        @for($i = 1; $i <= 28; $i++)
                                            <option value="{{ $i }}" {{ $backup->day_of_month == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-2">Timezone</label>
                                    <select name="timezone"
                                            class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                                        @foreach(['America/Sao_Paulo' => 'America/Sao_Paulo (BRT)', 'America/New_York' => 'America/New_York (EST)', 'Europe/London' => 'Europe/London (GMT)', 'Asia/Tokyo' => 'Asia/Tokyo (JST)'] as $tz => $label)
                                            <option value="{{ $tz }}" {{ $backup->timezone === $tz ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-2">Retention (dias)</label>
                                    <input type="number" name="retention_days" value="{{ $backup->retention_days }}" min="1" required
                                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Tab -->
                        <div x-show="currentTab === 'advanced'" x-transition>
                            <h3 class="text-lg font-semibold text-neutral-900 mb-4">Opções Avançadas</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-2">Compressão</label>
                                    <select name="compression_type"
                                            class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                                        @foreach(config('backup-providers.compression_types') as $key => $label)
                                            <option value="{{ $key }}" {{ $backup->compression_type === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="encryption_enabled" value="1" {{ $backup->encryption_enabled ? 'checked' : '' }} 
                                               class="rounded border-neutral-300 text-amber-600 focus:ring-amber-600">
                                        <span class="ml-2 text-sm text-neutral-700">Habilitar criptografia AES-256</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-2">Senha de Criptografia (opcional)</label>
                                    <input type="password" name="encryption_password" placeholder="••••••••"
                                           class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent">
                                    <p class="text-xs text-neutral-500 mt-1">Deixe em branco para manter a senha atual</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-2">Tabelas/Schemas para Excluir (opcional)</label>
                                    <textarea name="exclusions" rows="3"
                                              class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent">{{ is_array($backup->exclusions) ? implode(',', $backup->exclusions) : $backup->exclusions }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications Tab -->
                        <div x-show="currentTab === 'notifications'" x-transition>
                            <h3 class="text-lg font-semibold text-neutral-900 mb-4">Notificações</h3>
                            
                            @php $notif = $backup->notificationSetting @endphp
                            
                            <div class="space-y-6">
                                <!-- Email -->
                                <div class="border border-neutral-200 rounded-lg p-4">
                                    <label class="flex items-center mb-3">
                                        <input type="checkbox" name="notifications[email][enabled]" value="1" {{ $notif && $notif->email_enabled ? 'checked' : '' }}
                                               class="rounded border-neutral-300 text-amber-600 focus:ring-amber-600">
                                        <span class="ml-2 font-semibold text-neutral-900">Email</span>
                                    </label>
                                    <div class="ml-6 space-y-3">
                                        <input type="text" name="notifications[email][recipients]" value="{{ $notif && is_array($notif->email_recipients) ? implode(',', $notif->email_recipients) : '' }}"
                                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent"
                                               placeholder="admin@example.com,backup@example.com">
                                        <div class="flex gap-4">
                                            <label class="flex items-center">
                                                <input type="checkbox" name="notifications[email][on_success]" value="1" {{ $notif && $notif->email_on_success ? 'checked' : '' }}
                                                       class="rounded border-neutral-300 text-amber-600 focus:ring-amber-600">
                                                <span class="ml-2 text-sm text-neutral-700">Sucesso</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="notifications[email][on_failure]" value="1" {{ $notif && $notif->email_on_failure ? 'checked' : '' }}
                                                       class="rounded border-neutral-300 text-amber-600 focus:ring-amber-600">
                                                <span class="ml-2 text-sm text-neutral-700">Falha</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Webhook -->
                                <div class="border border-neutral-200 rounded-lg p-4">
                                    <label class="flex items-center mb-3">
                                        <input type="checkbox" name="notifications[webhook][enabled]" value="1" {{ $notif && $notif->webhook_enabled ? 'checked' : '' }}
                                               class="rounded border-neutral-300 text-amber-600 focus:ring-amber-600">
                                        <span class="ml-2 font-semibold text-neutral-900">Webhook</span>
                                    </label>
                                    <div class="ml-6 space-y-3">
                                        <input type="url" name="notifications[webhook][url]" value="{{ $notif?->webhook_url }}"
                                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent"
                                               placeholder="https://yourapp.com/webhooks/backup">
                                        <input type="text" name="notifications[webhook][secret]" value="{{ $notif?->webhook_secret }}"
                                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent"
                                               placeholder="Webhook secret key">
                                    </div>
                                </div>

                                <!-- Slack -->
                                <div class="border border-neutral-200 rounded-lg p-4">
                                    <label class="flex items-center mb-3">
                                        <input type="checkbox" name="notifications[slack][enabled]" value="1" {{ $notif && $notif->slack_enabled ? 'checked' : '' }}
                                               class="rounded border-neutral-300 text-amber-600 focus:ring-amber-600">
                                        <span class="ml-2 font-semibold text-neutral-900">Slack</span>
                                    </label>
                                    <div class="ml-6 space-y-3">
                                        <input type="url" name="notifications[slack][webhook_url]" value="{{ $notif?->slack_webhook_url }}"
                                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent"
                                               placeholder="https://hooks.slack.com/services/YOUR/WEBHOOK/URL">
                                        <input type="text" name="notifications[slack][channel]" value="{{ $notif?->slack_channel }}"
                                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent"
                                               placeholder="#backups">
                                    </div>
                                </div>

                                <!-- Discord -->
                                <div class="border border-neutral-200 rounded-lg p-4">
                                    <label class="flex items-center mb-3">
                                        <input type="checkbox" name="notifications[discord][enabled]" value="1" {{ $notif && $notif->discord_enabled ? 'checked' : '' }}
                                               class="rounded border-neutral-300 text-amber-600 focus:ring-amber-600">
                                        <span class="ml-2 font-semibold text-neutral-900">Discord</span>
                                    </label>
                                    <div class="ml-6 space-y-3">
                                        <input type="url" name="notifications[discord][webhook_url]" value="{{ $notif?->discord_webhook_url }}"
                                               class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-transparent"
                                               placeholder="https://discord.com/api/webhooks/YOUR/WEBHOOK">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="border-t border-neutral-200 px-6 py-4 flex justify-between">
                        <button type="button" @click="previousTab()" x-show="currentTab !== 'database'"
                                class="px-4 py-2 text-neutral-700 hover:text-neutral-900 transition">
                            ← Anterior
                        </button>
                        <div class="flex gap-3 ml-auto">
                            <a href="{{ route('backups.index') }}" 
                               class="px-4 py-2 text-neutral-700 hover:text-neutral-900 transition">
                                Cancelar
                            </a>
                            <button type="button" @click="nextTab()" x-show="currentTab !== 'notifications'"
                                    class="px-6 py-2 bg-neutral-200 text-neutral-700 rounded-lg hover:bg-neutral-300 transition">
                                Próximo →
                            </button>
                            <button type="submit" x-show="currentTab === 'notifications'"
                                    class="px-6 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition shadow-lg shadow-amber-600/20">
                                Salvar Alterações
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function backupForm() {
            return {
                currentTab: 'database',
                storageProvider: '{{ $backup->storage_provider }}',
                frequency: '{{ $backup->frequency }}',
                tabs: ['database', 'storage', 'schedule', 'advanced', 'notifications'],
                
                nextTab() {
                    const currentIndex = this.tabs.indexOf(this.currentTab);
                    if (currentIndex < this.tabs.length - 1) {
                        this.currentTab = this.tabs[currentIndex + 1];
                    }
                },
                
                previousTab() {
                    const currentIndex = this.tabs.indexOf(this.currentTab);
                    if (currentIndex > 0) {
                        this.currentTab = this.tabs[currentIndex - 1];
                    }
                },

                updateStorageFields(provider) {
                    this.storageProvider = provider;
                }
            }
        }
    </script>
</x-layout>
