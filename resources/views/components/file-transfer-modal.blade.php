<!-- File Transfer Modal Component (usar no terminal) -->
<div x-data="fileTransferModal()" x-show="show" @file-transfer-open.window="openModal()" 
     class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" @click="closeModal()"></div>
    
    <!-- Modal -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div @click.stop class="relative bg-neutral-800 rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden border border-neutral-700">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-700">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-white">File Transfer</h3>
                </div>
                <button @click="closeModal()" class="text-neutral-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="flex h-[70vh]">
                <!-- Upload Panel -->
                <div class="w-1/2 p-6 border-r border-neutral-700">
                    <h4 class="text-sm font-semibold text-primary-500 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Upload to Server
                    </h4>

                    <!-- Drag & Drop Zone -->
                    <div @drop.prevent="handleDrop($event)" 
                         @dragover.prevent="dragover = true" 
                         @dragleave.prevent="dragover = false"
                         :class="dragover ? 'border-primary-500 bg-primary-500/10' : 'border-neutral-600'"
                         class="border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer"
                         @click="$refs.fileInput.click()">
                        <svg class="w-12 h-12 mx-auto mb-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <p class="text-neutral-400 mb-2">Drag & drop files here</p>
                        <p class="text-xs text-neutral-500">or click to browse</p>
                        <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" class="hidden" multiple>
                    </div>

                    <!-- Upload Path -->
                    <div class="mt-4">
                        <label class="block text-xs text-neutral-400 mb-2">Remote Path</label>
                        <input type="text" x-model="uploadPath" 
                               class="w-full bg-neutral-900 border border-neutral-700 rounded px-3 py-2 text-sm text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                               placeholder="/home/user/uploads/">
                    </div>

                    <!-- Selected Files -->
                    <div x-show="selectedFiles.length > 0" class="mt-4">
                        <h5 class="text-xs font-semibold text-neutral-400 mb-2">Selected Files:</h5>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            <template x-for="(file, index) in selectedFiles" :key="index">
                                <div class="flex items-center justify-between bg-neutral-900 rounded px-3 py-2 text-xs">
                                    <div class="flex items-center gap-2 flex-1 min-w-0">
                                        <svg class="w-4 h-4 text-neutral-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-white truncate" x-text="file.name"></span>
                                        <span class="text-neutral-500" x-text="formatSize(file.size)"></span>
                                    </div>
                                    <button @click="removeFile(index)" class="text-error-400 hover:text-error-300 ml-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Upload Button -->
                    <button @click="uploadFiles()" :disabled="selectedFiles.length === 0 || uploading"
                            class="w-full mt-4 px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <svg x-show="!uploading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <svg x-show="uploading" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span x-text="uploading ? 'Uploading...' : 'Upload Files'"></span>
                    </button>
                </div>

                <!-- Browser/Download Panel -->
                <div class="w-1/2 p-6 flex flex-col">
                    <h4 class="text-sm font-semibold text-success-500 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download from Server
                    </h4>

                    <!-- Current Path -->
                    <div class="flex items-center gap-2 mb-4">
                        <input type="text" x-model="browsePath" @keyup.enter="loadFiles()"
                               class="flex-1 bg-neutral-900 border border-neutral-700 rounded px-3 py-2 text-sm text-white focus:ring-2 focus:ring-success-500 focus:border-transparent"
                               placeholder="/home/user/">
                        <button @click="loadFiles()" :disabled="loading"
                                class="px-3 py-2 bg-neutral-700 text-white rounded hover:bg-neutral-600 transition disabled:opacity-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- File List -->
                    <div class="flex-1 overflow-y-auto bg-neutral-900 rounded border border-neutral-700">
                        <div x-show="loading" class="flex items-center justify-center h-full text-neutral-500">
                            <svg class="w-8 h-8 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </div>

                        <template x-if="!loading && files.length === 0">
                            <div class="flex flex-col items-center justify-center h-full text-neutral-500 p-8 text-center">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p>No files found</p>
                            </div>
                        </template>

                        <template x-if="!loading && files.length > 0">
                            <table class="w-full text-xs">
                                <thead class="bg-neutral-800 sticky top-0">
                                    <tr class="text-left text-neutral-400">
                                        <th class="px-3 py-2">Name</th>
                                        <th class="px-3 py-2">Size</th>
                                        <th class="px-3 py-2 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-800">
                                    <template x-for="file in files" :key="file.name">
                                        <tr class="hover:bg-neutral-800/50 text-white">
                                            <td class="px-3 py-2">
                                                <div class="flex items-center gap-2">
                                                    <svg x-show="file.type === 'directory'" class="w-4 h-4 text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                                    </svg>
                                                    <svg x-show="file.type === 'file'" class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    <span x-text="file.name"></span>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 text-neutral-400" x-text="file.size"></td>
                                            <td class="px-3 py-2 text-right">
                                                <button x-show="file.type === 'directory'" @click="navigateTo(file.name)"
                                                        class="text-info-400 hover:text-info-300">
                                                    Open
                                                </button>
                                                <button x-show="file.type === 'file'" @click="downloadFile(file.name)"
                                                        class="text-success-400 hover:text-success-300">
                                                    Download
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fileTransferModal() {
    return {
        show: false,
        serverId: null,
        selectedFiles: [],
        uploadPath: '/home/',
        browsePath: '~',
        files: [],
        loading: false,
        uploading: false,
        dragover: false,
        
        openModal() {
            this.show = true;
            this.serverId = window.currentServerId;
            this.loadFiles();
        },
        
        closeModal() {
            this.show = false;
            this.selectedFiles = [];
        },
        
        handleDrop(e) {
            this.dragover = false;
            this.selectedFiles = Array.from(e.dataTransfer.files);
        },
        
        handleFileSelect(e) {
            this.selectedFiles = Array.from(e.target.files);
        },
        
        removeFile(index) {
            this.selectedFiles.splice(index, 1);
        },
        
        formatSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        },
        
        async uploadFiles() {
            if (this.selectedFiles.length === 0) return;
            
            this.uploading = true;
            
            for (const file of this.selectedFiles) {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('remote_path', this.uploadPath + file.name);
                
                try {
                    const response = await fetch(`/servers/${this.serverId}/files/upload`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        console.log(`Uploaded: ${file.name}`);
                    } else {
                        alert(`Error uploading ${file.name}: ${result.error}`);
                    }
                } catch (error) {
                    alert(`Error uploading ${file.name}: ${error.message}`);
                }
            }
            
            this.uploading = false;
            this.selectedFiles = [];
            this.loadFiles();
        },
        
        async loadFiles() {
            this.loading = true;
            
            try {
                const response = await fetch(`/servers/${this.serverId}/files/list?path=${encodeURIComponent(this.browsePath)}`);
                const result = await response.json();
                
                if (result.success) {
                    this.files = result.files;
                } else {
                    alert('Error loading files: ' + result.error);
                }
            } catch (error) {
                alert('Error loading files: ' + error.message);
            }
            
            this.loading = false;
        },
        
        navigateTo(dirname) {
            this.browsePath = this.browsePath.replace(/\/$/, '') + '/' + dirname;
            this.loadFiles();
        },
        
        async downloadFile(filename) {
            const remotePath = this.browsePath.replace(/\/$/, '') + '/' + filename;
            
            try {
                const response = await fetch(`/servers/${this.serverId}/files/download`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ remote_path: remotePath })
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    a.click();
                    window.URL.revokeObjectURL(url);
                } else {
                    const result = await response.json();
                    alert('Error downloading file: ' + result.error);
                }
            } catch (error) {
                alert('Error downloading file: ' + error.message);
            }
        }
    }
}
</script>
