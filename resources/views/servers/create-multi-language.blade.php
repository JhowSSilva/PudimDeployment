<x-layout>
    <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ route('servers.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                ← Back to Servers
            </a>
            <h1 class="mt-2 text-3xl font-bold text-neutral-900 dark:text-white">Create New Server</h1>
            <p class="mt-2 text-sm text-neutral-700 dark:text-neutral-300">Configure your server with modern multi-language support</p>
        </div>

        <!-- Progress Steps -->
        <div class="mb-8">
            <nav aria-label="Progress">
                <ol role="list" class="space-y-4 md:flex md:space-y-0 md:space-x-8">
                    <li class="md:flex-1">
                        <div class="group pl-4 py-2 flex flex-col border-l-4 border-primary-600 md:pl-0 md:pt-4 md:pb-0 md:border-l-0 md:border-t-4">
                            <span class="text-xs text-primary-600 font-semibold tracking-wide uppercase group-hover:text-primary-800">Step 01</span>
                            <span class="text-sm font-medium">Basic Configuration</span>
                        </div>
                    </li>
                    <li class="md:flex-1">
                        <div class="group pl-4 py-2 flex flex-col border-l-4 border-neutral-200 md:pl-0 md:pt-4 md:pb-0 md:border-l-0 md:border-t-4">
                            <span class="text-xs text-neutral-500 font-semibold tracking-wide uppercase">Step 02</span>
                            <span class="text-sm font-medium">Programming Language</span>
                        </div>
                    </li>
                    <li class="md:flex-1">
                        <div class="group pl-4 py-2 flex flex-col border-l-4 border-neutral-200 md:pl-0 md:pt-4 md:pb-0 md:border-l-0 md:border-t-4">
                            <span class="text-xs text-neutral-500 font-semibold tracking-wide uppercase">Step 03</span>
                            <span class="text-sm font-medium">Stack Configuration</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <form id="server-form" action="{{ route('servers.store') }}" method="POST" class="bg-white dark:bg-neutral-800 shadow sm:rounded-lg">
            @csrf
            
            <!-- Step 1: Basic Configuration -->
            <div id="step-1" class="step px-4 py-5 sm:p-6 space-y-6">
                <h3 class="text-lg font-medium text-neutral-900 dark:text-white">Server Details</h3>
                
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Server Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $suggested_name ?? '') }}" required
                        class="mt-1 block w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('name') border-red-300 @enderror"
                        placeholder="my-awesome-server">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- IP Address -->
                <div>
                    <label for="ip_address" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">IP Address</label>
                    <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address') }}" required 
                        placeholder="192.168.1.100"
                        class="mt-1 block w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('ip_address') border-red-300 @enderror">
                    @error('ip_address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <!-- SSH Port -->
                    <div>
                        <label for="ssh_port" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">SSH Port</label>
                        <input type="number" name="ssh_port" id="ssh_port" value="{{ old('ssh_port', 22) }}" required
                            class="mt-1 block w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('ssh_port') border-red-300 @enderror">
                        @error('ssh_port')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- OS -->
                    <div>
                        <label for="os" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Operating System</label>
                        <select name="os" id="os" required
                            class="mt-1 block w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('os') border-red-300 @enderror">
                            <option value="">Select OS</option>
                            <option value="ubuntu-22.04" {{ old('os') === 'ubuntu-22.04' ? 'selected' : '' }}>Ubuntu 22.04 LTS</option>
                            <option value="ubuntu-20.04" {{ old('os') === 'ubuntu-20.04' ? 'selected' : '' }}>Ubuntu 20.04 LTS</option>
                            <option value="ubuntu-24.04" {{ old('os') === 'ubuntu-24.04' ? 'selected' : '' }}>Ubuntu 24.04 LTS</option>
                        </select>
                        @error('os')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Server Type</label>
                    <select name="type" id="type" required
                        class="mt-1 block w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('type') border-red-300 @enderror">
                        <option value="">Select Type</option>
                        <option value="server" {{ old('type') === 'server' ? 'selected' : '' }}>Web Server</option>
                        <option value="database" {{ old('type') === 'database' ? 'selected' : '' }}>Database Server</option>
                        <option value="cache" {{ old('type') === 'cache' ? 'selected' : '' }}>Cache Server</option>
                        <option value="load_balancer" {{ old('type') === 'load_balancer' ? 'selected' : '' }}>Load Balancer</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Step 2: Programming Language -->
            <div id="step-2" class="step hidden px-4 py-5 sm:p-6 space-y-6">
                <h3 class="text-lg font-medium text-neutral-900 dark:text-white">Programming Language</h3>
                
                <!-- Language Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($languages as $lang => $langData)
                        <div class="language-option border-2 border-neutral-200 dark:border-neutral-600 rounded-lg p-4 cursor-pointer hover:border-primary-500 transition-colors"
                             data-language="{{ $lang }}"
                             onclick="selectLanguage('{{ $lang }}')">
                            <div class="flex flex-col items-center text-center">
                                <div class="w-16 h-16 mb-3 flex items-center justify-center bg-neutral-100 dark:bg-neutral-700 rounded-full">
                                    @if($lang === 'php')
                                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="#777BB4"><path d="M7.01 10.207h-.944l-.515 2.648h.838c.556 0 .982-.122 1.104-.472.097-.277-.089-.643-.483-2.176zM12 5.688C5.373 5.688 0 8.514 0 12s5.373 6.313 12 6.313S24 15.486 24 12s-5.373-6.312-12-6.312zM5.864 14.85c-.309.325-.778.477-1.431.477H2.78l.818-4.207h.43c1.378 0 2.017.422 1.865 1.185-.123.618-.553 1.146-1.029 1.422-.123.07-.231.123-.231.123zM9.864 11.188c-.092.395-.17.684-.17.684C9.77 11.262 9.648 11 9.648 11c-.062-.17-.062-.293-.062-.293-.123-.646-.939-.8-1.295-.8h-.755L7.216 11l-.925 4.76h1.108l.554-2.845s.185-1.163 1.353-1.163c1.107 0 1.185.708 1.185.708s-.062.277-.062.523zM17.341 14.85c-.309.325-.778.477-1.431.477h-1.653l.818-4.207h.43c1.378 0 2.017.422 1.865 1.185-.123.618-.553 1.146-1.029 1.422-.123.07-.231.123-.231.123zM21.341 11.188c-.092.395-.17.684-.17.684C21.247 11.262 21.125 11 21.125 11c-.062-.17-.062-.293-.062-.293-.123-.646-.939-.8-1.295-.8h-.755L18.693 11l-.925 4.76h1.108l.554-2.845s.185-1.163 1.353-1.163c1.107 0 1.185.708 1.185.708s-.062.277-.062.523zM18.374 10.207h-.944l-.515 2.648h.838c.556 0 .982-.122 1.104-.472.097-.277-.089-.643-.483-2.176z"/></svg>
                                    @elseif($lang === 'nodejs')
                                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="#68A063"><path d="M12 1.85c-.27 0-.55.07-.78.2l-7.44 4.3c-.48.28-.78.8-.78 1.36v8.58c0 .56.3 1.08.78 1.36l1.95 1.12c.97.56 1.59.56 2.12.56 1.76 0 2.77-1.07 2.77-2.93V7.61c0-.1-.08-.18-.19-.18H9.4c-.1 0-.19.08-.19.18v8.49c0 .9-.93 1.8-2.42 1.04L5.84 16.5c-.05-.03-.08-.08-.08-.14V7.78c0-.06.03-.12.08-.14l7.44-4.3c.05-.03.12-.03.17 0l7.44 4.3c.05.02.08.08.08.14v8.58c0 .06-.03.12-.08.14l-7.44 4.3c-.05.03-.12.03-.17 0l-1.88-1.12c-.02-.01-.04-.02-.07-.02-.04 0-.07.01-.1.04-.9.5-.64.69-.23.8l2.39 1.4c.24.13.5.2.78.2s.54-.07.78-.2l7.44-4.3c.48-.28.78-.8.78-1.36V7.78c0-.56-.3-1.08-.78-1.36l-7.44-4.3c-.23-.13-.51-.2-.78-.2z"/></svg>
                                    @elseif($lang === 'python')
                                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="#3776AB"><path d="M14.25.18l.9.2.73.26.59.3.45.32.34.34.25.34.16.33.1.3.04.26.02.2-.01.13V8.5l-.05.63-.13.55-.21.46-.26.38-.3.31-.33.25-.35.19-.35.14-.33.1-.3.07-.26.04-.21.02H8.77l-.69.05-.59.14-.5.22-.41.27-.33.32-.27.35-.2.36-.15.37-.1.35-.07.32-.04.27-.02.21v3.06H3.17l-.21-.03-.28-.07-.32-.12-.35-.18-.36-.26-.36-.36-.35-.46-.32-.59-.28-.73-.21-.88-.14-1.05-.05-1.23.06-1.22.16-1.04.24-.87.32-.71.36-.57.4-.44.42-.33.42-.24.4-.16.36-.1.32-.05.24-.01h.16l.06.01h8.16v-.83H6.18l-.01-2.75-.02-.37.05-.34.11-.31.17-.28.25-.26.31-.23.38-.2.44-.18.51-.15.58-.12.64-.1.71-.06.77-.04.84-.02 1.27.05zm-6.3 1.98l-.23.33-.08.41.08.41.23.34.33.22.41.09.41-.09.33-.22.23-.34.08-.41-.08-.41-.23-.33-.33-.22-.41-.09-.41.09zm13.09 3.95l.28.06.32.12.35.18.36.27.36.35.35.47.32.59.28.73.21.88.14 1.04.05 1.23-.06 1.23-.16 1.04-.24.86-.32.71-.36.57-.4.45-.42.33-.42.24-.4.16-.36.09-.32.05-.24.02-.16-.01h-8.22v.82h5.84l.01 2.76.02.36-.05.34-.11.31-.17.29-.25.25-.31.24-.38.2-.44.17-.51.15-.58.13-.64.09-.71.07-.77.04-.84.01-1.27-.04-1.07-.14-.9-.2-.73-.25-.59-.3-.45-.33-.34-.34-.25-.34-.16-.33-.1-.3-.04-.25-.02-.2.01-.13v-5.34l.05-.64.13-.54.21-.46.26-.38.3-.32.33-.24.35-.2.35-.14.33-.1.3-.06.26-.04.21-.02.13-.01h5.84l.69-.05.59-.14.5-.21.41-.28.33-.32.27-.35.2-.36.15-.36.1-.35.07-.32.04-.28.02-.21V6.07h2.09l.14.01zm-6.47 14.25l-.23.33-.08.41.08.41.23.33.33.23.41.08.41-.08.33-.23.23-.33.08-.41-.08-.41-.23-.33-.33-.23-.41-.08-.41.08z"/></svg>
                                    @else
                                        <div class="w-8 h-8 bg-neutral-500 rounded"></div>
                                    @endif
                                </div>
                                <h4 class="font-medium text-neutral-900 dark:text-white">{{ $langData['name'] }}</h4>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                                    {{ count($langData['versions']) }} versions available
                                </p>
                            </div>
                            <input type="radio" name="programming_language" value="{{ $lang }}" class="hidden">
                        </div>
                    @endforeach
                </div>

                <!-- Version Selection (Hidden initially) -->
                <div id="version-selection" class="hidden space-y-4">
                    <div class="border-t pt-4">
                        <h4 class="font-medium text-neutral-900 dark:text-white mb-4">Select Version</h4>
                        <select name="language_version" id="language_version" 
                            class="mt-1 block w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            <option value="">Choose a version</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Step 3: Stack Configuration (Will be dynamically loaded) -->
            <div id="step-3" class="step hidden px-4 py-5 sm:p-6 space-y-6">
                <h3 class="text-lg font-medium text-neutral-900 dark:text-white">Stack Configuration</h3>
                <div id="stack-configuration-content">
                    <!-- Dynamic content will be loaded here -->
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="px-4 py-3 bg-neutral-50 dark:bg-neutral-700 text-right sm:px-6 flex justify-between">
                <button type="button" id="prev-btn" 
                    class="hidden inline-flex justify-center py-2 px-4 border border-neutral-300 shadow-sm text-sm font-medium rounded-md text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Previous
                </button>
                
                <div class="flex space-x-3">
                    <button type="button" id="next-btn"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Next
                    </button>
                    
                    <button type="submit" id="submit-btn" 
                        class="hidden inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Create Server
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        let currentStep = 1;
        const availableLanguages = @json($languages);
        let selectedLanguage = null;

        function updateStepIndicator() {
            // Update progress steps visual indicator
            document.querySelectorAll('#step-1, #step-2, #step-3').forEach((el, index) => {
                if (index + 1 <= currentStep) {
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            });

            // Show/hide navigation buttons
            document.getElementById('prev-btn').classList.toggle('hidden', currentStep === 1);
            document.getElementById('next-btn').classList.toggle('hidden', currentStep === 3);
            document.getElementById('submit-btn').classList.toggle('hidden', currentStep !== 3);
        }

        function selectLanguage(language) {
            selectedLanguage = language;
            
            // Update visual selection
            document.querySelectorAll('.language-option').forEach(el => {
                el.classList.remove('border-primary-500', 'bg-primary-50', 'dark:bg-primary-900/20');
                el.classList.add('border-neutral-200', 'dark:border-neutral-600');
            });
            
            const selectedOption = document.querySelector(`[data-language="${language}"]`);
            selectedOption.classList.remove('border-neutral-200', 'dark:border-neutral-600');
            selectedOption.classList.add('border-primary-500', 'bg-primary-50', 'dark:bg-primary-900/20');
            
            // Update hidden input
            document.querySelector(`input[value="${language}"]`).checked = true;
            
            // Populate versions
            const versionSelect = document.getElementById('language_version');
            versionSelect.innerHTML = '<option value="">Choose a version</option>';
            
            const langData = availableLanguages[language];
            if (langData && langData.versions) {
                langData.versions.forEach(version => {
                    const option = document.createElement('option');
                    option.value = version;
                    option.textContent = `${langData.name} ${version}`;
                    versionSelect.appendChild(option);
                });
                
                // Show version selection
                document.getElementById('version-selection').classList.remove('hidden');
            }
            
            // Load stack configuration for step 3
            loadStackConfiguration(language);
        }

        function loadStackConfiguration(language) {
            const configContent = document.getElementById('stack-configuration-content');
            
            // Basic stack configuration
            let html = `
                <!-- Web Server -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Web Server</label>
                    <select name="webserver" class="mt-1 block w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="">None</option>
                        <option value="nginx">Nginx</option>
                        <option value="apache">Apache</option>
                        <option value="caddy">Caddy</option>
                    </select>
                </div>
                
                <!-- Database -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Database</label>
                    <select name="database_type" class="mt-1 block w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="">None</option>
                        <option value="mysql">MySQL</option>
                        <option value="mariadb">MariaDB</option>
                        <option value="postgresql">PostgreSQL</option>
                        <option value="mongodb">MongoDB</option>
                    </select>
                </div>
                
                <!-- Cache -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Cache Service</label>
                    <select name="cache_service" class="mt-1 block w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="">None</option>
                        <option value="redis">Redis</option>
                        <option value="memcached">Memcached</option>
                    </select>
                </div>
            `;
            
            // Language-specific configuration
            if (language === 'php') {
                html += `
                    <div class="border-t pt-4">
                        <h4 class="font-medium text-neutral-900 dark:text-white mb-4">PHP Configuration</h4>
                        <label class="flex items-center">
                            <input type="checkbox" name="install_composer" value="1" checked class="rounded border-neutral-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Install Composer</span>
                        </label>
                    </div>
                `;
            } else if (language === 'nodejs') {
                html += `
                    <div class="border-t pt-4">
                        <h4 class="font-medium text-neutral-900 dark:text-white mb-4">Node.js Configuration</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="install_yarn" value="1" checked class="rounded border-neutral-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Install Yarn</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="install_pm2" value="1" checked class="rounded border-neutral-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Install PM2</span>
                            </label>
                        </div>
                    </div>
                `;
            } else if (language === 'python') {
                html += `
                    <div class="border-t pt-4">
                        <h4 class="font-medium text-neutral-900 dark:text-white mb-4">Python Configuration</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="install_poetry" value="1" class="rounded border-neutral-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Install Poetry</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="install_pipenv" value="1" class="rounded border-neutral-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Install Pipenv</span>
                            </label>
                        </div>
                    </div>
                `;
            }
            
            configContent.innerHTML = html;
        }

        // Navigation
        document.getElementById('next-btn').addEventListener('click', () => {
            if (currentStep === 1) {
                // Validate step 1
                const requiredFields = ['name', 'ip_address', 'ssh_port', 'os', 'type'];
                let valid = true;
                
                requiredFields.forEach(field => {
                    const input = document.getElementById(field);
                    if (!input.value.trim()) {
                        valid = false;
                        input.classList.add('border-red-300');
                    } else {
                        input.classList.remove('border-red-300');
                    }
                });
                
                if (!valid) {
                    alert('Please fill in all required fields');
                    return;
                }
            } else if (currentStep === 2) {
                // Validate step 2
                if (!selectedLanguage) {
                    alert('Please select a programming language');
                    return;
                }
                
                const versionSelect = document.getElementById('language_version');
                if (!versionSelect.value) {
                    alert('Please select a language version');
                    return;
                }
            }
            
            currentStep++;
            updateStepIndicator();
        });

        document.getElementById('prev-btn').addEventListener('click', () => {
            currentStep--;
            updateStepIndicator();
        });

        // Initialize
        updateStepIndicator();
    </script>
    @endpush
</x-layout>