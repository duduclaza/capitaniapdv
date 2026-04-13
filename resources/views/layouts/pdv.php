<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDV - Capitania</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            400: '#e879f9', 500: '#d946ef', 600: '#c026d3', 700: '#a21caf',
                        },
                        dark: { 800: '#1e1b2e', 900: '#13111e', 950: '#0b0914' }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: #13111e; }
        ::-webkit-scrollbar-thumb { background: #4a044e; border-radius: 2px; }
        #toast-container { position: fixed; bottom: 1rem; right: 1rem; z-index: 100; pointer-events: none; }
        .toast {
            pointer-events: auto;
            background: rgba(30, 27, 46, 0.96);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(192, 38, 211, 0.3);
            border-left: 4px solid #c026d3;
            color: white;
            padding: 0.8rem 1rem;
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.75rem;
            min-width: 240px;
            animation: toastIn 0.25s ease forwards, toastOut 0.4s ease 2.6s forwards;
        }
        @keyframes toastIn { from { opacity:0; transform:translateY(100%); } to { opacity:1; transform:translateY(0); } }
        @keyframes toastOut { to { opacity:0; transform:translateX(100%); } }
    </style>
</head>
<body class="bg-dark-950 text-gray-100 h-screen overflow-hidden flex flex-col">
    <?= $content ?>
    <div id="toast-container"></div>
    <script>
        lucide.createIcons();

        window.toast = function(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const item = document.createElement('div');
            item.className = `toast ${type}`;

            const icon = type === 'success' ? 'check-circle' : (type === 'error' ? 'x-circle' : 'info');
            const color = type === 'success' ? 'text-emerald-400' : (type === 'error' ? 'text-red-400' : 'text-blue-400');

            const iconEl = document.createElement('i');
            iconEl.setAttribute('data-lucide', icon);
            iconEl.className = `w-5 h-5 ${color} flex-shrink-0`;

            const textWrap = document.createElement('div');
            textWrap.className = 'flex-1';

            const text = document.createElement('p');
            text.className = 'text-sm font-medium';
            text.textContent = message;

            textWrap.appendChild(text);
            item.appendChild(iconEl);
            item.appendChild(textWrap);

            container.appendChild(item);
            lucide.createIcons();
            setTimeout(() => item.remove(), 3200);
        };
    </script>
</body>
</html>
