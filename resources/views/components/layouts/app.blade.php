<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
        }

        * {
            transition: background-color 0.4s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.4s ease;
        }
    </style>
</head>

<body class="min-h-screen bg-[#f8fafc] dark:bg-[#020617] antialiased relative overflow-x-hidden glass-noise">

    {{-- Background Ambient Blobs (Ultra Modern) --}}
    <div class="fixed inset-0 -z-10 pointer-events-none">
        {{-- Top Indigo Glow --}}
        <div
            class="absolute -top-[10%] -left-[5%] w-[60%] h-[50%] rounded-full bg-indigo-500/15 dark:bg-indigo-600/20 blur-[120px] animate-pulse">
        </div>

        {{-- Center Fuchsia Accent --}}
        <div
            class="absolute top-[20%] right-[10%] w-[40%] h-[40%] rounded-full bg-fuchsia-500/10 dark:bg-fuchsia-600/10 blur-[100px]">
        </div>

        {{-- Bottom Emerald Glow --}}
        <div
            class="absolute -bottom-[5%] left-[20%] w-[50%] h-[40%] rounded-full bg-emerald-400/10 dark:bg-emerald-500/10 blur-[110px]">
        </div>
    </div>

    <div class="flex min-h-screen w-full relative">
        <x-layouts.app.sidebar />

        <div class="flex-1 flex flex-col min-w-0">
            {{-- Floating Mobile Header --}}
            <flux:header class="lg:hidden m-4 glass-card rounded-2xl !border-white/20">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
                <flux:spacer />
                <flux:profile :initials="auth()->user()->initials()" />
            </flux:header>

            <flux:main class="p-4 lg:p-8">
                {{-- Content Transition --}}
                <div class="animate-in fade-in slide-in-from-bottom-4 duration-1000">
                    {{ $slot }}
                </div>
            </flux:main>
        </div>
    </div>

    @fluxScripts
    @stack('scripts')

    <script>
        function getSwalConfig(payload) {
            // Pastikan payload ada, jika tidak gunakan objek kosong
            const data = payload || {};
            const isDark = document.documentElement.classList.contains('dark');

            return {
                icon: data.icon || 'success',
                title: data.title || '',
                text: data.text || '',
                timer: 3000,
                toast: true, // Karena ini TRUE...
                position: 'top-end',
                showConfirmButton: false,
                timerProgressBar: true,
                background: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',

                // PERBAIKAN: Backdrop harus FALSE atau dihapus jika menggunakan Toast
                backdrop: false,

                color: isDark ? '#f1f5f9' : '#1e293b',
                customClass: {
                    popup: 'glass-card !rounded-2xl !border-white/10 shadow-2xl'
                }
            };
        }

        window.addEventListener('swal', event => {
            // Pengamanan: Ambil data baik dari event.detail[0] (array) atau event.detail (objek)
            const payload = Array.isArray(event.detail) ? event.detail[0] : event.detail;

            if (payload) {
                Swal.fire(getSwalConfig(payload));
            }
        });
    </script>
</body>

</html>
