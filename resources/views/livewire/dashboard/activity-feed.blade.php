<?php

use App\Models\Agenda;
use App\Models\ActivityLog;
use App\Models\User;
use function Livewire\Volt\{state, computed};

$activities = computed(function () {
    $user = auth()->user();

    // Ambil ID bawahan (termasuk diri sendiri)
    $subordinateIds = User::where('parent_id', $user->id)->pluck('id');

    if ($subordinateIds->isNotEmpty()) {
        // Tampilan Atasan: Menggabungkan log bawahan dan dirinya sendiri
        return ActivityLog::whereIn('user_id', $subordinateIds->push($user->id))
            ->with(['user', 'agenda'])
            ->latest()
            ->take(15) // Ditambah sedikit untuk scroll
            ->get();
    }

    // Tampilan Staff: Agenda miliknya sendiri
    return Agenda::where('user_id', $user->id)->latest('updated_at')->take(10)->get();
});
?>

<div class="flex flex-col h-full" wire:poll.30s>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6 px-1">
        <div>
            <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-900 dark:text-white">Aktivitas Terbaru
            </h2>
            <p class="text-[9px] text-slate-400 font-bold uppercase italic tracking-tighter mt-1 leading-none">Real-time
                Update</p>
        </div>
        <flux:icon.bolt variant="mini" class="text-indigo-500 animate-pulse" />
    </div>

    {{-- Kontainer Scroll --}}
    <div class="flex-1 overflow-y-auto pr-4 -mr-2 custom-scrollbar" style="max-height: 550px;">
        <div class="space-y-6 relative pb-4">
            @forelse($this->activities as $item)
                @php
                    $isLog = $item instanceof \App\Models\ActivityLog;
                @endphp

                <div class="flex gap-4 relative group">
                    {{-- Garis Timeline --}}
                    @if (!$loop->last)
                        <div
                            class="absolute left-4 top-8 bottom-[-1.5rem] w-px bg-slate-100 dark:bg-slate-800 group-hover:bg-indigo-500/20 transition-colors">
                        </div>
                    @endif

                    {{-- Icon Badge --}}
                    <div
                        class="relative z-10 size-8 rounded-xl flex-shrink-0 flex items-center justify-center border-2 transition-all group-hover:scale-110 shadow-sm
                        @if ($isLog) {{ $item->color == 'indigo' ? 'bg-indigo-50 border-indigo-100 text-indigo-600 dark:bg-indigo-900/20 dark:border-indigo-800' : '' }}
                            {{ $item->color == 'emerald' ? 'bg-emerald-50 border-emerald-200 text-emerald-600 dark:bg-emerald-900/20 dark:border-emerald-800' : '' }}
                            {{ $item->color == 'amber' ? 'bg-amber-50 border-amber-200 text-amber-600 dark:bg-amber-900/20 dark:border-amber-800' : '' }}
                            {{ $item->color == 'rose' || $item->color == 'red' ? 'bg-red-50 border-red-200 text-red-600 dark:bg-red-900/20 dark:border-red-800' : '' }}
                            {{ !in_array($item->color, ['indigo', 'emerald', 'amber', 'rose', 'red']) ? 'bg-slate-50 border-slate-200 text-slate-600' : '' }}
                        @else
                            {{ $item->status == 'ongoing' ? 'bg-indigo-50 border-indigo-200 text-indigo-600 dark:bg-indigo-900/20 dark:border-indigo-800' : '' }}
                            {{ $item->status == 'pending' ? 'bg-amber-50 border-amber-200 text-amber-600 dark:bg-amber-900/20 dark:border-amber-800' : '' }}
                            {{ $item->status == 'completed' ? 'bg-emerald-50 border-emerald-200 text-emerald-600 dark:bg-emerald-900/20 dark:border-emerald-800' : '' }}
                            {{ $item->status == 'rejected' ? 'bg-red-50 border-red-200 text-red-600 dark:bg-red-900/20 dark:border-red-800' : '' }} @endif
                    ">
                        @if ($isLog)
                            @if ($item->icon == 'plus')
                                <flux:icon.plus variant="mini" />
                            @elseif($item->icon == 'check-circle')
                                <flux:icon.check variant="mini" />
                            @else
                                <flux:icon.document-text variant="mini" />
                            @endif
                        @else
                            @if ($item->status == 'ongoing')
                                <flux:icon.play variant="mini" />
                            @elseif($item->status == 'pending')
                                <flux:icon.clock variant="mini" />
                            @elseif($item->status == 'completed')
                                <flux:icon.check variant="mini" />
                            @else
                                <flux:icon.x-mark variant="mini" />
                            @endif
                        @endif
                    </div>

                    {{-- Konten --}}
                    <div class="flex-1 min-w-0">
                        @if ($isLog)
                            {{-- View Atasan --}}
                            <div class="flex justify-between items-start">
                                <p
                                    class="text-[11px] font-black text-slate-900 dark:text-white uppercase tracking-tight">
                                    {{ $item->action }}</p>
                                <span
                                    class="text-[9px] font-bold text-slate-400 whitespace-nowrap ml-2 italic">{{ $item->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-[11px] text-slate-600 dark:text-slate-400 leading-snug mt-1">
                                {{ $item->description }}</p>

                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                @if (auth()->id() !== $item->user_id)
                                    <span
                                        class="text-[8px] font-black px-2 py-0.5 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-md uppercase tracking-tighter">
                                        Staff: {{ $item->user->name }}
                                    </span>
                                @endif
                                @if ($item->agenda)
                                    <span class="text-[8px] font-bold text-slate-400 uppercase truncate max-w-[150px]">
                                        Ref: {{ $item->agenda->title }}
                                    </span>
                                @endif
                            </div>
                        @else
                            {{-- View Staff --}}
                            <div class="flex justify-between items-start">
                                <p
                                    class="text-[11px] font-black text-slate-900 dark:text-white truncate uppercase tracking-tight">
                                    {{ $item->title }}</p>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <p
                                    class="text-[9px] font-black uppercase tracking-widest italic
                                    {{ $item->status == 'ongoing' ? 'text-indigo-600' : '' }}
                                    {{ $item->status == 'pending' ? 'text-amber-600' : '' }}
                                    {{ $item->status == 'completed' ? 'text-emerald-600' : '' }}
                                    {{ $item->status == 'rejected' ? 'text-red-600' : '' }}">
                                    {{ $item->status }}
                                </p>
                                <span class="text-slate-300 text-[9px]">•</span>
                                <span
                                    class="text-[9px] font-bold text-slate-400">{{ $item->updated_at->diffForHumans() }}</span>
                            </div>

                            @if ($item->status == 'rejected' && $item->notes)
                                <div
                                    class="mt-2 p-2 bg-red-50 dark:bg-red-500/5 rounded-xl border border-red-100 dark:border-red-500/20">
                                    <p class="text-[9px] text-red-700 dark:text-red-400 leading-tight">
                                        <span class="font-black uppercase tracking-tighter">Feedback:</span>
                                        {{ $item->notes }}
                                    </p>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <flux:icon.clock class="size-8 text-slate-200 mb-2" />
                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Belum ada aktivitas</p>
                </div>
            @endforelse
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #1e293b;
        }
    </style>
</div>
