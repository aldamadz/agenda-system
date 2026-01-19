<?php
use App\Models\{Agenda, AgendaStep, ActivityLog, AgendaLog, User};
use Carbon\Carbon;
use function Livewire\Volt\{computed, state, usesFileUploads, on};

usesFileUploads();

state([
    'showIssueModal' => false,
    'selectedAgendaId' => null,
    'currentAgendaDeadline' => '',
    'issueCategory' => '',
    'issueDescription' => '',
    'requestedDeadline' => '',
    'showNoteModal' => false,
    'editingStepId' => null,
    'stepNote' => '',
]);

// Fungsi membuka modal laporan
$openIssueModal = function ($id) {
    $agenda = Agenda::findOrFail($id);
    $this->selectedAgendaId = $id;
    $this->currentAgendaDeadline = $agenda->deadline->format('d M Y, H:i');
    $this->reset(['issueCategory', 'issueDescription', 'requestedDeadline']);
    $this->showIssueModal = true;
};

// Query Monitoring Agendas (Mendukung Admin melihat semua)
$monitoringAgendas = computed(function () {
    $user = auth()->user();
    $isAdmin = $user->role === 'admin' || $user->is_admin;
    $isManager = User::where('parent_id', $user->id)->exists();

    $query = Agenda::with(['user', 'steps', 'logs'])->where('status', 'ongoing');

    if (!$isAdmin) {
        if ($isManager) {
            $query->where(function ($q) use ($user) {
                $q->where('approver_id', $user->id)->orWhere('user_id', $user->id);
            });
        } else {
            $query->where('user_id', $user->id);
        }
    }
    return $query->latest()->get();
});

// Submit Kendala & Perpanjangan
$submitIssue = function () {
    $this->validate([
        'issueCategory' => 'required',
        'issueDescription' => 'required|min:5',
        'requestedDeadline' => 'nullable|after:now',
    ]);

    $isExtension = !empty($this->requestedDeadline);
    $now = now();
    $category = $isExtension ? 'Extension Request' : $this->issueCategory;

    $deadlineInfo = $isExtension ? 'MEMINTA PERPANJANGAN KE: ' . Carbon::parse($this->requestedDeadline)->format('d M Y, H:i') . " WIB (Deadline Lama: {$this->currentAgendaDeadline}). Keterangan: " : "LAPORAN KENDALA JAM {$now->format('H:i')}: ";

    AgendaLog::create([
        'agenda_id' => $this->selectedAgendaId,
        'issue_category' => $category,
        'issue_description' => $deadlineInfo . $this->issueDescription,
        'progress_note' => $isExtension ? 'MENUNGGU PERSETUJUAN' : 'LAPORAN KENDALA',
        'log_date' => $now->format('Y-m-d'),
        'log_time' => $now->format('H:i:s'),
    ]);

    ActivityLog::create([
        'user_id' => auth()->id(),
        'agenda_id' => $this->selectedAgendaId,
        'action' => $isExtension ? 'REQUEST' : 'ISSUE',
        'description' => auth()->user()->name . ($isExtension ? ' mengajukan perpanjangan.' : ' melaporkan kendala.'),
        'icon' => $isExtension ? 'clock' : 'exclamation-triangle',
        'color' => $isExtension ? 'purple' : 'amber',
    ]);

    $this->reset(['showIssueModal', 'selectedAgendaId', 'issueCategory', 'issueDescription', 'requestedDeadline']);
    $this->dispatch('swal', ['icon' => 'success', 'title' => 'Laporan Berhasil Terkirim']);
};

// Logika Checklist
$clickStep = function ($stepId) {
    $step = AgendaStep::findOrFail($stepId);
    if ($step->is_completed) {
        $step->update(['is_completed' => false, 'completed_at' => null, 'notes' => null]);
    } else {
        $this->editingStepId = $stepId;
        $this->stepNote = '';
        $this->showNoteModal = true;
    }
};

$saveStepCompletion = function () {
    $step = AgendaStep::findOrFail($this->editingStepId);
    $step->update(['is_completed' => true, 'completed_at' => now(), 'notes' => $this->stepNote]);
    $this->reset(['showNoteModal', 'editingStepId', 'stepNote']);
    $this->dispatch('swal', ['icon' => 'success', 'title' => 'Progres Disimpan']);
};

$completeAgenda = function ($id) {
    Agenda::findOrFail($id)->update(['status' => 'completed']);
    $this->dispatch('swal', ['icon' => 'success', 'title' => 'Agenda Berhasil Diarsipkan']);
};
?>

