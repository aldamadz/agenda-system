<x-layouts.auth>
    <div
        class="fixed inset-0 w-screen h-screen flex overflow-hidden bg-white dark:bg-[#050505] transition-colors duration-700">

        <aside
            class="w-full lg:w-[480px] h-full flex flex-col p-8 md:p-14 z-20 bg-white dark:bg-zinc-950 border-r border-zinc-100 dark:border-white/5 shadow-2xl overflow-y-auto overflow-x-hidden">
            <div class="mb-12 flex items-center gap-3 cursor-pointer group/logo" onclick="window.location.href='/'">
                <div
                    class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 transition-all duration-500 group-hover/logo:rotate-[15deg] group-hover/logo:scale-110">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-2xl font-black tracking-tighter text-zinc-900 dark:text-white uppercase font-sans">
                    Agenda<span class="text-indigo-600">.</span>
                </span>
            </div>

            <div class="flex-1 flex flex-col justify-center animate-soft-in">
                <div class="space-y-2 mb-10">
                    <h1 class="text-4xl font-black text-zinc-900 dark:text-white tracking-tight leading-tight">Mulai
                        <br>Perjalananmu.</h1>
                    <p class="text-zinc-500 dark:text-zinc-400 font-medium">Satu langkah menuju manajemen waktu yang
                        sempurna.</p>
                </div>

                <x-auth-session-status class="mb-6" :status="session('status')" />

                <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
                    @csrf
                    <flux:input name="name" :label="__('Name')" type="text" required autofocus
                        :placeholder="__('Nama lengkap')" class="dark:text-zinc-200" />
                    <flux:input name="email" :label="__('Email')" type="email" required
                        placeholder="email@contoh.com" class="dark:text-zinc-200" />
                    <flux:input name="password" :label="__('Password')" type="password" required viewable
                        class="dark:text-zinc-200" />
                    <flux:input name="password_confirmation" :label="__('Confirm Password')" type="password" required
                        viewable class="dark:text-zinc-200" />

                    <div class="pt-4">
                        <button type="submit"
                            class="group relative w-full h-14 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl overflow-hidden transition-all duration-500 shadow-xl shadow-indigo-600/20 active:scale-95">
                            <span class="relative z-10">{{ __('Buat Akun Sekarang') }}</span>
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-indigo-400 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                        </button>
                    </div>
                </form>

                <div class="mt-10 text-center text-sm text-zinc-500">
                    <span>Sudah punya akun?</span>
                    <flux:link :href="route('login')" wire:navigate
                        class="ml-1 font-bold text-indigo-600 dark:text-indigo-400 hover:underline transition-colors">
                        Masuk kembali
                    </flux:link>
                </div>
            </div>

            <footer
                class="mt-12 text-[10px] text-zinc-400 dark:text-zinc-600 uppercase tracking-[0.2em] font-bold text-center">
                &copy; {{ date('Y') }} Agenda Core v1.0
            </footer>
        </aside>

        <main x-data="{
            hovering: false,
            percent1: 80,
            percent2: 45,
            timer: null,
            startAnimate() {
                this.hovering = true;
                this.timer = setInterval(() => {
                    this.percent1 = Math.floor(Math.random() * (99 - 70 + 1)) + 70;
                    this.percent2 = Math.floor(Math.random() * (60 - 30 + 1)) + 30;
                }, 800);
            },
            stopAnimate() {
                this.hovering = false;
                clearInterval(this.timer);
                this.percent1 = 80;
                this.percent2 = 45;
            }
        }"
            class="hidden lg:flex flex-1 relative bg-zinc-50 dark:bg-[#050505] items-center justify-center overflow-hidden [perspective:2000px]">

            <div class="absolute inset-0 z-0">
                <div
                    class="absolute top-[-10%] right-[-10%] w-[800px] h-[800px] bg-indigo-500/10 dark:bg-indigo-600/10 rounded-full blur-[140px] animate-blob-ultra">
                </div>
                <div
                    class="absolute bottom-[-10%] left-[10%] w-[700px] h-[700px] bg-purple-500/10 dark:bg-purple-600/10 rounded-full blur-[140px] animate-blob-ultra animation-delay-4000">
                </div>
            </div>

            <div @mouseenter="startAnimate()" @mouseleave="stopAnimate()"
                class="group/main relative z-10 w-full max-w-2xl p-12 transition-all duration-[1200ms] [transform-style:preserve-3d] will-change-transform cursor-default">

                <div
                    class="relative transition-all duration-[1200ms] cubic-bezier-main group-hover/main:[transform:rotateX(8deg)_rotateY(-15deg)_scale(1.02)]">

                    <div
                        class="p-10 bg-white/40 dark:bg-white/[0.03] backdrop-blur-3xl rounded-[4rem] border border-white/60 dark:border-white/10 shadow-2xl animate-float-smooth">
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-[3rem] p-10 shadow-inner border border-zinc-100 dark:border-zinc-800 transition-all duration-700">

                            <div class="space-y-10">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-5">
                                        <div
                                            class="w-14 h-14 rounded-[1.25rem] bg-indigo-600 flex items-center justify-center text-white shadow-2xl shadow-indigo-600/40">
                                            <svg class="w-8 h-8 transition-transform duration-1000"
                                                :class="hovering ? 'rotate-180' : ''" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="h-4 w-36 bg-zinc-100 dark:bg-zinc-800 rounded-full"></div>
                                            <div class="h-2 w-24 bg-zinc-50 dark:bg-zinc-800/50 rounded-full"></div>
                                        </div>
                                    </div>
                                    <div
                                        class="px-3 py-1 rounded-full border border-indigo-100 dark:border-indigo-500/30 text-[10px] font-bold text-indigo-600 animate-pulse">
                                        LIVE SYNC</div>
                                </div>

                                <div class="space-y-8">
                                    <div class="space-y-3">
                                        <div
                                            class="flex justify-between items-center text-[10px] font-black text-indigo-500 uppercase tracking-widest">
                                            <span>Efficiency Rate</span>
                                            <span x-text="percent1 + '%'" class="transition-all duration-300"></span>
                                        </div>
                                        <div
                                            class="h-2.5 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden shadow-inner">
                                            <div class="h-full bg-indigo-600 rounded-full transition-all duration-700 ease-in-out"
                                                :style="'width: ' + percent1 + '%'"></div>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div
                                            class="flex justify-between items-center text-[10px] font-black text-purple-500 uppercase tracking-widest">
                                            <span>Task Load</span>
                                            <span x-text="percent2 + '%'" class="transition-all duration-300"></span>
                                        </div>
                                        <div
                                            class="h-2.5 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden shadow-inner">
                                            <div class="h-full bg-purple-500 rounded-full transition-all duration-700 ease-in-out"
                                                :style="'width: ' + percent2 + '%'"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="absolute -top-12 -left-12 p-7 bg-white dark:bg-zinc-800 rounded-[2.5rem] shadow-2xl border border-zinc-100 dark:border-zinc-700 transition-all duration-[1000ms] [transform:translateZ(100px)] group-hover/main:[transform:translateZ(150px)_translateY(-20px)_translateX(-20px)]">
                        <div class="flex items-baseline gap-1">
                            <span x-text="percent1"
                                class="text-indigo-600 dark:text-indigo-400 font-black text-5xl tracking-tighter italic leading-none transition-all duration-300"></span>
                            <span class="text-indigo-400 text-xl font-bold">.9</span>
                        </div>
                        <div class="text-[9px] text-zinc-400 uppercase font-black tracking-[0.3em] mt-2">Uptime Score
                        </div>
                    </div>
                </div>

                <div class="mt-28 text-center transition-all duration-700 group-hover/main:opacity-100 opacity-60">
                    <h2
                        class="text-4xl font-black text-zinc-900 dark:text-white tracking-tighter mb-4 italic transition-transform group-hover/main:scale-105">
                        Smart Analytics.</h2>
                    <p class="text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto leading-relaxed font-medium">Data
                        performa Anda divisualisasikan secara dinamis untuk hasil yang optimal.</p>
                </div>
            </div>
        </main>
    </div>
