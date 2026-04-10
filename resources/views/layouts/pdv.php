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
    </style>
</head>
<body class="bg-dark-950 text-gray-100 h-screen overflow-hidden flex flex-col">
    <?= $content ?>
    <script>lucide.createIcons();</script>
</body>
</html>
