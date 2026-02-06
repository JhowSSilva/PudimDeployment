# Interface Livewire - Guia de Uso

## Dashboard Principal

A interface Livewire está configurada e rodando em `http://127.0.0.1:8000/dashboard`.

### Componentes Criados

#### 1. Dashboard Principal (`resources/views/livewire/dashboard.blade.php`)
- **Cards de Estatísticas**: Total de servidores, servidores online e offline
- **Lista de Servidores**: Mostra todos os servidores com métricas em tempo real
- **Gráfico de Métricas**: Exibe gráficos de CPU/RAM dos últimos 60 minutos
- **Deployments Recentes**: Lista dos últimos 5 deployments realizados

#### 2. Server Card (`resources/views/livewire/server-card.blade.php`)
- Exibe informações do servidor (nome, IP, sistema operacional)
- Badge de status (online/offline) com código de cores
- Barras de progresso para CPU, RAM e Disco
  - Verde: < 60%
  - Amarelo: 60-80%
  - Vermelho: > 80%
- Informações de uptime
- Timestamp da última atualização

#### 3. Deployment List (`resources/views/livewire/deployment-list.blade.php`)
- Lista os últimos 5 deployments
- Ícones de status com Heroicons:
  - ✓ Success (verde)
  - ✗ Failed (vermelho)
  - ↻ Running (azul, animado)
  - ⏰ Pending (amarelo)
- Informações: domínio, mensagem do commit, usuário, tempo decorrido, duração

#### 4. Server Metrics Chart (`resources/views/livewire/server-metrics-chart.blade.php`)
- Gráficos de linha com Chart.js
- Mostra CPU e RAM dos últimos 60 minutos
- Auto-refresh a cada 60 segundos
- Responsivo e interativo

### Layout

O layout principal (`resources/views/components/layout.blade.php`) inclui:
- Navbar com logo do projeto
- Menu de navegação
- Área de conteúdo principal
- Livewire Scripts
- Chart.js CDN

### Tecnologias Utilizadas

- **Livewire 4.1.2**: Framework reativo para Laravel
- **Tailwind CSS 3.x**: Framework CSS utility-first
- **Heroicons**: Ícones SVG oficiais do Tailwind
- **Chart.js 4.4.0**: Biblioteca de gráficos JavaScript
- **Vite**: Build tool para assets

### Como Testar

1. **Criar um servidor de teste via API**:
```bash
curl -X POST http://127.0.0.1:8000/api/servers \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "name": "Servidor Produção",
    "ip_address": "192.168.1.100",
    "ssh_port": 22,
    "ssh_user": "ubuntu",
    "auth_type": "password",
    "ssh_password": "senha123"
  }'
```

2. **Executar coleta de métricas**:
```bash
php artisan queue:work --once
```

3. **Criar um deployment de teste**:
```bash
curl -X POST http://127.0.0.1:8000/api/deployments \
  -H "Content-Type: application/json" \
  -d '{
    "site_id": 1,
    "user_id": 1,
    "status": "success",
    "commit_hash": "abc123def456",
    "commit_message": "Fix: Correção de bug no login"
  }'
```

4. **Acessar o dashboard**: `http://127.0.0.1:8000/dashboard`

### Atualização em Tempo Real

O dashboard possui refresh automático:
- **Server Card**: Atualiza a cada requisição
- **Metrics Chart**: Auto-refresh a cada 60 segundos
- **Botão "Atualizar"**: Recarrega manualmente as estatísticas e lista de servidores

### Comandos Úteis

```bash
# Iniciar servidor Laravel
php artisan serve

# Compilar assets em desenvolvimento (watch mode)
/usr/bin/npm run dev

# Compilar assets para produção
/usr/bin/npm run build

# Processar fila de jobs (métricas e deployments)
php artisan queue:work

# Executar scheduler (coleta de métricas a cada minuto)
php artisan schedule:work
```

### Próximos Passos Sugeridos

1. **Adicionar Autenticação**:
   - Laravel Breeze ou Jetstream
   - Proteção de rotas com middleware auth

2. **CRUD de Servidores**:
   - Criar componentes Livewire para adicionar/editar/deletar servidores
   - Formulários com validação em tempo real

3. **CRUD de Sites**:
   - Gerenciar sites/apps em cada servidor
   - Configuração de Nginx por site

4. **Página de Detalhes do Servidor**:
   - Métricas detalhadas
   - Logs do sistema
   - Gerenciamento de processos
   - Terminal SSH web

5. **Notificações**:
   - Alertas quando métricas ultrapassam limites
   - Notificações de deploy (sucesso/falha)
   - Toasts com Livewire

6. **Melhorias de UX**:
   - Loading states
   - Skeleton loaders
   - Animações com Alpine.js
   - Dark mode

### Estrutura de Arquivos

```
resources/
├── views/
│   ├── components/
│   │   └── layout.blade.php           # Layout principal
│   └── livewire/
│       ├── dashboard.blade.php         # Dashboard principal
│       ├── server-card.blade.php       # Card de servidor
│       ├── deployment-list.blade.php   # Lista de deployments
│       └── server-metrics-chart.blade.php  # Gráficos de métricas
├── css/
│   └── app.css                         # Estilos Tailwind
└── js/
    └── app.js                          # JavaScript base

routes/
└── web.php                             # Rotas web (dashboard)

tailwind.config.js                      # Configuração Tailwind
vite.config.js                         # Configuração Vite
package.json                           # Dependências Node.js
```

### Observações Importantes

1. **Node.js no WSL**: Use `/usr/bin/npm` para evitar conflitos com npm do Windows
2. **Assets**: Sempre compile os assets após modificações no CSS/JS
3. **Livewire Volt**: Componentes usam a sintaxe Volt (single-file components)
4. **Chart.js**: Carregado via CDN, mas pode ser instalado via npm se preferir
5. **Métricas**: Dependem do job `CollectServerMetrics` rodando a cada minuto

### Solução de Problemas

**CSS não aparece**:
```bash
/usr/bin/npm run build
php artisan optimize:clear
```

**Componentes Livewire não carregam**:
```bash
php artisan livewire:publish --assets
php artisan optimize:clear
```

**Gráficos não aparecem**:
- Verifique se Chart.js está carregado no DevTools (Network tab)
- Certifique-se de que há métricas no banco de dados

**Erro 500**:
```bash
php artisan optimize:clear
tail -f storage/logs/laravel.log
```
