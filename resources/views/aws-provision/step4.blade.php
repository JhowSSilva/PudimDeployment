<x-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-success-500 text-white font-semibold">
                                ✓
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-success-400">Credenciais</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 border-t-2 border-success-600 mx-4"></div>
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-success-500 text-white font-semibold">
                                ✓
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-success-400">Instância</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 border-t-2 border-success-600 mx-4"></div>
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-success-500 text-white font-semibold">
                                ✓
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-success-400">Stack</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 border-t-2 border-success-600 mx-4"></div>
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-info-600 text-white font-semibold">
                                4
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-info-400">Revisar</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-neutral-800 rounded-lg shadow p-8">
                <h2 class="text-2xl font-bold mb-2">Revisar Configuração</h2>
                <p class="text-neutral-400 mb-6">Confirme os detalhes antes de provisionar o servidor</p>

                <form action="{{ route('aws-provision.provision') }}" method="POST">
                    @csrf
                    <input type="hidden" name="aws_credential_id" value="{{ request('aws_credential_id') }}">
                    <input type="hidden" name="region" value="{{ request('region') }}">
                    <input type="hidden" name="instance_type" value="{{ request('instance_type') }}">
                    <input type="hidden" name="disk_size" value="{{ request('disk_size') }}">
                    <input type="hidden" name="webserver" value="{{ request('webserver') }}">
                    <input type="hidden" name="php_version" value="{{ request('php_version') }}">
                    <input type="hidden" name="database" value="{{ request('database') }}">
                    <input type="hidden" name="cache" value="{{ request('cache') }}">
                    <input type="hidden" name="nodejs" value="{{ request('nodejs') }}">
                    @if(request('extras'))
                        @foreach(request('extras') as $extra)
                            <input type="hidden" name="extras[]" value="{{ $extra }}">
                        @endforeach
                    @endif

                    <!-- Server Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-neutral-300 mb-2">
                            Nome do Servidor *
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name"
                            value="{{ old('name') }}"
                            class="w-full border border-neutral-600 rounded px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="app-production-01"
                            required
                        >
                    </div>

                    <!-- Configuration Summary -->
                    <div class="space-y-4 mb-6">
                        <!-- AWS -->
                        <div class="bg-neutral-900 rounded-lg p-4">
                            <h3 class="font-semibold text-neutral-100 mb-3">AWS Configuration</h3>
                            <dl class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <dt class="text-neutral-400">Credencial</dt>
                                    <dd class="font-medium text-neutral-100">{{ $credential->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-neutral-400">Região</dt>
                                    <dd class="font-medium text-neutral-100">{{ request('region') }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Instance -->
                        <div class="bg-neutral-900 rounded-lg p-4">
                            <h3 class="font-semibold text-neutral-100 mb-3 flex items-center">
                                Instance Configuration
                                @if($instanceType->isGraviton())
                                    <span class="ml-2 px-2 py-1 bg-success-900/30 text-success-400 text-xs font-medium rounded">GRAVITON</span>
                                @endif
                            </h3>
                            <dl class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <dt class="text-neutral-400">Tipo</dt>
                                    <dd class="font-medium text-neutral-100">{{ $instanceType->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-neutral-400">Arquitetura</dt>
                                    <dd class="font-medium text-neutral-100">
                                        {{ $instanceType->architecture }}
                                        @if($instanceType->isGraviton())
                                            <span class="text-success-400">(40% economia)</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-neutral-400">vCPU</dt>
                                    <dd class="font-medium text-neutral-100">{{ $instanceType->vcpu }}</dd>
                                </div>
                                <div>
                                    <dt class="text-neutral-400">RAM</dt>
                                    <dd class="font-medium text-neutral-100">{{ $instanceType->memory_gib }} GB</dd>
                                </div>
                                <div>
                                    <dt class="text-neutral-400">Disco</dt>
                                    <dd class="font-medium text-neutral-100">{{ request('disk_size') }} GB SSD</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Stack -->
                        <div class="bg-neutral-900 rounded-lg p-4">
                            <h3 class="font-semibold text-neutral-100 mb-3">Software Stack</h3>
                            <dl class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <dt class="text-neutral-400">Webserver</dt>
                                    <dd class="font-medium text-neutral-100">{{ strtoupper(request('webserver')) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-neutral-400">PHP</dt>
                                    <dd class="font-medium text-neutral-100">{{ request('php_version') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-neutral-400">Database</dt>
                                    <dd class="font-medium text-neutral-100">
                                        @if(request('database') === 'none')
                                            Nenhum
                                        @else
                                            {{ strtoupper(request('database')) }}
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-neutral-400">Cache</dt>
                                    <dd class="font-medium text-neutral-100">
                                        @if(request('cache') === 'none')
                                            Nenhum
                                        @else
                                            {{ ucfirst(request('cache')) }}
                                        @endif
                                    </dd>
                                </div>
                                @if(request('nodejs'))
                                    <div>
                                        <dt class="text-neutral-400">Node.js</dt>
                                        <dd class="font-medium text-neutral-100">{{ request('nodejs') }}.x</dd>
                                    </div>
                                @endif
                                @if(request('extras'))
                                    <div>
                                        <dt class="text-neutral-400">Extras</dt>
                                        <dd class="font-medium text-neutral-100">{{ implode(', ', array_map('ucfirst', request('extras'))) }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>

                        <!-- Cost Estimate -->
                        <div class="bg-info-900/20 border-2 border-blue-200 rounded-lg p-4">
                            <h3 class="font-semibold text-blue-900 mb-3">Custo Estimado</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-info-400">Instância EC2 ({{ $instanceType->name }})</dt>
                                    <dd class="font-medium text-blue-900">${{ number_format($cost['instance'], 2) }}/mês</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-info-400">Armazenamento EBS ({{ request('disk_size') }} GB)</dt>
                                    <dd class="font-medium text-blue-900">${{ number_format($cost['storage'], 2) }}/mês</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-info-400">Transferência de Dados</dt>
                                    <dd class="font-medium text-blue-900">${{ number_format($cost['transfer'], 2) }}/mês</dd>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-blue-200">
                                    <dt class="text-lg font-bold text-blue-900">Total Mensal</dt>
                                    <dd class="text-lg font-bold text-blue-900">${{ number_format($cost['total'], 2) }}</dd>
                                </div>
                                @if($instanceType->isGraviton())
                                    <div class="pt-2 border-t border-blue-200">
                                        <p class="text-xs text-success-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Você está economizando ~40% usando Graviton (ARM64) em vez de x86_64
                                        </p>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <div class="bg-warning-900/20 border border-yellow-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-warning-400">Importante</h3>
                                <div class="mt-2 text-sm text-warning-400">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>O provisionamento leva ~10-15 minutos</li>
                                        <li>Você será cobrado pela AWS assim que a instância for criada</li>
                                        <li>A senha do database será gerada automaticamente e armazenada no servidor</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-6 border-t">
                        <button type="button" onclick="history.back()" class="text-neutral-400 hover:text-neutral-800">
                            ← Voltar
                        </button>
                        <button 
                            type="submit" 
                            class="bg-success-500 hover:bg-success-700 text-white px-8 py-3 rounded font-medium text-lg"
                        >
                            🚀 Provisionar Servidor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
