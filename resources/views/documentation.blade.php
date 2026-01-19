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
                    @endauth
                </div>
            </div>
        </nav>

        <button x-show="showTopButton" x-cloak
            @click="$el.closest('.overflow-y-auto').scrollTo({top: 0, behavior: 'smooth'})"
            class="fixed bottom-8 right-8 z-[60] w-14 h-14 bg-indigo-600 text-white rounded-2xl shadow-2xl shadow-indigo-600/40 flex items-center justify-center hover:bg-indigo-700 hover:-translate-y-1 active:scale-95 transition-all outline-none border-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"></path>
            </svg>
        </button>

        <header class="relative z-20 pt-44 pb-20 px-8 max-w-5xl mx-auto text-center select-none">
            <h2
                class="inline-block px-4 py-1.5 mb-6 text-xs font-black tracking-[0.2em] uppercase bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-full border border-indigo-100 dark:border-indigo-500/20">
                Dokumentasi Alur
            </h2>
            <h1
                class="text-5xl md:text-7xl font-black tracking-tight text-zinc-900 dark:text-white mb-8 leading-[1.1] uppercase">
                Prosedur & <br /> <span class="text-zinc-400 dark:text-zinc-600">Operasional.</span>
            </h1>
            <p
                class="text-lg md:text-xl text-zinc-500 dark:text-zinc-400 max-w-2xl mx-auto leading-relaxed font-medium">
                Sistem Manajemen Agenda Marison mengoptimalkan efisiensi kerja melalui pemisahan fungsi input data dan
                visualisasi monitoring agenda.
            </p>
        </header>

        <main class="relative z-20 max-w-7xl mx-auto px-8 pb-40">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">

                <div
                    class="md:col-span-12 p-10 bg-zinc-50 dark:bg-[#121214] border border-zinc-200 dark:border-zinc-800 rounded-[3rem] group">
                    <div class="flex flex-col lg:flex-row gap-12">
                        <div class="lg:w-1/3">
                            <span
                                class="w-16 h-16 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-black shadow-lg shadow-indigo-500/20 text-2xl mb-6">01</span>
                            <h3 class="text-3xl font-black dark:text-white mb-4 uppercase tracking-tighter">Entry Agenda
                                & Pelaporan</h3>
                            <p
                                class="text-zinc-500 dark:text-zinc-400 font-bold text-sm leading-relaxed uppercase tracking-tight">
                                Setiap kegiatan wajib direkam melalui modul pembuatan agenda pusat untuk menjaga
                                integritas data harian.</p>
                        </div>
                        <div class="lg:w-2/3 grid md:grid-cols-2 gap-6">
                            <div
                                class="p-8 bg-white dark:bg-zinc-800/50 rounded-[2.5rem] border border-zinc-200 dark:border-zinc-700 hover:border-indigo-500 transition-all">
                                <div
                                    class="w-10 h-10 bg-indigo-500/10 text-indigo-500 rounded-xl flex items-center justify-center mb-4 font-black text-xs">
                                    ADD</div>
                                <h4 class="font-black text-zinc-900 dark:text-white uppercase mb-3 tracking-tighter">
                                    Halaman Create</h4>
                                <ul
                                    class="space-y-3 text-[11px] font-black text-zinc-600 dark:text-zinc-400 uppercase tracking-tight">
                                    <li class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-indigo-600 rounded-full"></div> Akses via rute
                                        /agenda/create
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-indigo-600 rounded-full"></div> Input judul, jam, dan
                                        deskripsi
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-indigo-600 rounded-full"></div> Kategorisasi tugas
                                        otomatis
                                    </li>
                                </ul>
                            </div>
                            <div
                                class="p-8 bg-white dark:bg-zinc-800/50 rounded-[2.5rem] border border-zinc-200 dark:border-zinc-700 hover:border-indigo-500 transition-all">
                                <div
                                    class="w-10 h-10 bg-indigo-500/10 text-indigo-500 rounded-xl flex items-center justify-center mb-4 font-black text-xs">
                                    LST</div>
                                <h4 class="font-black text-zinc-900 dark:text-white uppercase mb-3 tracking-tighter">
                                    Monitoring Progres</h4>
                                <ul
                                    class="space-y-3 text-[11px] font-black text-zinc-600 dark:text-zinc-400 uppercase tracking-tight">
                                    <li class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-indigo-600 rounded-full"></div> Update status To-Do
                                        ke Done
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-indigo-600 rounded-full"></div> Dashboard ringkasan
                                        harian
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-indigo-600 rounded-full"></div> Notifikasi deadline
                                        tugas
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="md:col-span-12 lg:col-span-8 p-12 bg-purple-600 rounded-[4rem] text-white shadow-2xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-[100px] -mr-32 -mt-32">
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-10">
                            <span
                                class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center font-black text-2xl shadow-lg">02</span>
                            <h3 class="text-4xl font-black uppercase tracking-tighter leading-none">
                                Visualisasi<br>Kalender</h3>
                        </div>
                        <div class="grid md:grid-cols-2 gap-12">
                            <div>
                                <h4 class="font-black text-purple-200 uppercase text-[10px] tracking-[0.2em] mb-4">
                                    Interaktivitas View</h4>
                                <p class="text-sm font-bold leading-relaxed opacity-90 mb-6 uppercase tracking-tight">
                                    Fokus utama kalender adalah penyajian data. Pengguna dapat meninjau detail agenda
                                    secara mendalam tanpa risiko perubahan data yang tidak disengaja.
                                </p>
                                <div
                                    class="inline-flex items-center px-4 py-2 bg-white text-purple-600 rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl">
                                    Read-Only View</div>
                            </div>
                            <div>
                                <h4 class="font-black text-purple-200 uppercase text-[10px] tracking-[0.2em] mb-4">
                                    Document Export</h4>
                                <p class="text-sm font-bold leading-relaxed opacity-90 mb-6 uppercase tracking-tight">
                                    Fitur ekspor PDF memungkinkan pembuatan laporan agenda harian atau mingguan secara
                                    instan langsung dari tampilan kalender.
                                </p>
                                <div
                                    class="inline-flex items-center px-4 py-2 bg-white/20 border border-white/30 rounded-xl font-black text-[10px] uppercase tracking-widest text-white backdrop-blur-sm">
                                    PDF Engine Ready</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="md:col-span-12 lg:col-span-4 p-10 bg-zinc-900 text-white rounded-[4rem] flex flex-col justify-between group hover:border-indigo-500 border border-transparent transition-all">
                    <div>
                        <div class="flex items-center gap-4 mb-8">
                            <span
                                class="w-12 h-12 rounded-xl bg-white text-zinc-900 flex items-center justify-center font-black text-xl">03</span>
                            <h3 class="text-2xl font-black uppercase tracking-tighter">Admin Core</h3>
                        </div>
                        <div class="space-y-4">
                            <div
                                class="p-6 bg-white/5 border border-white/10 rounded-3xl group-hover:bg-indigo-500/10 transition-colors">
                                <h5 class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">
                                    Hierarchy Control</h5>
                                <p class="text-[11px] font-bold opacity-70 uppercase leading-relaxed">Kelola relasi
                                    Parent-Child untuk otoritas verifikasi agenda antar jabatan.</p>
                            </div>
                            <div
                                class="p-6 bg-white/5 border border-white/10 rounded-3xl group-hover:bg-indigo-500/10 transition-colors">
                                <h5 class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">Audit
                                    Logs</h5>
                                <p class="text-[11px] font-bold opacity-70 uppercase leading-relaxed">Pantau histori
                                    pembuatan agenda dan aktivitas login pengguna secara real-time.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-12 grid md:grid-cols-2 gap-8">
                    <div
                        class="p-10 bg-white dark:bg-[#121214] border border-zinc-200 dark:border-zinc-800 rounded-[3rem] group hover:border-emerald-500 transition-all">
                        <div class="flex items-center gap-4 mb-6">
                            <div
                                class="w-10 h-10 bg-emerald-500/10 text-emerald-500 rounded-xl flex items-center justify-center font-black text-xs">
                                2FA</div>
                            <h4 class="font-black text-zinc-900 dark:text-white uppercase tracking-tighter text-xl">
                                Keamanan Lapis Dua</h4>
                        </div>
                        <p
                            class="text-sm font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-tight leading-relaxed mb-6">
                            Proteksi akun menggunakan Two-Factor Authentication (Laravel Fortify) untuk memastikan data
                            agenda tetap aman dan privat.
                        </p>
                        <div
                            class="px-4 py-2 bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 rounded-xl text-[10px] font-black uppercase tracking-widest inline-block">
                            Security Level: High</div>
                    </div>

                    <div
                        class="p-10 bg-white dark:bg-[#121214] border border-zinc-200 dark:border-zinc-800 rounded-[3rem] group hover:border-indigo-500 transition-all">
                        <div class="flex items-center gap-4 mb-6">
                            <div
                                class="w-10 h-10 bg-indigo-500/10 text-indigo-500 rounded-xl flex items-center justify-center font-black text-xs">
                                UI</div>
                            <h4 class="font-black text-zinc-900 dark:text-white uppercase tracking-tighter text-xl">
                                Opsi Tampilan</h4>
                        </div>
                        <p
                            class="text-sm font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-tight leading-relaxed mb-6">
                            Personalisasi antarmuka melalui pengaturan Appearance dengan dukungan penuh untuk Dark Mode
                            dan Light Mode.
                        </p>
                        <div class="flex gap-2">
                            <div class="w-4 h-4 rounded-full bg-zinc-200"></div>
                            <div class="w-4 h-4 rounded-full bg-zinc-900"></div>
                            <div class="w-4 h-4 rounded-full bg-indigo-600"></div>
                        </div>
                    </div>
                </div>

                <div
                    class="md:col-span-12 p-12 bg-zinc-50 dark:bg-[#09090b] border border-zinc-200 dark:border-zinc-800 rounded-[4rem] text-center">
                    <h4 class="text-sm font-black text-zinc-400 uppercase tracking-[0.4em] mb-8">System Infrastructure
                    </h4>
                    <div class="flex flex-wrap justify-center gap-12 grayscale opacity-50">
                        <span
                            class="text-xs font-black uppercase tracking-widest text-zinc-900 dark:text-white">Laravel
                            11</span>
                        <span class="text-xs font-black uppercase tracking-widest text-zinc-900 dark:text-white">DomPDF
                            Engine</span>
                        <span class="text-xs font-black uppercase tracking-widest text-zinc-900 dark:text-white">Volt
                            Livewire</span>
                        <span
                            class="text-xs font-black uppercase tracking-widest text-zinc-900 dark:text-white">Tailwind
                            CSS</span>
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
