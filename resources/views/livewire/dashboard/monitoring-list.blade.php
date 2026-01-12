<?php
use App\Models\{Agenda, AgendaStep, ActivityLog};
use function Livewire\Volt\{computed, state, usesFileUploads};

usesFileUploads();

state([
    'showNoteModal' => false,
    'editingStepId' => null,
    'stepNote' => '',
    'attachment' => null,
]);

// Query Agenda berdasarkan Role
$monitoringAgendas = computed(function () {
    $user = auth()->user();
    $query = Agenda::with(['user', 'steps'])->where('status', 'ongoing');

    return $user->parent_id === null ? $query->where('approver_id', $user->id)->latest()->get() : $query->where('user_id', $user->id)->latest()->get();
});

// Menangani Klik Checkbox
$clickStep = function ($stepId) {
    $step = AgendaStep::findOrFail($stepId);

    if ($step->is_completed) {
        // Logika Un-check
        $step->update([
            'is_completed' => false,
            'completed_at' => null,
            'attachment' => null,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'agenda_id' => $step->agenda_id,
            'action' => 'Update',
            'description' => auth()->user()->name . " membatalkan penyelesaian tahap: \"{$step->step_name}\".",
            'icon' => 'arrow-path',
            'color' => 'slate',
        ]);

        $this->dispatch('refresh-stats');
    } else {
        // Buka Modal Konfirmasi
        $this->editingStepId = $stepId;
        $this->stepNote = '';
        $this->attachment = null;
        $this->showNoteModal = true;
    }
};

// Simpan dari Modal
$saveStepCompletion = function () {
    $this->validate([
        'stepNote' => 'required|min:3',
        'attachment' => 'nullable|max:2048',
    ]);

    $step = AgendaStep::findOrFail($this->editingStepId);
    $data = [
        'is_completed' => true,
        'completed_at' => now(),
        'notes' => $this->stepNote,
    ];

    if ($this->attachment) {
        $path = $this->attachment->store('attachments', 'public');
        $data['attachment'] = $path;
    }

    $step->update($data);

    ActivityLog::create([
        'user_id' => auth()->id(),
        'agenda_id' => $step->agenda_id,
        'action' => 'Progress',
        'description' => auth()->user()->name . " menyelesaikan tahap: \"{$step->step_name}\".",
        'icon' => 'check-circle',
        'color' => 'emerald',
    ]);

    $this->reset(['showNoteModal', 'editingStepId', 'stepNote', 'attachment']);
    $this->dispatch('refresh-stats');
};

$cancelCompletion = function () {
    $this->reset(['showNoteModal', 'editingStepId', 'stepNote', 'attachment']);
};

$completeAgenda = function ($id) {
    $agenda = Agenda::findOrFail($id);
    $agenda->update(['status' => 'completed']);

    ActivityLog::create([
        'user_id' => auth()->id(),
        'agenda_id' => $agenda->id,
        'action' => 'Completion',
        'description' => auth()->user()->name . " telah menyelesaikan dan mengarsipkan agenda: \"{$agenda->title}\".",
        'icon' => 'archive-box-arrow-down',
        'color' => 'indigo',
    ]);

    $this->dispatch('refresh-stats');
    $this->dispatch('swal', icon: 'success', title: 'Berhasil', text: 'Agenda telah diarsipkan.');
};

$rejectAgenda = function ($id) {
    Agenda::findOrFail($id)->delete();
    $this->dispatch('refresh-stats');
};
?>