</x-layouts.auth>

<style>
    .cubic-bezier-main {
        transition-timing-function: cubic-bezier(0.23, 1, 0.32, 1);
    }

    /* Keyframes Dasar */
    @keyframes float-smooth {

        0%,
        100% {
            transform: translateY(0) rotate(0);
        }

        50% {
            transform: translateY(-25px) rotate(0.8deg);
        }
    }

    @keyframes blob-ultra {
        0% {
            transform: scale(1) translate(0, 0);
        }

        50% {
            transform: scale(1.2) translate(40px, -40px);
            opacity: 0.8;
        }

        100% {
            transform: scale(1) translate(0, 0);
        }
    }

    @keyframes soft-in {
        from {
            opacity: 0;
            transform: translateX(-20px);
            filter: blur(10px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
            filter: blur(0);
        }
    }

    .animate-float-smooth {
        animation: float-smooth 8s ease-in-out infinite;
    }

    .animate-blob-ultra {
        animation: blob-ultra 20s infinite alternate cubic-bezier(0.45, 0, 0.55, 1);
    }

    .animate-soft-in {
        animation: soft-in 1.2s cubic-bezier(0.23, 1, 0.32, 1) forwards;
    }

    .animation-delay-4000 {
        animation-delay: 4s;
    }

    .group\/main:hover .animate-float-smooth {
        animation-play-state: paused;
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 4px;
    }

    ::-webkit-scrollbar-thumb {
        background: #6366f1;
        border-radius: 10px;
    }
</style>
