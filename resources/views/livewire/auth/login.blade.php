<x-layouts.auth>
    <div
        class="fixed inset-0 flex items-center justify-center bg-zinc-50 dark:bg-zinc-950 transition-colors duration-500">

        <div class="grid h-full w-full lg:grid-cols-3 overflow-hidden">

            <div
                class="col-span-1 relative flex flex-col justify-center p-10 md:p-16
                        bg-white/70 dark:bg-zinc-900/60 backdrop-blur-2xl z-20
                        border-r border-zinc-200/50 dark:border-white/10 shadow-2xl">

                <div class="w-full max-w-sm mx-auto">
                    <div class="mb-10">
                        <h1
                            class="text-4xl font-extrabold tracking-tighter text-zinc-900 dark:text-white transition-colors">
                            Agenda<span class="text-indigo-600 dark:text-indigo-400">.</span>
                        </h1>
                        <p class="text-zinc-500 dark:text-zinc-400 mt-2 font-medium">Selesaikan tugasmu tepat waktu.</p>
                    </div>

                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
                        @csrf

                        <flux:input name="email" :label="__('Email Address')" type="email" required autofocus
                            class="bg-white/50 dark:bg-white/5 border-zinc-200 dark:border-white/10" />

                        <div class="relative">
                            <flux:input name="password" :label="__('Password')" type="password" required viewable
                                class="bg-white/50 dark:bg-white/5 border-zinc-200 dark:border-white/10" />
                            @if (Route::has('password.request'))
                                <flux:link class="absolute top-0 end-0 text-xs text-indigo-600 dark:text-indigo-400"
                                    :href="route('password.request')">Lupa?</flux:link>
                            @endif
                        </div>

                        <flux:checkbox name="remember" :label="__('Ingat saya')"
                            class="text-zinc-600 dark:text-zinc-400" />

                        <flux:button variant="primary" type="submit"
                            class="w-full py-6 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 shadow-xl shadow-indigo-500/20 transition-all">
                            {{ __('Sign In') }}
                        </flux:button>
                    </form>

                    @if (Route::has('register'))
                        <p class="mt-10 text-sm text-center text-zinc-500 dark:text-zinc-500">
                            Baru di sini? <flux:link :href="route('register')"
                                class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Buat Akun
                            </flux:link>
                        </p>
                    @endif
                </div>
            </div>

            <div
                class="col-span-2 relative hidden lg:flex items-center justify-center bg-zinc-100 dark:bg-zinc-900/30 transition-colors">

                <div class="absolute inset-0 overflow-hidden">
                    <div
                        class="absolute top-[-10%] right-[10%] w-[500px] h-[500px]
                                bg-indigo-200/50 dark:bg-indigo-600/20 rounded-full blur-[100px] animate-blob">
                    </div>
                    <div
                        class="absolute bottom-[-10%] left-[20%] w-[400px] h-[400px]
                                bg-purple-200/50 dark:bg-purple-600/20 rounded-full blur-[100px] animate-blob animation-delay-2000">
                    </div>
                </div>

                <div class="relative z-10 text-center">
                    <div class="flex justify-center mb-10">
                        <div
                            class="relative p-10 bg-white/40 dark:bg-white/5 backdrop-blur-3xl rounded-[3rem] border border-white/50 dark:border-white/10 shadow-2xl animate-float">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-24 h-24 text-indigo-600 dark:text-indigo-300" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <div
                                class="absolute -bottom-4 -right-4 p-4 bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-100 dark:border-zinc-700 animate-pulse">
                                <div class="w-8 h-1.5 bg-indigo-500 rounded-full mb-2"></div>
                                <div class="w-12 h-1.5 bg-zinc-200 dark:bg-zinc-600 rounded-full"></div>
                            </div>
                        </div>
                    </div>

                    <h2 class="text-5xl font-black text-zinc-900 dark:text-white tracking-tighter leading-tight">
                        KERJA CERDAS,<br />BUKAN KERJA KERAS.
                    </h2>
                    <p
                        class="mt-6 text-xl text-zinc-600 dark:text-zinc-400 max-w-md mx-auto font-light leading-relaxed">
                        Atur prioritas agenda Anda dengan sistem visual yang memanjakan mata.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.auth>

<style>
    /* Reset layout default agar benar-benar full screen */
    main,
    section {
        max-width: none !important;
        padding: 0 !important;
        margin: 0 !important;
        height: 100vh;
        overflow: hidden;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0) rotate(0);
        }

        50% {
            transform: translateY(-20px) rotate(2deg);
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

    .animate-float {
        animation: float 6s ease-in-out infinite;
    }

    .animate-blob {
        animation: blob 10s infinite ease-in-out;
    }

    .animation-delay-2000 {
        animation-delay: 2s;
    }
</style>
