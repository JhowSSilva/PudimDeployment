<x-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-600 text-white font-semibold">
                                ✓
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-600">Credenciais AWS</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 border-t-2 border-green-600 mx-4"></div>
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white font-semibold">
                                2
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-600">Configurar Instância</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 border-t-2 border-neutral-300 mx-4"></div>
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-neutral-300 text-neutral-600 font-semibold">
                                3
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-neutral-500">Stack</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 border-t-2 border-neutral-300 mx-4"></div>
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-neutral-300 text-neutral-600 font-semibold">
                                4
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-neutral-500">Revisar</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-8">
                <h2 class="text-2xl font-bold mb-2">Configure a Instância EC2</h2>
                <p class="text-neutral-600 mb-6">Selecione a região, tipo de instância e tamanho do disco</p>

                <form action="{{ route('aws-provision.step3') }}" method="POST">
                    @csrf
                    <input type="hidden" name="aws_credential_id" value="{{ $credential->id }}">

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                        <!-- Region -->
                        <div>
                            <label for="region" class="block text-sm font-medium text-neutral-700 mb-2">
                                Região AWS *
                            </label>
                            <select 
                                name="region" 
                                id="region"
                                class="w-full border border-neutral-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                            >
                                <option value="">Selecione uma região</option>
                                @foreach($regions as $code => $name)
                                    <option value="{{ $code }}" {{ old('region', $credential->default_region) === $code ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Disk Size -->
                        <div>
                            <label for="disk_size" class="block text-sm font-medium text-neutral-700 mb-2">
                                Tamanho do Disco (GB) *
                            </label>
                            <input 
                                type="number" 
                                name="disk_size" 
                                id="disk_size"
                                value="{{ old('disk_size', 30) }}"
                                min="20"
                                max="1000"
                                class="w-full border border-neutral-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                            >
                            <p class="text-xs text-neutral-500 mt-1">Entre 20 GB e 1000 GB</p>
                        </div>

                        <!-- Architecture Filter -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">
                                Filtrar por Arquitetura
                            </label>
                            <div class="flex gap-2">
                                <button 
                                    type="button" 
                                    onclick="filterArchitecture('all')"
                                    class="filter-btn flex-1 px-3 py-2 border border-neutral-300 rounded text-sm bg-blue-600 text-white"
                                    data-filter="all"
                                >
                                    Todas
                                </button>
                                <button 
                                    type="button" 
                                    onclick="filterArchitecture('arm64')"
                                    class="filter-btn flex-1 px-3 py-2 border border-neutral-300 rounded text-sm hover:bg-neutral-50"
                                    data-filter="arm64"
                                >
                                    ARM64
                                </button>
                                <button 
                                    type="button" 
                                    onclick="filterArchitecture('x86_64')"
                                    class="filter-btn flex-1 px-3 py-2 border border-neutral-300 rounded text-sm hover:bg-neutral-50"
                                    data-filter="x86_64"
                                >
                                    x86_64
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Instance Types -->
                    @foreach(['arm64' => 'ARM64 (Graviton) - 40% mais barato', 'x86_64' => 'x86_64 (Intel/AMD)'] as $arch => $label)
                        @if(isset($instanceTypes[$arch]))
                            <div class="mb-8 instance-group" data-architecture="{{ $arch }}">
                                <h3 class="text-lg font-semibold mb-4 flex items-center">
                                    {{ $label }}
                                    @if($arch === 'arm64')
                                        <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">ECONOMIA</span>
                                    @endif
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    @foreach($instanceTypes[$arch] as $instance)
                                        <label class="relative">
                                            <input 
                                                type="radio" 
                                                name="instance_type" 
                                                value="{{ $instance->name }}"
                                                class="peer sr-only"
                                                required
                                            >
                                            <div class="border-2 border-neutral-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 rounded-lg p-4 cursor-pointer hover:border-neutral-400 transition h-full">
                                                <div class="flex items-start justify-between mb-2">
                                                    <h4 class="font-semibold">{{ $instance->name }}</h4>
                                                    <svg class="w-5 h-5 text-blue-600 hidden peer-checked:block flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                                
                                                <div class="space-y-1 text-sm text-neutral-600 mb-3">
                                                    <div class="flex justify-between">
                                                        <span>vCPU:</span>
                                                        <span class="font-medium">{{ $instance->vcpu }}</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span>RAM:</span>
                                                        <span class="font-medium">{{ $instance->memory_gib }} GB</span>
                                                    </div>
                                                </div>

                                                <div class="pt-3 border-t">
                                                    <div class="text-xs text-neutral-500">Por hora</div>
                                                    <div class="text-sm font-medium text-neutral-700">${{ number_format($instance->price_per_hour, 4) }}</div>
                                                    <div class="text-lg font-bold text-neutral-900 mt-1">
                                                        ${{ number_format($instance->price_per_month, 2) }}/mês
                                                    </div>
                                                </div>

                                                @if($arch === 'arm64')
                                                    <div class="mt-2">
                                                        <span class="inline-block px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">
                                                            -40% vs x86
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @error('instance_type')
                        <p class="text-red-500 text-sm mb-4">{{ $message }}</p>
                    @enderror

                    <div class="flex justify-between items-center pt-6 border-t">
                        <a href="{{ route('aws-provision.step1') }}" class="text-neutral-600 hover:text-neutral-800">
                            ← Voltar
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-medium">
                            Próxima Etapa →
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function filterArchitecture(arch) {
            const groups = document.querySelectorAll('.instance-group');
            const buttons = document.querySelectorAll('.filter-btn');

            // Update button states
            buttons.forEach(btn => {
                if (btn.dataset.filter === arch) {
                    btn.classList.add('bg-blue-600', 'text-white');
                    btn.classList.remove('hover:bg-neutral-50');
                } else {
                    btn.classList.remove('bg-blue-600', 'text-white');
                    btn.classList.add('hover:bg-neutral-50');
                }
            });

            // Show/hide groups
            groups.forEach(group => {
                if (arch === 'all' || group.dataset.architecture === arch) {
                    group.style.display = 'block';
                } else {
                    group.style.display = 'none';
                }
            });
        }
    </script>
</x-layout>
