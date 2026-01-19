<x-layouts.auth>
    <div x-data="{
        lastPos: 0,
        showNav: true,
        showTopButton: false
    }"
        @scroll="
            let currentPos = $el.scrollTop;
            showNav = currentPos < lastPos || currentPos < 50;
            lastPos = currentPos;
            showTopButton = currentPos > 400;
         "
        class="fixed inset-0 w-screen h-screen overflow-y-auto bg-white dark:bg-[#09090b] transition-colors duration-500 cursor-default selection:bg-indigo-100 dark:selection:bg-indigo-500/30 scroll-smooth">

        <div class="fixed inset-0 overflow-hidden pointer-events-none select-none">
            <div
                class="absolute top-[-15%] right-[-5%] w-[700px] h-[700px] bg-indigo-500/10 dark:bg-indigo-500/5 rounded-full blur-[120px] animate-blob">
            </div>
            <div
                class="absolute bottom-[-10%] left-[-10%] w-[600px] h-[600px] bg-purple-500/10 dark:bg-purple-500/5 rounded-full blur-[120px] animate-blob animation-delay-2000">
            </div>
        </div>

        <nav x-show="showNav" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-y-full" x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-y-0"
            x-transition:leave-end="-translate-y-full"
            class="fixed top-0 z-50 w-full bg-white/60 dark:bg-[#09090b]/60 backdrop-blur-md border-b border-zinc-200/50 dark:border-white/5">
            <div class="max-w-7xl mx-auto px-8 h-20 flex justify-between items-center">
                <div class="flex items-center gap-2 group cursor-pointer" onclick="window.location.href='/'">
                    <div
                        class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center transition-transform group-hover:rotate-6 shadow-lg shadow-indigo-500/20 text-white font-bold text-sm">
                        SA
                    </div>
                    <span class="text-xl font-black tracking-tighter text-zinc-900 dark:text-white uppercase">
                        Sistem <span class="text-indigo-600">Agenda</span>
                    </span>
                </div>

                <div class="flex items-center gap-6">
                    @auth
                        <a href="/dashboard"
                            class="px-6 py-2 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-[10px] font-black rounded-full uppercase tracking-widest hover:scale-105 transition-all shadow-lg">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-sm font-black text-zinc-500 hover:text-zinc-900 dark:hover:text-white transition-colors uppercase tracking-widest">
                            Login
                        </a>
                        <a href="{{ route('register') }}"
                            class="px-6 py-2 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-[10px] font-black rounded-full uppercase tracking-widest hover:scale-105 transition-all shadow-lg">
                            Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <main class="relative z-10 w-full max-w-7xl mx-auto px-8 min-h-screen flex items-center">
            <div class="grid lg:grid-cols-2 gap-16 items-center w-full pt-20 pb-20">

                <div class="space-y-10 animate-fade-in-up">
                    <div
                        class="inline-block px-4 py-1.5 text-[10px] font-black tracking-[0.2em] uppercase bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-full border border-indigo-100 dark:border-indigo-500/20">
                        ✨ Sistem Manajemen Internal
                    </div>

                    <h1
                        class="text-6xl md:text-8xl font-black text-zinc-900 dark:text-white leading-[0.9] tracking-tighter uppercase">
                        KONTROL <br />
                        <span class="text-zinc-400 dark:text-zinc-600">AGENDA ANDA.</span>
                    </h1>

                    <p
                        class="text-lg md:text-xl text-zinc-500 dark:text-zinc-400 max-w-lg leading-relaxed font-bold uppercase tracking-tight">
                        Platform koordinasi agenda untuk meningkatkan transparansi kerja antar departemen secara
                        real-time.
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('register') }}"
                            class="px-10 py-5 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-2xl shadow-2xl shadow-indigo-600/30 transition-all hover:-translate-y-1 active:scale-95 text-xs uppercase tracking-[0.2em]">
                            Mulai Sekarang
                        </a>
                        <a href="{{ url('/features') }}"
                            class="px-10 py-5 bg-white dark:bg-zinc-900 border-2 border-zinc-100 dark:border-zinc-800 text-zinc-900 dark:text-white font-black rounded-2xl hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all text-xs uppercase tracking-[0.2em]">
                            Lihat Fitur
                        </a>
                    </div>
                </div>

                <div class="relative hidden lg:block animate-fade-in">
                    <div
                        class="p-8 bg-zinc-50/50 dark:bg-zinc-900/30 backdrop-blur-3xl rounded-[4rem] border border-zinc-200/50 dark:border-white/5 shadow-2xl animate-float">
                        <div
                            class="bg-white dark:bg-[#09090b] rounded-[3rem] p-8 shadow-2xl border border-zinc-100 dark:border-zinc-800">
                            <div class="space-y-6">
                                <div
                                    class="flex items-center justify-between border-b pb-4 border-zinc-50 dark:border-zinc-800/50">
                                    <div class="h-4 w-32 bg-zinc-100 dark:bg-zinc-800 rounded-full"></div>
                                    <div class="w-8 h-8 rounded-lg bg-indigo-600"></div>
                                </div>
                                <div class="space-y-3">
                                    <div
                                        class="p-4 bg-indigo-50 dark:bg-indigo-500/10 rounded-2xl flex items-center gap-4">
                                        <div class="w-2 h-2 bg-indigo-600 rounded-full"></div>
                                        <div class="h-3 w-1/2 bg-indigo-200 dark:bg-indigo-400/30 rounded-full"></div>
                                    </div>
                                    <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl flex items-center gap-4">
                                        <div class="w-2 h-2 bg-zinc-200 dark:bg-zinc-700 rounded-full"></div>
                                        <div class="h-3 w-2/3 bg-zinc-100 dark:bg-zinc-700 rounded-full"></div>
                                    </div>
                                </div>
                                <div class="pt-4">
                                    <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                        <div class="h-full w-2/3 bg-indigo-600"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="relative z-30 max-w-7xl mx-auto pb-20 px-8 border-t border-zinc-200 dark:border-zinc-800 pt-10">
            <div
                class="flex flex-col md:flex-row justify-between items-center gap-8 text-[10px] font-black tracking-[0.4em] text-zinc-400 uppercase">
                <div class="flex items-center gap-4">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    Sistem Agenda Marison © 2026
                </div>
                <div class="flex gap-8">
                    <a href="/features" class="hover:text-indigo-600 transition-colors">Features</a>
                    <a href="/documentation" class="hover:text-indigo-600 transition-colors">Documentation</a>
                    <a href="/login" class="hover:text-indigo-600 transition-colors">Portal Login</a>
                </div>
            </div>
        </footer>
    </div>
</x-layouts.auth>

<style>
    [x-cloak] {
        display: none !important;
    }

    .scroll-smooth {
        scroll-behavior: smooth;
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
            transform: scale(1.1) translate(30px, -30px);
        }

        100% {
            transform: scale(1) translate(0px, 0px);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(40px);
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
        animation: blob 12s infinite alternate ease-in-out;
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .animate-fade-in {
        animation: fadeIn 1.5s ease-out forwards;
    }

    .animation-delay-2000 {
        animation-delay: 2s;
    }

    /* Custom Scrollbar */
    .overflow-y-auto::-webkit-scrollbar {
        width: 8px;
    }

    .overflow-y-auto::-webkit-scrollbar-track {
        background: transparent;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #e4e4e7;
        border-radius: 20px;
    }

    .dark .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #27272a;
    }
</style>
