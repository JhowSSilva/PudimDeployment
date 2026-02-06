// Gerenciamento de Chaves SSH

document.addEventListener('DOMContentLoaded', function() {
    // Carregar chaves
    loadKeys();

    // Botão gerar chave
    document.getElementById('btn-generate-key').addEventListener('click', function() {
        showModal('modal-generate');
    });

    // Botão importar chave
    document.getElementById('btn-import-key').addEventListener('click', function() {
        showModal('modal-import');
    });

    // Cancelar geração
    document.getElementById('btn-cancel-generate').addEventListener('click', function() {
        hideModal('modal-generate');
        document.getElementById('form-generate-key').reset();
    });

    // Cancelar importação
    document.getElementById('btn-cancel-import').addEventListener('click', function() {
        hideModal('modal-import');
        document.getElementById('form-import-key').reset();
    });

    // Fechar modal de chave pública
    document.getElementById('btn-close-public').addEventListener('click', function() {
        hideModal('modal-view-public');
    });

    // Copiar chave pública
    document.getElementById('btn-copy-public').addEventListener('click', function() {
        const content = document.getElementById('public-key-content');
        content.select();
        document.execCommand('copy');
        
        const btn = this;
        const originalText = btn.textContent;
        btn.textContent = '✓ Copiado!';
        btn.classList.remove('bg-yellow-600', 'hover:bg-yellow-700');
        btn.classList.add('bg-green-600', 'hover:bg-green-700');
        
        setTimeout(() => {
            btn.textContent = originalText;
            btn.classList.remove('bg-green-600', 'hover:bg-green-700');
            btn.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
        }, 2000);
    });

    // Form: Gerar chave
    document.getElementById('form-generate-key').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        try {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Gerando...';

            const response = await fetch('/api/ssh/keys/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                showNotification('Chave SSH gerada com sucesso!', 'success');
                hideModal('modal-generate');
                this.reset();
                loadKeys();
                
                // Mostrar chave pública
                setTimeout(() => {
                    viewPublicKey(result.key.id);
                }, 500);
            } else {
                showNotification(result.message || 'Erro ao gerar chave', 'error');
            }
        } catch (error) {
            console.error('Erro:', error);
            showNotification('Erro ao gerar chave SSH', 'error');
        } finally {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = false;
            btn.textContent = 'Gerar Chave';
        }
    });

    // Form: Importar chave
    document.getElementById('form-import-key').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        try {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Importando...';

            const response = await fetch('/api/ssh/keys/import', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                showNotification('Chave SSH importada com sucesso!', 'success');
                hideModal('modal-import');
                this.reset();
                loadKeys();
            } else {
                showNotification(result.message || 'Erro ao importar chave', 'error');
            }
        } catch (error) {
            console.error('Erro:', error);
            showNotification('Erro ao importar chave SSH', 'error');
        } finally {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = false;
            btn.textContent = 'Importar';
        }
    });
});

// Carregar lista de chaves
async function loadKeys() {
    try {
        const response = await fetch('/api/ssh/keys');
        const data = await response.json();

        const container = document.getElementById('keys-list');

        if (!data.success || !data.keys || data.keys.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Nenhuma chave SSH encontrada</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Clique em "Gerar Nova Chave" para começar</p>
                </div>
            `;
            return;
        }

        container.innerHTML = data.keys.map(key => createKeyCard(key)).join('');
    } catch (error) {
        console.error('Erro ao carregar chaves:', error);
        showNotification('Erro ao carregar chaves SSH', 'error');
    }
}

// Criar card de chave
function createKeyCard(key) {
    const createdAt = new Date(key.created_at).toLocaleDateString('pt-BR');
    
    return `
        <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between space-y-4 lg:space-y-0">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-1">
                        <h3 class="font-semibold text-gray-900 dark:text-white">${escapeHtml(key.name)}</h3>
                        ${key.has_passphrase ? '<span class="text-xs bg-yellow-600 text-black px-2 py-0.5 rounded">Protegida</span>' : ''}
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">${key.type} ${key.bits} bits</p>
                    <p class="text-xs text-gray-500 font-mono mt-1">Fingerprint: ${escapeHtml(key.fingerprint)}</p>
                    <p class="text-xs text-gray-500 mt-1">Criada em: ${createdAt}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button onclick="viewPublicKey(${key.id})" class="text-yellow-600 hover:text-yellow-700 px-3 py-1 rounded border border-yellow-600 transition duration-200 text-sm">
                        Ver Chave Pública
                    </button>
                    <button onclick="deleteKey(${key.id}, '${escapeHtml(key.name)}')" class="text-red-600 hover:text-red-700 px-3 py-1 rounded border border-red-600 transition duration-200 text-sm">
                        Deletar
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Ver chave pública
async function viewPublicKey(keyId) {
    try {
        const response = await fetch(`/api/ssh/keys/${keyId}/public`);
        const data = await response.json();

        if (data.success) {
            document.getElementById('public-key-content').value = data.public_key;
            showModal('modal-view-public');
        } else {
            showNotification(data.message || 'Erro ao obter chave pública', 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        showNotification('Erro ao obter chave pública', 'error');
    }
}

// Deletar chave
async function deleteKey(keyId, keyName) {
    if (!confirm(`Tem certeza que deseja deletar a chave "${keyName}"?\n\nEsta ação não pode ser desfeita.`)) {
        return;
    }

    try {
        const response = await fetch(`/api/ssh/keys/${keyId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Chave SSH deletada com sucesso', 'success');
            loadKeys();
        } else {
            showNotification(data.message || 'Erro ao deletar chave', 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        showNotification('Erro ao deletar chave SSH', 'error');
    }
}

// Utilitários
function showModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function hideModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function showNotification(message, type = 'info') {
    // Criar notificação
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 ${
        type === 'success' ? 'bg-green-600 text-white' :
        type === 'error' ? 'bg-red-600 text-white' :
        'bg-blue-600 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remover após 3s
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
