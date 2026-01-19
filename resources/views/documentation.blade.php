<x-layouts.auth>
    <style>
        body,
        html {
            overflow: hidden !important;
            height: 100%;
        }

        main,
        .container {
            max-width: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .content-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .content-scroll::-webkit-scrollbar-thumb {
            background: #4f46e5;
            border-radius: 10px;
        }

        .nav-active {
            background: #4f46e5 !important;
            color: white !important;
            font-weight: 800;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        }

        .nav-active svg {
            color: white !important;
        }

        .section-divider {
            border-left: 4px solid #4f46e5;
            padding-left: 1.5rem;
        }
    </style>

    <div class="absolute inset-0 w-screen h-screen bg-white dark:bg-[#09090b] z-[9999] flex overflow-hidden font-sans">

        <aside
            class="w-72 h-full border-r border-zinc-200 dark:border-zinc-800 flex flex-col bg-zinc-50 dark:bg-[#0d0d0f] shrink-0">
            <div class="p-8">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <span
                            class="block font-black text-lg tracking-tighter dark:text-white uppercase leading-none">SATU
                            AGENDA</span>
                        <span class="text-[9px] font-bold text-indigo-600 tracking-widest uppercase">Marison
                            Group</span>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-4 space-y-2 mt-4 text-zinc-500">
                <p class="text-[10px] font-black uppercase tracking-widest px-4 mb-2">Struktur Modul</p>
                <a href="#staff-area"
                    class="nav-item nav-active flex items-center gap-3 p-3.5 rounded-2xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                        </path>
                    </svg>
                    <span class="font-bold text-[11px] uppercase tracking-wider">Modul Operasional</span>
                </a>
                <a href="#manager-area" class="nav-item flex items-center gap-3 p-3.5 rounded-2xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                        </path>
                    </svg>
                    <span class="font-bold text-[11px] uppercase tracking-wider">Modul Manajerial</span>
                </a>
                <a href="#admin-area" class="nav-item flex items-center gap-3 p-3.5 rounded-2xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                    <span class="font-bold text-[11px] uppercase tracking-wider">Modul Administrasi</span>
                </a>
                <a href="#office" class="nav-item flex items-center gap-3 p-3.5 rounded-2xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                        </path>
                    </svg>
                    <span class="font-bold text-[11px] uppercase tracking-wider">Lokasi Kantor</span>
                </a>
            </nav>

            <div class="p-6">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center justify-center w-full py-4 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-xl active:scale-95 transition-all">
                    Akses Dashboard
                </a>
            </div>
        </aside>

        <div id="scroll-ctx" class="flex-1 overflow-y-auto content-scroll bg-white dark:bg-[#09090b] scroll-smooth">
            <div class="max-w-4xl mx-auto px-12 py-20 space-y-32">

                <section id="hero" class="space-y-8">
                    <div
                        class="inline-block px-4 py-1.5 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.3em] rounded-full">
                        Executive Summary
                    </div>
                    <h1 class="text-7xl font-black dark:text-white leading-[0.85] tracking-tighter uppercase">
                        Sistem Penugasan<br />Terintegrasi<br /><span class="text-indigo-600">Marison Group.</span>
                    </h1>
                    <div class="grid grid-cols-2 gap-6 pt-10">
                        <div
                            class="p-8 bg-zinc-50 dark:bg-zinc-900 rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800 shadow-sm">
                            <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-2">Total Agenda
                            </p>
                            <h4 class="text-4xl font-black dark:text-white leading-none">{{ $totalAgenda }} <span
                                    class="text-xs text-indigo-600 block mt-1 uppercase">Entri Penugasan</span></h4>
                        </div>
                        <div
                            class="p-8 bg-zinc-50 dark:bg-zinc-900 rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800 shadow-sm">
                            <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-2">Sinkronisasi
                                Terakhir</p>
                            <h4 class="text-3xl font-black dark:text-white leading-none uppercase">{{ $lastBackup }}
                            </h4>
                        </div>
                    </div>
                </section>

                <section id="staff-area" class="scroll-mt-10 space-y-12">
                    <div class="section-divider">
                        <h2 class="text-xs font-black text-indigo-600 uppercase tracking-[0.4em] mb-2">Bagian I:
                            Operasional Lapangan</h2>
                        <h3 class="text-4xl font-black dark:text-white uppercase tracking-tighter">Eksekusi dan
                            Pelaporan Tugas</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div
                            class="p-8 bg-zinc-50 dark:bg-zinc-900 rounded-[2rem] border border-zinc-100 dark:border-zinc-800">
                            <h4 class="font-black text-sm uppercase dark:text-white mb-4 tracking-widest">Penjadwalan
                                Terstruktur</h4>
                            <p class="text-sm text-zinc-500 leading-relaxed">Staf dapat mengelola jadwal kerja melalui
                                kalender interaktif untuk memastikan setiap tugas memiliki alokasi waktu yang jelas dan
                                tidak berbenturan.</p>
                        </div>
                        <div
                            class="p-8 bg-zinc-50 dark:bg-zinc-900 rounded-[2rem] border border-zinc-100 dark:border-zinc-800">
                            <h4 class="font-black text-sm uppercase dark:text-white mb-4 tracking-widest">Monitoring
                                Progres</h4>
                            <p class="text-sm text-zinc-500 leading-relaxed">Dashboard memberikan visibilitas penuh
                                terhadap daftar tugas yang sedang berjalan maupun riwayat pekerjaan yang telah
                                diselesaikan secara transparan.</p>
                        </div>
                    </div>
                </section>

                <section id="manager-area" class="scroll-mt-10 space-y-12">
                    <div class="p-12 bg-zinc-900 rounded-[3.5rem] text-white shadow-2xl overflow-hidden">
                        <h2 class="text-xs font-black text-indigo-400 uppercase tracking-[0.4em] mb-6">Bagian II:
                            Kendali Manajerial</h2>
                        <h3 class="text-4xl font-black uppercase tracking-tighter leading-none mb-10">Validasi Hasil
                            &<br />Manajemen Kendala.</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            <div class="space-y-4">
                                <h5 class="font-black text-xs uppercase tracking-widest text-indigo-400">Verifikasi
                                    Berjenjang</h5>
                                <p class="text-xs text-zinc-400 leading-relaxed">Setiap laporan penugasan melewati
                                    filter persetujuan manajer untuk menjamin kualitas dan keabsahan pekerjaan staf di
                                    lapangan.</p>
                            </div>
                            <div class="space-y-4">
                                <h5 class="font-black text-xs uppercase tracking-widest text-red-500">Pusat Resolusi Isu
                                </h5>
                                <p class="text-xs text-zinc-400 leading-relaxed">Tersedia ruang khusus untuk memantau
                                    kendala teknis lapangan agar pimpinan dapat memberikan instruksi dan solusi secara
                                    cepat.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="admin-area" class="scroll-mt-10 space-y-12">
                    <div class="section-divider">
                        <h2 class="text-xs font-black text-indigo-600 uppercase tracking-[0.4em] mb-2">Bagian III:
                            Administrasi Pusat</h2>
                        <h3 class="text-4xl font-black dark:text-white uppercase tracking-tighter">Keamanan Data & Tata
                            Kelola User</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-6">
                        <div class="p-10 border border-zinc-100 dark:border-zinc-800 rounded-[3rem] shadow-sm">
                            <h4 class="font-black text-lg dark:text-white uppercase tracking-tighter mb-4">Otoritas
                                Pengguna & Audit Trail</h4>
                            <p class="text-sm text-zinc-500 leading-relaxed">Admin memiliki kendali penuh atas manajemen
                                peran pengguna dan pemantauan log aktivitas sistem untuk menjamin keamanan informasi
                                perusahaan.</p>
                        </div>
                        <div class="p-10 border border-zinc-100 dark:border-zinc-800 rounded-[3rem] shadow-sm">
                            <h4 class="font-black text-lg dark:text-white uppercase tracking-tighter mb-4">Personalisasi
                                & Keamanan Berlapis</h4>
                            <p class="text-sm text-zinc-500 leading-relaxed">Penyediaan pengaturan profil, tampilan
                                sistem, hingga pengamanan akun melalui autentikasi dua faktor untuk melindungi data
                                strategis.</p>
                        </div>
                    </div>
                </section>

                <section id="office"
                    class="scroll-mt-10 space-y-10 pb-20 border-t border-zinc-100 dark:border-zinc-800 pt-20">
                    <div class="text-center space-y-4">
                        <h3 class="text-5xl font-black dark:text-white uppercase tracking-tighter leading-none">Pusat
                            Operasional<br /><span class="text-zinc-400">Marison Bangun Nusantara.</span></h3>
                    </div>

                    <div class="flex justify-center">
                        <div
                            class="bg-zinc-100 dark:bg-zinc-800 rounded-[3.5rem] overflow-hidden border-8 border-white dark:border-zinc-800 shadow-2xl inline-block">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.6144882530666!2d110.21544159999999!3d-7.507739600000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a8fd18d272843%3A0x79e8f5b6551b88ad!2sKantor%20Pusat%20Marison%20Group!5e0!3m2!1sid!2sid!4v1768794634939!5m2!1sid!2sid"
                                width="600" height="400" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                    <p class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.5em] text-center">Titik
                        Koordinat Resmi Kantor Pusat Marison</p>
                </section>

                <footer class="text-center pb-20">
                    <div class="h-px w-24 bg-zinc-200 dark:bg-zinc-800 mx-auto mb-8"></div>
                    <p class="text-[9px] font-black text-zinc-400 uppercase tracking-[0.8em]">
                        Internal Report • PT Marison Bangun Nusantara • 2026
                    </p>
                </footer>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('scroll-ctx');
            const items = document.querySelectorAll('.nav-item');
            const sections = document.querySelectorAll('section');

            container.addEventListener('scroll', () => {
                let current = "";
                sections.forEach(s => {
                    if (container.scrollTop >= s.offsetTop - 250) current = s.getAttribute('id');
                });
                items.forEach(i => {
                    i.classList.toggle('nav-active', i.getAttribute('href') === `#${current}`);
                });
            });
        });
    </script>
</x-layouts.auth>
