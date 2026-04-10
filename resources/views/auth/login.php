<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Capitania PDV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            400: '#e879f9', 500: '#d946ef', 600: '#c026d3',
                            700: '#a21caf', 800: '#86198f', 900: '#701a75',
                        },
                        dark: { 800: '#1e1b2e', 900: '#13111e', 950: '#0b0914' }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg {
            background: radial-gradient(ellipse at top left, rgba(192, 38, 211, 0.15) 0%, transparent 60%),
                        radial-gradient(ellipse at bottom right, rgba(109, 40, 217, 0.1) 0%, transparent 60%),
                        #0b0914;
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }
        .animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }
    </style>
</head>
<body class="gradient-bg h-full flex items-center justify-center">

    <!-- Decorative circles -->
    <div class="fixed top-0 left-0 w-96 h-96 bg-primary-700/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2 animate-pulse-slow"></div>
    <div class="fixed bottom-0 right-0 w-80 h-80 bg-purple-800/10 rounded-full blur-3xl translate-x-1/2 translate-y-1/2 animate-pulse-slow" style="animation-delay:1.5s"></div>

    <div class="relative w-full max-w-md px-4">
        
        <!-- Logo -->
        <div class="text-center mb-10">
            <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-primary-500 to-primary-800 flex items-center justify-center mx-auto mb-4 shadow-2xl shadow-primary-900/50">
                <i data-lucide="anchor" class="w-10 h-10 text-white"></i>
            </div>
            <h1 class="text-3xl font-bold text-white">Capitania PDV</h1>
            <p class="text-gray-400 mt-1">Sistema de Gestão de Bar</p>
        </div>

        <!-- Flash messages -->
        <?php $errorMsg = getFlash('error'); ?>
        <?php if ($errorMsg): ?>
        <div class="mb-4 flex items-center gap-3 bg-red-900/50 border border-red-500/30 text-red-200 px-4 py-3 rounded-xl">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
            <span class="text-sm"><?= e($errorMsg) ?></span>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <div class="bg-white/5 backdrop-blur border border-white/10 rounded-2xl p-8 shadow-2xl">
            <form method="POST" action="/login" class="space-y-5">
                <?= csrf_field() ?>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">E-mail</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="w-4 h-4 text-gray-500"></i>
                        </div>
                        <input 
                            type="email" id="email" name="email"
                            required autofocus
                            placeholder="seu@email.com"
                            value="<?= e($_POST['email'] ?? '') ?>"
                            class="w-full pl-10 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors text-sm"
                        >
                    </div>
                </div>

                <div>
                    <label for="senha" class="block text-sm font-medium text-gray-300 mb-2">Senha</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-4 h-4 text-gray-500"></i>
                        </div>
                        <input 
                            type="password" id="senha" name="senha"
                            required
                            placeholder="••••••••"
                            class="w-full pl-10 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors text-sm"
                        >
                    </div>
                </div>

                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-500 hover:to-primary-600 text-white font-semibold py-3 rounded-xl transition-all duration-200 shadow-lg shadow-primary-900/30 flex items-center justify-center gap-2"
                >
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    Entrar no Sistema
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-600 mt-6">
            Capitania PDV &copy; <?= date('Y') ?> — Todos os direitos reservados
        </p>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