<section class="space-y-6 pt-10" wire:poll.30s>
    {{-- Header --}}
    <div class="flex items-center justify-between px-2">
        <div>
            <flux:heading size="xl" class="!font-black tracking-tighter uppercase italic">
                {{ auth()->user()->parent_id === null ? 'Monitoring Progres Tim' : 'Agenda Kerja Saya' }}
            </flux:heading>
            <flux:subheading>Pantau real-time setiap tahapan pekerjaan</flux:subheading>
        </div>
        <div
            class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-200 dark:via-white/10 to-transparent mx-8 hidden md:block">
        </div>
    </div>

    {{-- Agenda Cards --}}
    <div class="grid grid-cols-1 gap-8">
        @foreach ($this->monitoringAgendas as $agenda)
            @php $isAllStepsDone = $agenda->steps->every(fn($step) => $step->is_completed); @endphp

            <div
                class="bg-white dark:bg-white/5 rounded-[2.5rem] overflow-hidden transition-all duration-500 border border-slate-200 dark:border-white/10 shadow-xl relative group">
                @if ($isAllStepsDone)
                    <div class="absolute inset-0 bg-emerald-500/5 pointer-events-none animate-pulse"></div>
                @endif

                {{-- Card Header --}}
                <div
                    class="p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-slate-100 dark:border-white/10 relative z-10">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-2xl transition-all duration-500 {{ $isAllStepsDone ? 'bg-emerald-500 text-white rotate-6' : 'bg-indigo-500/10 text-indigo-500' }} flex items-center justify-center shadow-lg">
                            @if ($isAllStepsDone)
                                <flux:icon.check-badge variant="solid" class="w-7 h-7" />
                            @else
                                <flux:icon.clipboard-document-list variant="solid" class="w-7 h-7" />
                            @endif
                        </div>
                        <div>
                            <h4 class="font-black text-lg text-slate-900 dark:text-white leading-tight italic">
                                {{ $agenda->title }}</h4>
                            <div
                                class="flex items-center gap-2 mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                <span>PIC: {{ $agenda->user->name }}</span>
                                <span class="text-indigo-500 transition-all">•
                                    {{ $agenda->steps->where('is_completed', true)->count() }} /
                                    {{ $agenda->steps->count() }} TAHAP</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @if ($isAllStepsDone)
                            <flux:button size="sm" color="emerald" variant="primary"
                                class="!rounded-xl font-bold animate-bounce"
                                wire:click="completeAgenda({{ $agenda->id }})" wire:confirm="Arsipkan agenda ini?">
                                Tutup & Arsipkan
                            </flux:button>
                        @endif
                        @if (auth()->user()->parent_id === null)
                            <flux:button variant="ghost" size="sm" icon="trash" color="red"
                                class="!rounded-xl" wire:click="rejectAgenda({{ $agenda->id }})"
                                wire:confirm="Hapus seluruh agenda ini?" />
                        @endif
                    </div>
                </div>

                {{-- Steps Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 relative z-10">
                    @foreach ($agenda->steps as $step)
                        <div
                            class="p-6 flex flex-col justify-between min-h-[140px] border-b md:border-b-0 md:border-r border-slate-100 dark:border-white/5 last:border-r-0 hover:bg-slate-50 dark:hover:bg-white/5 transition-all duration-300">

                            <div class="space-y-4">
                                <div class="flex justify-between items-start gap-3">
                                    <p
                                        class="text-sm leading-tight transition-all duration-500 {{ $step->is_completed ? 'text-slate-400 line-through opacity-60' : 'text-slate-800 dark:text-white font-bold' }}">
                                        {{ $step->step_name }}
                                    </p>
                                    <input type="checkbox" {{ $step->is_completed ? 'checked' : '' }}
                                        wire:click="clickStep({{ $step->id }})"
                                        class="w-5 h-5 rounded-lg border-slate-300 dark:border-white/10 text-indigo-600 cursor-pointer transition-all duration-300 transform active:scale-125">
                                </div>

                                @if ($step->attachment)
                                    <a href="{{ asset('storage/' . $step->attachment) }}" target="_blank"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-[10px] font-black uppercase hover:bg-indigo-500 hover:text-white transition-colors">
                                        <flux:icon.paper-clip variant="micro" /> Bukti Kerja
                                    </a>
                                @endif
                            </div>

                            <div
                                class="mt-4 pt-4 border-t border-slate-100 dark:border-white/5 flex items-center justify-between text-[10px] font-bold text-slate-400">
                                <div class="flex items-center gap-2">
                                    <flux:icon.clock variant="micro" />
                                    <span>{{ $step->deadline?->format('H:i') ?? '--:--' }}</span>
                                </div>
                                @if ($step->is_completed)
                                    <span
                                        class="text-emerald-500 font-black uppercase tracking-tighter animate-pulse">Selesai</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Modal Konfirmasi --}}
    <flux:modal wire:model="showNoteModal" class="md:w-[450px] !rounded-[2.5rem]" variant="flyout"
        x-on:close="$wire.cancelCompletion()">
        <div class="space-y-6 p-2">
            <div class="text-center">
                <div
                    class="w-16 h-16 bg-indigo-500/20 rounded-3xl flex items-center justify-center text-indigo-500 mx-auto mb-4 rotate-3">
                    <flux:icon.document-check variant="solid" class="w-8 h-8" />
                </div>
                <flux:heading size="lg" class="!font-black uppercase italic">Konfirmasi Progres</flux:heading>
                <flux:subheading>Isi catatan untuk menyelesaikan tahap ini</flux:subheading>
            </div>

            <div class="space-y-4">
                <flux:textarea label="Catatan Pekerjaan" wire:model="stepNote" placeholder="Apa yang Anda kerjakan?"
                    rows="3" />

                <div
                    class="p-4 rounded-3xl bg-slate-50 dark:bg-white/5 border border-dashed border-slate-200 dark:border-white/10 transition-all hover:border-indigo-400">
                    <flux:label class="mb-2 block !font-bold !text-[10px] uppercase">Upload Bukti (Opsional)
                    </flux:label>
                    <input type="file" wire:model="attachment"
                        class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 transition-all" />

                    {{-- Progress Indicator --}}
                    <div wire:loading wire:target="attachment" class="mt-3">
                        <div class="flex items-center gap-2 text-[10px] text-indigo-500 font-bold">
                            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Mengunggah dokumen...
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <flux:button variant="ghost" class="flex-1 !rounded-2xl" wire:click="cancelCompletion">Batal
                </flux:button>
                <flux:button variant="primary" color="indigo" class="flex-1 !rounded-2xl font-black"
                    wire:click="saveStepCompletion" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveStepCompletion">Kirim Progres</span>
                    <span wire:loading wire:target="saveStepCompletion">Menyimpan...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>
