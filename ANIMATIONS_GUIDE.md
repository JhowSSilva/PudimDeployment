# Animações e Loading States - Guia de Uso

## Componentes Criados

### 1. **Loading Spinner** (`<x-loading />`)

Spinner animado com múltiplos estilos:

```blade
<!-- Default circular spinner -->
<x-loading />

<!-- Com texto -->
<x-loading>Carregando...</x-loading>

<!-- Tamanhos -->
<x-loading size="sm" />
<x-loading size="lg" />

<!-- Tipos -->
<x-loading type="dots" />     <!-- 3 pontos pulando -->
<x-loading type="pulse" />    <!-- Círculo pulsante -->
<x-loading type="bars" />     <!-- Barras animadas -->
```

### 2. **Skeleton Loader** (`<x-skeleton />`)

Placeholders animados para conteúdo carregando:

```blade
<!-- Card skeleton -->
<x-skeleton type="card" />

<!-- Table row skeleton -->
<tbody>
    <x-skeleton type="table-row" />
    <x-skeleton type="table-row" />
    <x-skeleton type="table-row" />
</tbody>

<!-- List item skeleton -->
<x-skeleton type="list-item" />

<!-- Avatar -->
<x-skeleton type="avatar" size="12" />

<!-- Linha de texto -->
<x-skeleton type="text" height="4" width="w-2/3" />

<!-- Custom skeleton -->
<x-skeleton>
    <div class="h-4 bg-neutral-200 rounded w-full"></div>
    <div class="h-4 bg-neutral-200 rounded w-3/4"></div>
</x-skeleton>
```

### 3. **Toast Notifications** (`<x-toast-container />`)

Sistema de notificações toast:

```javascript
// JavaScript - Use em qualquer lugar
toast.success('Operação concluída', 'Servidor criado com sucesso');
toast.error('Erro ao conectar', 'Verifique as credenciais');
toast.warning('Aviso', 'Espaço em disco baixo');
toast.info('Informação', 'Nova versão disponível');

// Com duração personalizada (ms)
toast.success('Salvo!', 'Dados salvos', 3000);

// Sem auto-fechar
showToast('info', 'Mensagem', 'Descrição', 0);
```

### 4. **Loading Overlay** (`<x-loading-overlay />`)

Overlay de carregamento de tela cheia:

```javascript
// JavaScript
showLoading();  // Mostra overlay

// Fazer operação async
await fetch('/api/endpoint');

hideLoading();  // Esconde overlay
```

```blade
<!-- Blade com customização -->
<x-loading-overlay 
    title="Processando..."
    message="Aguarde enquanto conectamos ao servidor"
    cancellable />
```

### 5. **Progress Bar** (`<x-progress />`)

Barra de progresso determinada ou indeterminada:

```blade
<!-- Barra de progresso normal -->
<x-progress value="75" label="Upload em progresso" />

<!-- Indeterminada -->
<x-progress indeterminate label="Processando..." />

<!-- Com descrição -->
<x-progress 
    value="50" 
    label="Backup em andamento"
    description="50% - 2.5GB de 5GB" />

<!-- Altura customizada -->
<x-progress value="80" height="4" />
```

### 6. **Page Transition** (`<x-page-transition />`)

Transições suaves entre páginas (já integrado no layout):

```blade
<!-- Automático no layout -->
<x-page-transition>
    <!-- Conteúdo da página -->
</x-page-transition>
```

## Exemplos Práticos

### Exemplo 1: Lista com Loading State

```blade
<div x-data="{ loading: true }">
    <!-- Skeleton enquanto carrega -->
    <template x-if="loading">
        <div class="space-y-4">
            <x-skeleton type="list-item" />
            <x-skeleton type="list-item" />
            <x-skeleton type="list-item" />
        </div>
    </template>
    
    <!-- Conteúdo real -->
    <template x-if="!loading">
        <div class="space-y-4">
            @foreach($servers as $server)
                <div class="bg-white p-4 rounded-lg">
                    {{ $server->name }}
                </div>
            @endforeach
        </div>
    </template>
</div>
```

