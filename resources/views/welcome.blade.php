<x-layouts.auth>
    <div
        class="fixed inset-0 w-screen h-screen overflow-y-auto bg-zinc-50 dark:bg-zinc-950 transition-colors duration-500 cursor-default selection:bg-indigo-100 dark:selection:bg-indigo-900/30">

        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div
                class="absolute top-[-10%] left-[-5%] w-[600px] h-[600px] bg-indigo-200/30 dark:bg-indigo-900/15 rounded-full blur-[120px] animate-blob">
            </div>
            <div
                class="absolute bottom-[10%] right-[-5%] w-[500px] h-[500px] bg-purple-200/30 dark:bg-purple-900/15 rounded-full blur-[120px] animate-blob animation-delay-2000">
            </div>
        </div>

        <nav
            class="sticky top-0 z-50 w-full bg-white/60 dark:bg-zinc-950/60 backdrop-blur-md border-b border-zinc-200/50 dark:border-white/5">
            <div class="max-w-7xl mx-auto px-6 sm:px-12 h-20 flex items-center justify-between">
                <div class="flex items-center gap-2 cursor-pointer group" onclick="window.location.href='/'">
                    <div
                        class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30 transition-transform group-hover:rotate-6">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-black tracking-tighter text-zinc-900 dark:text-white uppercase">
                        Agenda<span class="text-indigo-600">.</span>
                    </span>
                </div>

                <div class="flex items-center gap-6">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-full hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-600/20 hover:scale-105 active:scale-95">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-sm font-bold text-zinc-600 dark:text-zinc-400 hover:text-indigo-600 dark:hover:text-white transition-colors">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}"
                            class="px-6 py-2.5 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-bold rounded-full shadow-lg hover:scale-105 active:scale-95 transition-all">
                            Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <main class="relative z-10 w-full max-w-7xl mx-auto px-6 sm:px-12 min-h-[calc(100vh-80px)] flex items-center">
            <div class="grid lg:grid-cols-2 gap-16 items-center w-full py-16">

                <div class="space-y-8 animate-fade-in-up">
                    <div
                        class="inline-block px-4 py-1 rounded-full bg-indigo-100/50 dark:bg-indigo-500/10 border border-indigo-200/50 dark:border-indigo-400/20">
                        <span class="text-xs font-bold text-indigo-700 dark:text-indigo-400 uppercase tracking-widest">
                            ✨ Manajemen Waktu Modern
                        </span>
                    </div>

                    <h1
                        class="text-6xl md:text-8xl font-black text-zinc-900 dark:text-white leading-[0.85] tracking-tighter">
                        WAKTUMU <br />
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-500">
                            ADALAH ASET.
                        </span>
                    </h1>

                    <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-lg leading-relaxed font-medium">
                        Atur setiap detik produktivitasmu dengan sistem agenda berbasis Glassmorphism yang intuitif dan
                        transparan.
                    </p>

                    <div class="flex flex-wrap gap-4 pt-4">
                        <a href="{{ route('register') }}"
                            class="px-10 py-5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-2xl shadow-indigo-600/30 transition-all hover:-translate-y-1 active:scale-95">
                            Mulai Sekarang — Gratis
                        </a>
                        <a href="{{ url('/features') }}"
                            class="px-10 py-5 bg-white/40 dark:bg-white/5 backdrop-blur-xl border border-zinc-200 dark:border-white/10 text-zinc-900 dark:text-white font-bold rounded-2xl hover:bg-white/60 dark:hover:bg-white/10 transition-all">
                            Lihat Fitur
                        </a>
                    </div>
                </div>

                <div class="relative hidden lg:block animate-fade-in">
                    <div
                        class="p-8 bg-white/30 dark:bg-white/5 backdrop-blur-3xl rounded-[3rem] border border-white/50 dark:border-white/10 shadow-2xl animate-float cursor-default">
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-[2.5rem] p-8 shadow-inner border border-zinc-100 dark:border-zinc-800">
                            <div class="space-y-6">
                                <div
                                    class="flex justify-between items-center border-b pb-4 border-zinc-100 dark:border-zinc-800">
                                    <div class="h-6 w-32 bg-zinc-100 dark:bg-zinc-800 rounded-full"></div>
                                    <div class="flex gap-2 text-indigo-500 opacity-50">● ● ●</div>
                                </div>
                                <div
                                    class="p-5 bg-indigo-50 dark:bg-indigo-500/10 rounded-3xl border border-indigo-100 dark:border-indigo-500/20">
                                    <div class="h-4 w-1/2 bg-indigo-200 dark:bg-indigo-400/30 rounded-full mb-3"></div>
                                    <div class="h-3 w-3/4 bg-indigo-100 dark:bg-indigo-300/20 rounded-full"></div>
                                </div>
                                <div
                                    class="p-5 bg-zinc-50 dark:bg-zinc-800/50 rounded-3xl border border-zinc-100 dark:border-zinc-700">
                                    <div class="h-4 w-2/3 bg-zinc-200 dark:bg-zinc-700 rounded-full mb-3"></div>
                                    <div class="h-3 w-1/2 bg-zinc-100 dark:bg-zinc-600/50 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="absolute -top-6 -right-6 w-24 h-24 bg-white/80 dark:bg-zinc-800 backdrop-blur-xl rounded-2xl shadow-2xl flex items-center justify-center border border-white dark:border-zinc-700 rotate-12 transition-transform hover:rotate-0 cursor-pointer group">
                        <svg class="w-12 h-12 text-indigo-600 transition-transform group-hover:scale-110" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layouts.auth>

<style>
    /* Optimization: Menghapus force-reset yang merusak margin Tailwind
    */
    main {
        margin-left: auto !important;
        margin-right: auto !important;
    }

    /* Animations */
    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    @keyframes blob {
        0% {
            transform: scale(1) translate(0px, 0px);
        }

        50% {
            transform: scale(1.1) translate(20px, -20px);
        }

        100% {
            transform: scale(1) translate(0px, 0px);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .animate-float {
        animation: float 6s ease-in-out infinite;
    }

    .animate-blob {
        animation: blob 10s infinite alternate ease-in-out;
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.8s ease-out forwards;
    }

    .animate-fade-in {
        animation: fadeIn 1.2s ease-out forwards;
    }

    .animation-delay-2000 {
        animation-delay: 2s;
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: transparent;
    }

    ::-webkit-scrollbar-thumb {
        background: #e4e4e7;
        border-radius: 10px;
    }

    .dark ::-webkit-scrollbar-thumb {
        background: #27272a;
    }
</style>
