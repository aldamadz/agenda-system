<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'AgendaSystem' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxStyles
</head>

<body class="min-h-screen bg-slate-50 dark:bg-zinc-950 font-sans antialiased text-slate-900 dark:text-zinc-100">

    <nav
        class="sticky top-0 z-50 w-full border-b border-slate-200/50 dark:border-zinc-800/50 bg-slate-50/80 dark:bg-zinc-950/80 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="/" class="flex items-center space-x-2">
                    <x-app-logo class="size-8" />
                    <h1 class="text-xl font-black tracking-tighter uppercase">
                        Agenda<span class="text-indigo-600">System</span>
                    </h1>
                </a>

                <div class="flex items-center gap-4">
                    <flux:button variant="ghost" :href="route('login')" wire:navigate>Login</flux:button>
                    <flux:button variant="primary" :href="route('register')" wire:navigate>Daftar</flux:button>
                </div>
            </div>
        </div>
    </nav>

    <main class="relative">
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-3xl h-64 bg-indigo-500/10 blur-[120px] rounded-full -z-10">
        </div>

        {{ $slot }}
    </main>

    <footer class="py-12 border-t border-slate-200 dark:border-zinc-800 mt-20">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <flux:text size="sm" class="dark:text-zinc-500">
                &copy; {{ date('Y') }} AgendaSystem. Seluruh hak cipta dilindungi.
            </flux:text>
        </div>
    </footer>

    @fluxScripts
</body>

</html>
