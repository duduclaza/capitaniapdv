<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? config('app.name')) ?> - Capitania PDV</title>
    <meta name="description" content="Sistema de gestão de bar - Capitania PDV">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50:  '#fdf4ff',
                            100: '#fae8ff',
                            200: '#f5d0fe',
                            300: '#f0abfc',
                            400: '#e879f9',
                            500: '#d946ef',
                            600: '#c026d3',
                            700: '#a21caf',
                            800: '#86198f',
                            900: '#701a75',
                            950: '#4a044e',
                        },
                        dark: {
                            800: '#1e1b2e',
                            900: '#13111e',
                            950: '#0b0914',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Sidebar active state */
        .nav-link.active { 
            background: linear-gradient(135deg, #c026d3, #a21caf);
            color: white;
            box-shadow: 0 4px 15px rgba(192, 38, 211, 0.4);
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #13111e; }
        ::-webkit-scrollbar-thumb { background: #4a044e; border-radius: 3px; }
        
        /* Card hover effect */
        .card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        
        /* Gradient background for sidebar */
        .sidebar-gradient {
            background: linear-gradient(180deg, #1e1b2e 0%, #13111e 100%);
            border-right: 1px solid rgba(192, 38, 211, 0.2);
        }
        
        /* Glassmorphism cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in-up { animation: fadeInUp 0.4s ease forwards; }
        
        /* Toast Notifications */
        #toast-container { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 100; pointer-events: none; }
        .toast {
            pointer-events: auto;
            background: rgba(30, 27, 46, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(192, 38, 211, 0.3);
            border-left: 4px solid #c026d3;
            color: white;
            padding: 1rem 1.25rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.75rem;
            min-width: 280px;
            animation: toastIn 0.3s ease forwards, toastOut 0.5s ease 3s forwards;
        }
        @keyframes toastIn { from { opacity:0; transform:translateY(100%); } to { opacity:1; transform:translateY(0); } }
        @keyframes toastOut { to { opacity:0; transform:translateX(100%); } }

        /* Flash messages */
        .flash-message { animation: fadeInUp 0.3s ease, fadeOut 0.5s ease 4s forwards; }
        @keyframes fadeOut {
            to { opacity: 0; transform: translateX(20px); pointer-events: none; }
        }
    </style>
</head>
<body class="bg-dark-950 text-gray-100 h-full flex overflow-hidden">

    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar-gradient w-64 flex-shrink-0 flex flex-col h-screen overflow-y-auto transition-all duration-300">
        
        <!-- Logo -->
        <div class="p-6 border-b border-purple-900/30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-primary-800 flex items-center justify-center shadow-lg shadow-primary-900/50">
                    <i data-lucide="anchor" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <span class="text-lg font-bold text-white">Capitania</span>
                    <span class="block text-xs text-primary-400 font-medium -mt-1">PDV Sistema</span>
                </div>
            </div>
        </div>

        <!-- User Info -->
        <div class="px-4 py-4 border-b border-purple-900/20">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-600 to-purple-800 flex items-center justify-center text-sm font-bold">
                    <?= strtoupper(substr(auth()['nome'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate"><?= e(auth()['nome'] ?? '') ?></p>
                    <p class="text-xs text-gray-400 capitalize"><?= e(auth()['perfil'] ?? '') ?></p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4 space-y-1">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2 mt-2">Principal</p>
            
            <a href="/dashboard" class="nav-link <?= isActive('/dashboard') ?> flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
            </a>
            <a href="/pdv" class="nav-link <?= isActive('/pdv') ?> flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i> PDV - Vendas
            </a>
            <a href="/comandas" class="nav-link <?= isActive('/comandas') ?> flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                <i data-lucide="clipboard-list" class="w-4 h-4"></i> Comandas
            </a>
            <a href="/mesas" class="nav-link <?= isActive('/mesas') ?> flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                <i data-lucide="grid-3x3" class="w-4 h-4"></i> Mesas
            </a>
            <a href="/caixa" class="nav-link <?= isActive('/caixa') ?> flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                <i data-lucide="landmark" class="w-4 h-4"></i> Caixa
            </a>
            
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2 mt-4">Cadastros</p>
            
            <a href="/produtos" class="nav-link <?= isActive('/produtos') ?> flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                <i data-lucide="package" class="w-4 h-4"></i> Produtos
            </a>
            <a href="/categorias" class="nav-link <?= isActive('/categorias') ?> flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                <i data-lucide="tag" class="w-4 h-4"></i> Categorias
            </a>
            <a href="/clientes" class="nav-link <?= isActive('/clientes') ?> flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                <i data-lucide="users" class="w-4 h-4"></i> Clientes
            </a>
            <a href="/fornecedores" class="nav-link <?= isActive('/fornecedores') ?> flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                <i data-lucide="truck" class="w-4 h-4"></i> Fornecedores
            </a>

            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2 mt-4">Operações</p>
            
            <a href="/estoque" class="nav-link <?= isActive('/estoque') ?> flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                <i data-lucide="boxes" class="w-4 h-4"></i> Estoque
            </a>
            <a href="/relatorios/vendas" class="nav-link <?= isActive('/relatorios') ?> flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i> Relatórios
            </a>
            <a href="/config" class="nav-link <?= isActive('/config') ?> flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                <i data-lucide="settings" class="w-4 h-4"></i> Configurações
            </a>
            <?php if (isAdmin()): ?>
            <a href="/usuarios" class="nav-link <?= isActive('/usuarios') ?> flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                <i data-lucide="user-cog" class="w-4 h-4"></i> Usuários
            </a>
            <?php endif; ?>
        </nav>

        <!-- Logout -->
        <div class="p-4 border-t border-purple-900/20">
            <a href="/logout" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:text-red-400 hover:bg-red-900/10 transition-all">
                <i data-lucide="log-out" class="w-4 h-4"></i> Sair
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- Top Bar -->
        <header class="flex-shrink-0 bg-dark-900/80 backdrop-blur border-b border-purple-900/20 px-6 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h1 class="text-base font-semibold text-white"><?= e($pageTitle ?? 'Dashboard') ?></h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-500"><?= date('d/m/Y H:i') ?></span>
                <div class="w-px h-4 bg-gray-700"></div>
                <a href="/pdv" class="flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition-all">
                    <i data-lucide="shopping-cart" class="w-3 h-3"></i> Abrir PDV
                </a>
            </div>
        </header>

        <!-- Flash Messages -->
        <?php $successMsg = getFlash('success'); $errorMsg = getFlash('error'); ?>
        <?php if ($successMsg || $errorMsg): ?>
        <div class="fixed top-4 right-4 z-50 space-y-2">
            <?php if ($successMsg): ?>
            <div class="flash-message flex items-center gap-3 bg-emerald-900/90 border border-emerald-500/30 text-emerald-200 px-4 py-3 rounded-xl shadow-lg max-w-sm">
                <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
                <span class="text-sm"><?= e($successMsg) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
            <div class="flash-message flex items-center gap-3 bg-red-900/90 border border-red-500/30 text-red-200 px-4 py-3 rounded-xl shadow-lg max-w-sm">
                <i data-lucide="x-circle" class="w-4 h-4 flex-shrink-0"></i>
                <span class="text-sm"><?= e($errorMsg) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-6">
            <?= $content ?>
        </main>
    </div>

    <div id="toast-container"></div>

    <script>
        lucide.createIcons();
        
        // Auto-dismiss flash messages
        setTimeout(() => {
            document.querySelectorAll('.flash-message').forEach(el => el.remove());
        }, 5000);

        // Toast Helper
        window.toast = function(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icon = type === 'success' ? 'check-circle' : (type === 'error' ? 'x-circle' : 'info');
            const color = type === 'success' ? 'text-emerald-400' : (type === 'error' ? 'text-red-400' : 'text-blue-400');
            
            toast.innerHTML = `
                <i data-lucide="${icon}" class="w-5 h-5 ${color}"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium">${message}</p>
                </div>
            `;
            
            container.appendChild(toast);
            lucide.createIcons();
            
            setTimeout(() => toast.remove(), 4000);
        };
    </script>
</body>
</html>
