<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMKN 9 Malang</title>

    <!-- FontAwesome 6 for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
      }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --bg-body: #f1f5f9;
            --text-primary: #1e293b;
        }
        .dark {
            --bg-body: #0f172a;
            --text-primary: #f8fafc;
        }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            margin: 0;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body.dark .text-gray-800,
        body.dark .text-gray-700,
        body.dark .text-gray-500 {
            color: #cbd5e1 !important;
        }

        body.dark .dark\\:text-white,
        body.dark .dark\\:text-white\\/90 {
            color: #f8fafc !important;
        }
    </style>
</head>
<body
    x-data="{ darkMode: false }"
    x-init="
         let stored = localStorage.getItem('darkMode');
         darkMode = stored ? JSON.parse(stored) : false;
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark': darkMode === true}"
>

    @yield('content')

</body>
</html>
