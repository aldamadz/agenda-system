<x-layouts.guest>
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-12 lg:flex gap-12" x-data="{ activeRole: 'staff' }">

        <aside class="hidden lg:block w-64 shrink-0">
            <nav class="sticky top-28 space-y-1">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Navigasi Peran</p>
                <button @click="activeRole = 'staff'"
                    :class="activeRole === 'staff' ?
                        'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400' :
                        'text-slate-600 hover:bg-slate-100 dark:text-zinc-400 dark:hover:bg-zinc-900'"
                    class="w-full text-left px-4 py-2 rounded-lg font-medium transition-all flex items-center gap-3">
                    <flux:icon name="users" size="sm" /> Staff
                </button>
                <button @click="activeRole = 'atasan'"
                    :class="activeRole === 'atasan' ?
                        'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400' :
                        'text-slate-600 hover:bg-slate-100 dark:text-zinc-400 dark:hover:bg-zinc-900'"
                    class="w-full text-left px-4 py-2 rounded-lg font-medium transition-all flex items-center gap-3">
                    <flux:icon name="briefcase" size="sm" /> Atasan
                    </flux:icon> Atasan
                </button>
                <button @click="activeRole = 'admin'"
                    :class="activeRole === 'admin' ?
                        'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400' :
                        'text-slate-600 hover:bg-slate-100 dark:text-zinc-400 dark:hover:bg-zinc-900'"
                    class="w-full text-left px-4 py-2 rounded-lg font-medium transition-all flex items-center gap-3">
                    <flux:icon name="shield-check" size="sm" /> Administrator
                </button>
            </nav>
        </aside>

        <div class="flex-1 space-y-12">

            <header class="space-y-4 animate-in fade-in slide-in-from-bottom-4 duration-1000">
                <flux:heading size="xl" class="font-black tracking-tighter uppercase text-4xl lg:text-6xl">
                    Sistem <span class="text-indigo-600 underline decoration-indigo-200">Agenda</span>
                </flux:heading>
                <flux:subheading size="lg" class="max-w-3xl">
                    Satu platform untuk transparansi kerja tim. Pahami alur kerja dan fitur berdasarkan peran Anda dalam
                    organisasi.
                </flux:subheading>
            </header>



            <div class="lg:hidden flex p-1 bg-slate-200 dark:bg-zinc-900 rounded-xl">
                <button @click="activeRole = 'staff'"
                    :class="activeRole === 'staff' ? 'bg-white dark:bg-zinc-800 shadow-sm' : ''"
                    class="flex-1 py-2 rounded-lg text-sm font-bold transition-all">Staff</button>
                <button @click="activeRole = 'atasan'"
                    :class="activeRole === 'atasan' ? 'bg-white dark:bg-zinc-800 shadow-sm' : ''"
                    class="flex-1 py-2 rounded-lg text-sm font-bold transition-all">Atasan</button>
                <button @click="activeRole = 'admin'"
                    :class="activeRole === 'admin' ? 'bg-white dark:bg-zinc-800 shadow-sm' : ''"
                    class="flex-1 py-2 rounded-lg text-sm font-bold transition-all">Admin</button>
            </div>

            <div class="relative overflow-hidden min-h-[600px]">

                <div x-show="activeRole === 'staff'" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-x-12"
                    x-transition:enter-end="opacity-100 translate-x-0" class="space-y-8">
                    <div class="p-6 bg-indigo-600 rounded-3xl text-white shadow-xl shadow-indigo-200 dark:shadow-none">
                        <flux:heading size="lg" class="text-white font-bold mb-2">Fitur Bawahan (Staff/User)
                        </flux:heading>
                        <flux:text class="text-indigo-100">Dirancang untuk mempermudah perencanaan hari kerja,
                            melaporkan kemajuan, dan berkomunikasi secara transparan tanpa birokrasi rumit.</flux:text>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            class="glass-card p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 hover:scale-[1.02] transition-transform duration-300">
                            <div
                                class="size-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mb-4 text-indigo-600">
                                <flux:icon name="pencil-square" />
                            </div>
                            <flux:heading class="font-bold mb-2">1. Manajemen Agenda Mandiri</flux:heading>
                            <flux:text size="sm">Input judul, deskripsi, dan waktu. Pantau status secara real-time:
                                <flux:badge color="yellow" size="sm">Pending</flux:badge>, <flux:badge
                                    color="green" size="sm">Approved</flux:badge>, atau <flux:badge color="red"
                                    size="sm">Rejected</flux:badge>.
                            </flux:text>
                        </div>

                        <div
                            class="glass-card p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 hover:scale-[1.02] transition-transform duration-300">
                            <div
                                class="size-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mb-4 text-indigo-600">
                                <flux:icon name="calendar" />
                            </div>
                            <flux:heading class="font-bold mb-2">2. Kalender Kerja Pribadi</flux:heading>
                            <flux:text size="sm">Visualisasi jadwal otomatis untuk mencegah bentrok waktu.
                                Terkoneksi langsung dengan layar monitoring atasan.</flux:text>
                        </div>

                        <div
                            class="glass-card p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 hover:scale-[1.02] transition-transform duration-300 md:col-span-2">
                            <div
                                class="size-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mb-4 text-indigo-600">
                                <flux:icon name="exclamation-circle" />
                            </div>
                            <flux:heading class="font-bold mb-2">3. Pelaporan Kendala (Issue Reporting)</flux:heading>
                            <flux:text size="sm">Lapor cepat kendala teknis lapangan. Memberikan bukti sah atas
                                hambatan tugas agar penilaian kinerja tetap objektif.</flux:text>
                        </div>
                    </div>
                </div>

                <div x-show="activeRole === 'atasan'" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-x-12"
                    x-transition:enter-end="opacity-100 translate-x-0" class="space-y-8">
                    <div class="p-6 bg-slate-900 dark:bg-indigo-950 rounded-3xl text-white">
                        <flux:heading size="lg" class="text-white font-bold mb-2">Fitur Atasan (Manager)
                        </flux:heading>
                        <flux:text class="text-slate-400">Memungkinkan pemantauan tim secara efektif dan kontrol
                            kualitas operasional secara real-time.</flux:text>
                    </div>



                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="glass-card p-6 rounded-2xl border border-slate-200 dark:border-zinc-800">
                            <flux:heading class="font-bold mb-2 flex items-center gap-2">
                                <flux:icon name="presentation-chart-line" size="sm" /> Dashboard Monitoring
                            </flux:heading>
                            <flux:text size="sm">Melihat ringkasan status tim (Pending, Approved, Rejected). Berkat
                                <strong>wire:poll</strong>, data update otomatis tanpa refresh.
                            </flux:text>
                        </div>
                        <div class="glass-card p-6 rounded-2xl border border-slate-200 dark:border-zinc-800">
                            <flux:heading class="font-bold mb-2 flex items-center gap-2">
                                <flux:icon name="check-badge" size="sm" /> Menu Persetujuan
                            </flux:heading>
                            <flux:text size="sm">Review detail kegiatan dan lakukan aksi cepat
                                <strong>Approve</strong> atau <strong>Reject</strong> untuk setiap agenda.
                            </flux:text>
                        </div>
                        <div class="glass-card p-6 rounded-2xl border border-slate-200 dark:border-zinc-800">
                            <flux:heading class="font-bold mb-2 flex items-center gap-2">
                                <flux:icon name="chat-bubble-left-right" size="sm" /> Issue Center
                            </flux:heading>
                            <flux:text size="sm">Pantau laporan kendala dari staf dan berikan solusi hingga status
                                menjadi <strong>Resolved</strong>.</flux:text>
                        </div>
                        <div class="glass-card p-6 rounded-2xl border border-slate-200 dark:border-zinc-800">
                            <flux:heading class="font-bold mb-2 flex items-center gap-2">
                                <flux:icon name="globe-alt" size="sm" /> Monitoring Global
                            </flux:heading>
                            <flux:text size="sm">Cek jadwal tim pada kalender global untuk menghindari bentrok dan
                                tinjau riwayat untuk evaluasi.</flux:text>
                        </div>
                    </div>
                </div>

                <div x-show="activeRole === 'admin'" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-x-12"
                    x-transition:enter-end="opacity-100 translate-x-0" class="space-y-8">
                    <div
                        class="p-8 bg-gradient-to-br from-indigo-700 to-indigo-900 rounded-3xl text-white relative overflow-hidden">
                        <div class="relative z-10">
                            <flux:heading size="lg" class="text-white font-bold mb-2">Fitur Admin (System
                                Governance)</flux:heading>
                            <flux:text class="text-indigo-200">Pemegang kendali tertinggi untuk memastikan
                                infrastruktur data dan akses berjalan benar.</flux:text>
                        </div>
                        <div class="absolute -right-10 -bottom-10 opacity-10">
                            <flux:icon name="shield-check" size="xl" />
                        </div>
                    </div>



                    <div class="space-y-4">
                        <div
                            class="flex items-start gap-4 p-6 glass-card rounded-2xl border border-slate-200 dark:border-zinc-800">
                            <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl text-indigo-600">
                                <flux:icon name="users" />
                            </div>
                            <div>
                                <flux:heading class="font-bold mb-1 text-lg">User Management</flux:heading>
                                <flux:text size="sm">Registrasi akun, pengaturan peran (RBAC), hirarki
                                    atasan-bawahan (parent_id), dan aktivasi status akun.</flux:text>
                            </div>
                        </div>
                        <div
                            class="flex items-start gap-4 p-6 glass-card rounded-2xl border border-slate-200 dark:border-zinc-800">
                            <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl text-indigo-600">
                                <flux:icon name="document-magnifying-glass" />
                            </div>
                            <div>
                                <flux:heading class="font-bold mb-1 text-lg">System Logs (Audit Trail)</flux:heading>
                                <flux:text size="sm">Mencatat aktivitas login, modifikasi data, dan pelacakan
                                    error secara transparan demi keamanan.</flux:text>
                            </div>
                        </div>
                        <div
                            class="flex items-start gap-4 p-6 glass-card rounded-2xl border border-slate-200 dark:border-zinc-800">
                            <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl text-indigo-600">
                                <flux:icon name="command-line" />
                            </div>
                            <div>
                                <flux:heading class="font-bold mb-1 text-lg">Monitoring Seluruh Kantor (Global View)
                                </flux:heading>
                                <flux:text size="sm">Master dashboard untuk memantau seluruh departemen dan
                                    otoritas intervensi data jika terjadi kesalahan fatal.</flux:text>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-layouts.guest>