### Exemplo 2: Botão com Loading

```blade
<button @click="loading = true; await saveData(); loading = false; toast.success('Salvo!')"
        :disabled="loading"
        class="px-4 py-2 bg-amber-600 text-white rounded hover-scale">
    <x-loading x-show="loading" size="sm" class="mr-2" />
    <span x-text="loading ? 'Salvando...' : 'Salvar'"></span>
</button>
```

### Exemplo 3: Upload com Progress

```blade
<div x-data="{ uploading: false, progress: 0 }">
    <input type="file" @change="uploadFile">
    
    <x-progress 
        x-show="uploading"
        :value="progress"
        label="Upload em andamento" />
</div>

<script>
async function uploadFile(event) {
    this.uploading = true;
    const file = event.target.files[0];
    
    const xhr = new XMLHttpRequest();
    xhr.upload.addEventListener('progress', (e) => {
        this.progress = (e.loaded / e.total) * 100;
    });
    
    xhr.onload = () => {
        this.uploading = false;
        toast.success('Upload completo!');
    };
    
    xhr.open('POST', '/upload');
    xhr.send(new FormData().append('file', file));
}
</script>
```

### Exemplo 4: Formulário com Validação

```blade
<form @submit.prevent="submitForm">
    <!-- Campos do formulário -->
    
    <button type="submit" 
            @click="showLoading()"
            class="px-6 py-2 bg-amber-600 text-white rounded hover-scale">
        Criar Servidor
    </button>
</form>

<script>
async function submitForm() {
    showLoading();
    
    try {
        const response = await fetch('/api/servers', {
            method: 'POST',
            body: new FormData(this.$el)
        });
        
        if (response.ok) {
            toast.success('Sucesso!', 'Servidor criado com sucesso');
            window.location = '/servers';
        } else {
            toast.error('Erro', 'Falha ao criar servidor');
        }
    } catch (error) {
        toast.error('Erro', error.message);
    } finally {
        hideLoading();
    }
}
</script>
```

### Exemplo 5: Tabela com Skeleton

```blade
<table class="min-w-full">
    <thead>
        <tr>
            <th>Nome</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody x-data="{ loading: true }" x-init="fetchData().then(() => loading = false)">
        <!-- Skeleton rows -->
        <template x-if="loading">
            @for($i = 0; $i < 5; $i++)
                <x-skeleton type="table-row" />
            @endfor
        </template>
        
        <!-- Real data -->
        <template x-if="!loading">
            @foreach($data as $item)
                <tr class="hover-scale">
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->status }}</td>
                    <td>
                        <button @click="deleteItem(); toast.success('Deletado!')">
                            Deletar
                        </button>
                    </td>
                </tr>
            @endforeach
        </template>
    </tbody>
</table>
```

## Classes CSS de Animação

```css
/* Fade in (já disponível) */
.fade-in { animation: fadeIn 0.3s ease-out; }

/* Hover scale (já disponível) */
.hover-scale { transition: transform 0.2s; }
.hover-scale:hover { transform: scale(1.02); }

/* Smooth scroll (automático) */
html { scroll-behavior: smooth; }
```

## Boas Práticas

1. **Use skeletons para listas/tabelas** - Melhor UX que spinners genéricos
2. **Toast para feedback** - Sempre confirme ações do usuário
3. **Loading overlay para operações longas** - Bloqueia UI durante processo
4. **Progress bars para uploads/downloads** - Mostre progresso real
5. **Hover scale em cards/botões** - Micro-interação sutil
6. **Fade-in em páginas** - Transição suave no carregamento

## Próximos Passos

✅ Sistema completo de animações implementado
✅ Toast notifications globais
✅ Loading states reutilizáveis
✅ Skeleton screens
✅ Progress bars
✅ Page transitions

**Todos os componentes estão prontos para uso em produção!**
