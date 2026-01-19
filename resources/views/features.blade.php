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
                class="absolute top-[-15%] right-[-5%] w-[700px] h-[700px] bg-indigo-500/10 dark:bg-indigo-500/5 rounded-full blur-[120px]">
            </div>
            <div
                class="absolute bottom-[-10%] left-[-10%] w-[600px] h-[600px] bg-purple-500/10 dark:bg-purple-500/5 rounded-full blur-[120px]">
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
                    <a href="/documentation"
                        class="text-sm font-bold text-indigo-600 hover:text-indigo-500 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                        Dokumentasi
                    </a>
                    <a href="{{ route('login') }}"
                        class="text-sm font-bold text-zinc-500 hover:text-zinc-900 dark:hover:text-white transition-colors">
                        Kembali ke Login
                    </a>
                </div>
            </div>
        </nav>

        <button x-show="showTopButton" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-20 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="translate-y-20 opacity-0"
            @click="$el.closest('.overflow-y-auto').scrollTo({top: 0, behavior: 'smooth'})"
            class="fixed bottom-8 right-8 z-[60] w-14 h-14 bg-indigo-600 text-white rounded-2xl shadow-2xl shadow-indigo-600/40 flex items-center justify-center hover:bg-indigo-700 hover:-translate-y-1 active:scale-95 transition-all outline-none border-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"></path>
            </svg>
        </button>

        <header class="relative z-20 pt-44 pb-20 px-8 max-w-5xl mx-auto text-center select-none">
            <h2
                class="inline-block px-4 py-1.5 mb-6 text-xs font-black tracking-[0.2em] uppercase bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-full border border-indigo-100 dark:border-indigo-500/20">
                Product Features
            </h2>
            <h1
                class="text-5xl md:text-7xl font-black tracking-tight text-zinc-900 dark:text-white mb-8 leading-[1.1] uppercase">
                Satu Sistem. <br /> <span class="text-zinc-400 dark:text-zinc-600">Kendali Penuh.</span>
            </h1>
            <p
                class="text-lg md:text-xl text-zinc-500 dark:text-zinc-400 max-w-2xl mx-auto leading-relaxed font-medium">
                Panduan komprehensif mengenai kemampuan teknis dan operasional Sistem Agenda untuk efisiensi tim Anda.
            </p>
        </header>

        <main class="relative z-20 max-w-7xl mx-auto px-8 pb-10">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">

                <div
                    class="md:col-span-12 lg:col-span-8 p-10 bg-zinc-50 dark:bg-[#121214] border border-zinc-200 dark:border-zinc-800 rounded-[3rem] group cursor-pointer hover:border-indigo-500/30 transition-all hover:shadow-2xl hover:shadow-indigo-500/5 hover:-translate-y-1">
                    <div class="flex flex-col md:flex-row gap-12 items-start">
                        <div class="flex-1">
                            <span class="text-indigo-600 font-black text-sm uppercase">Modul 01</span>
                            <h3 class="text-3xl font-black dark:text-white mt-2 mb-6 uppercase tracking-tighter">
                                Monitoring & Visualisasi</h3>
                            <p class="text-zinc-500 dark:text-zinc-400 leading-relaxed mb-8 font-bold">
                                Dashboard interaktif yang menyajikan data beban kerja secara visual untuk pengambilan
                                keputusan cepat oleh manajer.
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-8">
                                <div
                                    class="flex items-center gap-3 text-sm font-black dark:text-zinc-300 uppercase tracking-tighter">
                                    <div class="w-2 h-2 rounded-full bg-indigo-500"></div> Kalender Interaktif Grid
                                </div>
                                <div
                                    class="flex items-center gap-3 text-sm font-black dark:text-zinc-300 uppercase tracking-tighter">
                                    <div class="w-2 h-2 rounded-full bg-indigo-500"></div> Smart Persistence Agenda
                                </div>
                                <div
                                    class="flex items-center gap-3 text-sm font-black dark:text-zinc-300 uppercase tracking-tighter">
                                    <div class="w-2 h-2 rounded-full bg-indigo-500"></div> Multi-User Filter
                                </div>
                                <div
                                    class="flex items-center gap-3 text-sm font-black dark:text-zinc-300 uppercase tracking-tighter">
                                    <div class="w-2 h-2 rounded-full bg-indigo-500"></div> Detail Modal & Search
                                </div>
                            </div>
                        </div>
                        <div
                            class="w-full md:w-64 aspect-square bg-white dark:bg-zinc-800 rounded-3xl border border-zinc-200 dark:border-zinc-700 p-4 shadow-sm transition-transform group-hover:scale-105">
                            <div class="grid grid-cols-7 gap-1 mb-4">
                                @for ($i = 0; $i < 21; $i++)
                                    <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-700 rounded-sm"></div>
                                @endfor
                            </div>
                            <div
                                class="h-10 w-full bg-indigo-500/20 border border-indigo-500/40 rounded-lg animate-pulse">
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="md:col-span-12 lg:col-span-4 p-10 bg-white dark:bg-[#121214] border border-zinc-200 dark:border-zinc-800 rounded-[3rem] flex flex-col justify-between group cursor-pointer hover:border-purple-500/30 transition-all hover:shadow-2xl hover:shadow-purple-500/5 hover:-translate-y-1">
                    <div>
                        <span class="text-purple-600 font-black text-sm uppercase">Modul 02</span>
                        <h3 class="text-2xl font-black dark:text-white mt-2 mb-4 uppercase tracking-tighter">Workflow
                            Micro-Step</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed font-bold">
                            Memastikan setiap tugas memiliki langkah terukur dengan estimasi durasi spesifik untuk audit
                            keterlambatan otomatis.
                        </p>
                    </div>
                    <div class="mt-8 space-y-4">
                        <div
                            class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl flex items-center justify-between group-hover:bg-purple-50 dark:group-hover:bg-purple-500/10 transition-colors">
                            <span class="text-xs font-black dark:text-white uppercase tracking-widest">Estimasi</span>
                            <span
                                class="text-xs bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400 px-2 py-1 rounded font-black">1j
                                30m</span>
                        </div>
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl flex items-center justify-between">
                            <span class="text-xs font-black dark:text-white uppercase tracking-widest">Audit</span>
                            <span class="text-xs text-red-500 font-black uppercase tracking-tighter">Overdue!</span>
                        </div>
                    </div>
                </div>

                <div
                    class="md:col-span-12 lg:col-span-5 p-10 bg-zinc-900 text-white rounded-[3rem] shadow-2xl relative overflow-hidden group cursor-pointer hover:-translate-y-1 transition-all">
                    <div class="relative z-10">
                        <span class="text-indigo-400 font-black text-sm uppercase">Modul 03</span>
                        <h3 class="text-3xl font-black mt-2 mb-6 leading-tight uppercase tracking-tighter">Reporting
                            Engine Pro</h3>
                        <p class="text-zinc-400 text-sm leading-relaxed mb-8 font-bold">
                            Hasilkan laporan formal PDF atau data Excel untuk evaluasi performa bulanan dengan sekali
                            klik.
                        </p>
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-white/10 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/20 group-hover:bg-indigo-500 transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 16l4-4h-3V4h-2v8H8l4 4zm9 4H3v-2h18v2z" />
                                </svg>
                            </div>
                            <span
                                class="text-xs font-black uppercase tracking-widest group-hover:text-indigo-300 transition-colors">Download
                                Rekapitulasi</span>
                        </div>
                    </div>
                    <div
                        class="absolute -right-8 -bottom-8 opacity-10 group-hover:scale-125 group-hover:opacity-20 transition-all duration-700">
                        <svg class="w-48 h-48" fill="white" viewBox="0 0 24 24">
                            <path
                                d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z" />
                        </svg>
                    </div>
                </div>

                <div
                    class="md:col-span-12 lg:col-span-7 p-10 bg-zinc-50 dark:bg-[#121214] border border-zinc-200 dark:border-zinc-800 rounded-[3rem] group cursor-pointer hover:border-zinc-400 dark:hover:border-zinc-600 transition-all hover:-translate-y-1">
                    <div class="flex flex-col md:flex-row justify-between gap-10">
                        <div class="space-y-6">
                            <h4 class="text-2xl font-black dark:text-white uppercase tracking-tighter">Hierarki &
                                Keamanan</h4>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 max-w-xs font-bold">
                                Pengaturan peran Parent-Child untuk memisahkan wewenang antara Admin/Manager dan Staff.
                            </p>
                            <div class="flex gap-3">
                                <span
                                    class="px-3 py-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-[10px] font-black uppercase">Staff</span>
                                <span
                                    class="px-3 py-1 bg-indigo-600 text-white rounded-lg text-[10px] font-black uppercase shadow-lg shadow-indigo-500/20 group-hover:scale-110 transition-transform">Manager</span>
                            </div>
                        </div>
                        <div class="flex-1 space-y-3">
                            <div
                                class="p-4 bg-white dark:bg-zinc-800/30 rounded-2xl border border-zinc-200 dark:border-zinc-700 flex items-center gap-4 group-hover:border-indigo-500/50 transition-colors">
                                <div
                                    class="w-8 h-8 bg-green-500/10 text-green-500 rounded-lg flex items-center justify-center font-black">
                                    ✔</div>
                                <span class="text-xs font-black dark:text-zinc-300 uppercase tracking-widest">Guest
                                    Access Support</span>
                            </div>
                            <div
                                class="p-4 bg-white dark:bg-zinc-800/30 rounded-2xl border border-zinc-200 dark:border-zinc-700 flex items-center gap-4">
                                <div
                                    class="w-8 h-8 bg-indigo-500/10 text-indigo-500 rounded-lg flex items-center justify-center font-black">
                                    ☾</div>
                                <span class="text-xs font-black dark:text-zinc-300 uppercase tracking-widest">Native
                                    Dark Mode</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="md:col-span-12 bg-white dark:bg-[#09090b] border border-zinc-200 dark:border-zinc-800 rounded-[3rem] p-12 group hover:border-indigo-500/20 transition-all">
                    <div class="flex flex-col md:flex-row items-center gap-12">
                        <div class="flex-1">
                            <h3 class="text-2xl font-black dark:text-white mb-4 uppercase tracking-tighter">Spesifikasi
                                Teknis</h3>
                            <p class="text-zinc-500 dark:text-zinc-400 font-bold uppercase text-sm tracking-tight">
                                Dibangun dengan teknologi modern untuk performa maksimal pada infrastruktur shared
                                hosting maupun cloud.
                            </p>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-8 w-full md:w-auto">
                            <div class="text-center group-hover:scale-110 transition-transform">
                                <div class="text-2xl font-black dark:text-white uppercase tracking-tighter">Real-time
                                </div>
                                <div class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] mt-1">
                                    Livewire Volt</div>
                            </div>
                            <div class="text-center group-hover:scale-110 transition-transform">
                                <div class="text-2xl font-black dark:text-white uppercase tracking-tighter">Hosting
                                </div>
                                <div class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] mt-1">
                                    Shared Ready</div>
                            </div>
                            <div class="text-center group-hover:scale-110 transition-transform">
                                <div class="text-2xl font-black dark:text-white uppercase tracking-tighter">Full</div>
                                <div class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] mt-1">
                                    Responsive</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer
            class="relative z-30 max-w-7xl mx-auto pb-20 px-8 border-t border-zinc-200 dark:border-zinc-800 pt-10 mt-20">
            <div
                class="flex flex-col md:flex-row justify-between items-center gap-8 text-[10px] font-black tracking-[0.4em] text-zinc-400 uppercase select-none">
                <div class="flex items-center gap-4 order-2 md:order-1">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    Sistem Agenda Marison © 2026
                </div>

                <div class="flex items-center gap-8 order-1 md:order-2">
                    <a href="/" class="hover:text-indigo-600 transition-colors">Index</a>
                    <a href="/features" class="text-indigo-600">Features</a>
                    <a href="/documentation" class="hover:text-indigo-600 transition-colors">Docs</a>
                    <a href="{{ route('login') }}" class="hover:text-indigo-600 transition-colors">Portal</a>
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

    /* Scrollbar Styling */
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