<section class="space-y-6 pt-10" wire:poll.60s>
    <div class="px-6 flex justify-between items-end">
        <div>
            <flux:heading size="xl" class="!font-black tracking-tighter uppercase italic">Monitoring System
            </flux:heading>
            <flux:subheading>Pantau progres tim dan kelola hambatan secara real-time.</flux:subheading>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 px-6 pb-20">
        @forelse ($this->monitoringAgendas as $agenda)
            @php
                $isAllStepsDone = $agenda->steps->isNotEmpty() && $agenda->steps->every(fn($s) => $s->is_completed);
                $isOverdue = $agenda->deadline->isPast() && !$isAllStepsDone;
            @endphp

            <div
                class="bg-white dark:bg-zinc-900 border-2 {{ $isOverdue ? 'border-red-500' : 'border-slate-100 dark:border-white/5' }} rounded-[2.5rem] overflow-hidden shadow-xl">
                <div class="p-8 border-b border-slate-100 dark:border-white/5">
                    <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                        <div class="flex items-start gap-6 flex-1">
                            <div
                                class="w-16 h-16 shrink-0 rounded-3xl flex items-center justify-center text-white bg-indigo-600 shadow-xl">
                                <flux:icon name="clipboard-document-list" variant="solid" class="w-8 h-8" />
                            </div>

                            <div class="space-y-3">
                                <h4 class="font-black text-2xl uppercase italic tracking-tight dark:text-white">
                                    {{ $agenda->title }}</h4>
                                <div class="flex flex-wrap gap-3 items-center text-[10px] font-black uppercase">
                                    <span
                                        class="bg-slate-100 dark:bg-white/5 px-3 py-1 rounded-full text-slate-500 tracking-widest">PIC:
                                        {{ $agenda->user->name }}</span>
                                    <span
                                        class="px-3 py-1 rounded-full {{ $isOverdue ? 'bg-red-500 text-white' : 'bg-slate-100 dark:bg-white/5 text-slate-500' }} tracking-widest">
                                        DEADLINE: {{ $agenda->deadline->format('d M Y, H:i') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            @if (auth()->id() === $agenda->user_id && !$isAllStepsDone)
                                <flux:button size="sm" variant="subtle" color="amber"
                                    class="!rounded-xl font-bold uppercase"
                                    wire:click="openIssueModal({{ $agenda->id }})">
                                    Lapor Kendala
                                </flux:button>
                            @endif
                            @if ($isAllStepsDone)
                                <flux:button size="md" variant="filled" color="emerald"
                                    class="!rounded-xl font-black italic uppercase"
                                    wire:click="completeAgenda({{ $agenda->id }})">
                                    Arsipkan
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($agenda->steps->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-4 bg-slate-50/50 dark:bg-white/5">
                        @foreach ($agenda->steps as $step)
                            <div
                                class="p-6 border-r border-slate-100 dark:border-white/5 last:border-r-0 min-h-[100px]">
                                <div class="flex justify-between items-start">
                                    <span
                                        class="text-sm font-bold {{ $step->is_completed ? 'text-slate-300 line-through' : 'text-slate-700 dark:text-slate-200' }}">{{ $step->step_name }}</span>
                                    <input type="checkbox" @checked($step->is_completed)
                                        wire:click.stop="clickStep({{ $step->id }})"
                                        class="w-5 h-5 rounded-lg border-slate-300 text-indigo-600 cursor-pointer focus:ring-0">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="py-32 text-center border-4 border-dashed border-slate-100 dark:border-white/5 rounded-[3rem]">
                <p class="text-slate-400 font-black uppercase tracking-widest">Tidak ada agenda aktif</p>
            </div>
        @endforelse
    </div>

    {{-- MODAL GABUNGAN --}}
    <flux:modal wire:model="showIssueModal" class="md:w-[450px] !rounded-[2.5rem]">
        <form wire:submit="submitIssue" class="space-y-6 p-2">
            <div class="text-center">
                <flux:heading size="lg" class="!font-black text-amber-500 uppercase italic">Laporan Monitoring
                </flux:heading>
                <div
                    class="mt-2 px-4 py-2 bg-slate-50 dark:bg-zinc-800 rounded-xl border border-dashed border-slate-200 dark:border-white/10">
                    <p class="text-[10px] font-black uppercase text-slate-400">Deadline Saat Ini</p>
                    <p class="font-bold text-red-500 italic uppercase">{{ $currentAgendaDeadline }} WIB</p>
                </div>
            </div>

            <div class="space-y-4">
                <flux:select label="Kategori Laporan" wire:model="issueCategory">
                    <flux:select.option value="Teknis">Masalah Teknis</flux:select.option>
                    <flux:select.option value="Data">Masalah Data</flux:select.option>
                    <flux:select.option value="Koordinasi">Koordinasi Lapangan</flux:select.option>
                    <flux:select.option value="Lainnya">Lainnya</flux:select.option>
                </flux:select>

                <flux:input type="datetime-local" label="Ajukan Deadline Baru" wire:model="requestedDeadline" />
                <flux:textarea label="Keterangan / Alasan" wire:model="issueDescription" rows="4" />
            </div>

            <div class="flex gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost" class="w-full !rounded-2xl font-bold uppercase">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="filled" color="amber"
                    class="flex-1 !rounded-2xl font-black uppercase">Kirim Laporan</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- MODAL CATATAN --}}
    <flux:modal wire:model="showNoteModal" class="md:w-[400px] !rounded-[2.5rem]">
        <div class="space-y-6 p-2">
            <flux:heading size="lg" class="!font-black uppercase italic text-center text-indigo-600">Hasil Tahap
                Ini</flux:heading>
            <flux:textarea label="Catatan Progres" wire:model="stepNote" placeholder="Apa yang sudah dikerjakan?"
                rows="3" />
            <div class="flex gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost" class="w-full !rounded-2xl font-bold uppercase">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="filled" color="indigo" class="flex-1 !rounded-2xl font-black uppercase"
                    wire:click="saveStepCompletion">Simpan Progres</flux:button>
            </div>
        </div>
    </flux:modal>
</section>
