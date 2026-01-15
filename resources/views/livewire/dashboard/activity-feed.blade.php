<?php

use App\Models\Agenda;
use App\Models\ActivityLog;
use App\Models\User;
use function Livewire\Volt\{state, computed};

$activities = computed(function () {
    $user = auth()->user();

    // 1. ADMIN: Lihat semua log dari semua user
    if ($user->role === 'admin') {
        return ActivityLog::with(['user', 'agenda'])
            ->latest()
            ->take(20)
            ->get();
    }

    // 2. MANAGER: Lihat log bawahan dan diri sendiri
    $subordinateIds = User::where('parent_id', $user->id)->pluck('id');
    if ($subordinateIds->isNotEmpty()) {
        return ActivityLog::with(['user', 'agenda'])
            ->whereIn('user_id', $subordinateIds->push($user->id))
            ->latest()
            ->take(15)
            ->get();
    }

    // 3. STAFF: Lihat agenda milik sendiri
    return Agenda::where('user_id', $user->id)->latest('updated_at')->take(10)->get();
});
?>

<div class="flex flex-col h-full bg-transparent" wire:poll.30s>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8 px-1">
        <div class="space-y-1">
            <h2
                class="text-[11px] font-black uppercase tracking-[0.25em] text-slate-900 dark:text-white flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-600"></span>
                </span>
                Aktivitas Terbaru
            </h2>
            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider leading-none">
                @if (auth()->user()->role === 'admin')
                    Sistem Monitoring Global
                @elseif(auth()->user()->role === 'manager')
                    Monitoring Tim
                @else
                    Timeline Saya
                @endif
            </p>
        </div>
        <flux:icon.bolt variant="mini" class="text-indigo-500 animate-pulse" />
    </div>

    {{-- Container Scroll --}}
    <div class="flex-1 overflow-y-auto pr-4 -mr-2 custom-scrollbar" style="max-height: 600px;">
        <div class="space-y-8 relative pb-6">
            @forelse($this->activities as $item)
                @php
                    $isLog = $item instanceof \App\Models\ActivityLog;
                    $waktu = $isLog ? $item->created_at->diffForHumans() : $item->updated_at->diffForHumans();
                @endphp

                <div class="flex gap-5 relative group">
                    {{-- Garis Timeline --}}
                    @if (!$loop->last)
                        <div
                            class="absolute left-[15px] top-10 bottom-[-2rem] w-[2px] bg-slate-100 dark:bg-slate-800 transition-all group-hover:bg-indigo-500/20">
                        </div>
                    @endif

                    {{-- Icon Badge --}}
                    <div class="relative flex-shrink-0">
                        <div
                            class="relative z-10 size-8 rounded-xl flex items-center justify-center border-2 bg-white dark:bg-slate-900 transition-all duration-300 group-hover:scale-110 shadow-sm
                        @if ($isLog) {{ $item->color == 'indigo' ? 'border-indigo-100 text-indigo-600' : '' }}
                            {{ $item->color == 'emerald' ? 'border-emerald-100 text-emerald-600' : '' }}
                            {{ $item->color == 'amber' ? 'border-amber-100 text-amber-600' : '' }}
                            {{ in_array($item->color, ['rose', 'red']) ? 'border-red-100 text-red-600' : '' }}
                        @else
                            {{ $item->status == 'ongoing' ? 'border-indigo-100 text-indigo-600' : '' }}
                            {{ $item->status == 'completed' ? 'border-emerald-100 text-emerald-600' : '' }}
                            {{ $item->status == 'rejected' ? 'border-red-100 text-red-600' : '' }} @endif">

                            {{-- Pengecekan Ikon Manual (Sangat Aman) --}}
                            @if ($isLog)
                                @if ($item->icon == 'plus')
                                    <flux:icon.plus variant="mini" />
                                @elseif($item->icon == 'check-circle')
                                    <flux:icon.check-circle variant="mini" />
                                @elseif($item->icon == 'x-circle')
                                    <flux:icon.x-circle variant="mini" />
                                @else
                                    <flux:icon.document-text variant="mini" />
                                @endif
                            @else
                                @if ($item->status == 'ongoing')
                                    <flux:icon.play variant="mini" />
                                @elseif($item->status == 'completed')
                                    <flux:icon.check variant="mini" />
                                @elseif($item->status == 'rejected')
                                    <flux:icon.x-mark variant="mini" />
                                @else
                                    <flux:icon.clock variant="mini" />
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0 pt-0.5">
                        <div class="flex justify-between items-start mb-1">
                            <span
                                class="text-[9px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 dark:bg-white/5 px-2 py-0.5 rounded-md border border-slate-100 dark:border-white/5">
                                {{ $waktu }}
                            </span>
                            @if ($isLog)
                                <flux:badge size="sm" variant="subtle" color="{{ $item->color }}"
                                    class="!text-[8px] !font-black uppercase tracking-tighter">
                                    {{ $item->action }}
                                </flux:badge>
                            @endif
                        </div>

                        @if ($isLog)
                            <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-snug font-medium">
                                <strong
                                    class="text-slate-900 dark:text-white font-black">{{ $item->user->name }}</strong>
                                {{ str_replace($item->user->name, '', $item->description) }}
                            </p>

                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                @if (auth()->user()->role !== 'staff' && auth()->id() !== $item->user_id)
                                    <span
                                        class="text-[8px] font-black px-2 py-0.5 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded border border-indigo-100 dark:border-indigo-500/20 uppercase">
                                        User: {{ $item->user->name }}
                                    </span>
                                @endif
                                @if ($item->agenda)
                                    <span
                                        class="text-[8px] font-bold text-slate-400 uppercase truncate max-w-[150px] bg-slate-50 dark:bg-white/5 px-1.5 py-0.5 rounded border border-slate-100 dark:border-white/5">
                                        Ref: {{ $item->agenda->title }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <h4
                                class="text-[11px] font-black text-slate-900 dark:text-white truncate uppercase tracking-tight">
                                {{ $item->title }}
                            </h4>
                            <div class="flex items-center gap-2 mt-1">
                                <span
                                    class="text-[9px] font-black uppercase tracking-widest {{ $item->status == 'completed' ? 'text-emerald-600' : ($item->status == 'rejected' ? 'text-red-600' : 'text-indigo-600') }}">
                                    {{ $item->status }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div
                    class="flex flex-col items-center justify-center py-20 text-center border-2 border-dashed border-slate-100 dark:border-white/5 rounded-[3rem]">
                    <flux:icon.clock class="size-10 text-slate-200 dark:text-slate-700 mb-4" />
                    <p class="text-[10px] font-black text-slate-300 dark:text-slate-600 uppercase tracking-[0.2em]">
                        Belum ada aktivitas</p>
                </div>
            @endforelse
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 20px;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #1e293b;
        }
    </style>
</div>
