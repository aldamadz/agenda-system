<?php
use App\Models\{Agenda, AgendaLog, ActivityLog};
use function Livewire\Volt\{computed, state, layout, title};
use Illuminate\Support\Str;
use Carbon\Carbon;

layout('components.layouts.app');
title('Issue Center');

state(['filter' => 'all', 'selectedIssue' => null]);

$issues = computed(function () {
    $user = auth()->user();
    $isAdmin = $user->role === 'admin';

    $query = AgendaLog::with(['agenda.user'])
        ->whereHas('agenda', function ($q) {
            $q->where('status', '!=', 'done');
        })
        ->latest();

    if (!$isAdmin) {
        $query->whereHas('agenda', function ($q) use ($user) {
            $q->where('approver_id', $user->id);
        });
    }

    if ($this->filter === 'request') {
        $query->where('issue_category', 'Extension Request');
    } elseif ($this->filter === 'issue') {
        $query->whereNotIn('issue_category', ['Extension Request', 'Progres', 'Update']);
    } else {
        $query->whereIn('issue_category', ['Extension Request', 'Technical Issue', 'Data Issue', 'Other Issue', 'Constraint']);
    }

    return $query->get()->unique('agenda_id');
});

$showDetail = function ($id) {
    $this->selectedIssue = AgendaLog::with(['agenda.user'])->find($id);
    $this->dispatch('modal-show', name: 'issue-detail');
};

$approveExtension = function ($logId) {
    $log = AgendaLog::findOrFail($logId);
    $agenda = $log->agenda;

    /** * PERBAIKAN REGEX:
     * Sekarang mencari format "15 Jan 2026, 17:00"
     * pola: angka(1-2) spasi kata(3 huruf) spasi angka(4) koma spasi angka(2):angka(2)
     */
    preg_match('/\d{1,2}\s[a-zA-Z]{3}\s\d{4},\s\d{2}:\d{2}/', $log->issue_description, $matches);

    if (isset($matches[0])) {
        // Karena format teks (15 Jan 2026), kita konversi kembali ke format Database
        $newDeadline = Carbon::parse($matches[0])->format('Y-m-d H:i:s');

        $agenda->update(['deadline' => $newDeadline]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'agenda_id' => $agenda->id,
            'action' => 'APPROVE',
            'description' => "Perpanjangan disetujui untuk '{$agenda->title}' hingga " . Carbon::parse($newDeadline)->format('d M Y, H:i'),
            'icon' => 'check-circle',
            'color' => 'emerald',
        ]);

        $log->delete();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Deadline Diperbarui']);
    } else {
        // Fallback jika format tidak sengaja berubah (opsional)
        $this->dispatch('swal', ['icon' => 'error', 'title' => 'Format Tanggal Tidak Ditemukan']);
    }
};

$resolveIssue = function ($id) {
    $log = AgendaLog::findOrFail($id);
    $isRequest = $log->issue_category === 'Extension Request';

    if ($isRequest) {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'agenda_id' => $log->agenda_id,
            'action' => 'REJECT',
            'description' => 'Permintaan perpanjangan ditolak.',
            'icon' => 'x-circle',
            'color' => 'red',
        ]);
    }

    $log->delete();
    $this->dispatch('swal', ['icon' => 'info', 'title' => 'Laporan Dihapus']);
};
?>

<div class="p-8 space-y-8 bg-[#fcfcfd] dark:bg-zinc-950 min-h-screen" wire:poll.30s>
    {{-- Top Header --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-2 h-8 bg-indigo-600 rounded-full"></div>
                <flux:heading size="xl"
                    class="!font-black tracking-tighter uppercase italic text-zinc-800 dark:text-white">
                    Issue <span class="text-indigo-600">Center</span>
                </flux:heading>
            </div>
            <flux:subheading class="ml-5">Kelola hambatan dan persetujuan waktu dalam satu pintu.</flux:subheading>
        </div>

        <div
            class="flex p-1.5 bg-zinc-200/50 dark:bg-white/5 backdrop-blur-md rounded-2xl border border-zinc-200 dark:border-white/10 shadow-inner">
            <button wire:click="$set('filter', 'all')"
                class="px-6 py-2 text-xs font-black uppercase rounded-xl transition-all {{ $filter === 'all' ? 'bg-white dark:bg-zinc-800 shadow-md text-indigo-600' : 'text-zinc-500 hover:text-zinc-700' }}">
                Semua
            </button>
            <button wire:click="$set('filter', 'request')"
                class="px-6 py-2 text-xs font-black uppercase rounded-xl transition-all {{ $filter === 'request' ? 'bg-purple-600 text-white shadow-lg shadow-purple-600/30' : 'text-zinc-500 hover:text-zinc-700' }}">
                Requests
            </button>
            <button wire:click="$set('filter', 'issue')"
                class="px-6 py-2 text-xs font-black uppercase rounded-xl transition-all {{ $filter === 'issue' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/30' : 'text-zinc-500 hover:text-zinc-700' }}">
                Kendala
            </button>
        </div>
    </div>

    {{-- Cards Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        @forelse ($this->issues as $log)
            <div wire:click="showDetail({{ $log->id }})"
                class="relative overflow-hidden bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-white/5 rounded-[2.5rem] p-7 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 group cursor-pointer shadow-sm border-l-4 {{ $log->issue_category === 'Extension Request' ? 'border-l-purple-500' : 'border-l-amber-500' }}">

                <div class="flex flex-col gap-6">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-16 h-16 rounded-[1.5rem] flex items-center justify-center shrink-0 shadow-inner {{ $log->issue_category === 'Extension Request' ? 'bg-purple-50 text-purple-600 dark:bg-purple-500/10' : 'bg-amber-50 text-amber-600 dark:bg-amber-500/10' }}">
                                <flux:icon.{{ $log->issue_category === 'Extension Request' ? 'clock' : 'exclamation-triangle' }}
                                    variant="solid" class="w-8 h-8" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="px-2 py-0.5 bg-zinc-100 dark:bg-white/5 rounded text-[9px] font-black text-zinc-500 uppercase tracking-tighter italic">PIC:
                                        {{ $log->agenda->user->name }}</span>
                                    <span
                                        class="text-[9px] font-black uppercase text-indigo-500 tracking-tighter italic">AGENDA:
                                        {{ $log->agenda->title }}</span>
                                </div>
                                <h3
                                    class="text-xl font-black text-zinc-800 dark:text-zinc-100 leading-tight tracking-tight uppercase italic">
                                    {{ Str::limit($log->issue_description, 60) }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2" wire:click.stop>
                        @if ($log->issue_category === 'Extension Request')
                            <flux:button size="sm" color="purple" variant="primary"
                                class="!rounded-xl font-black uppercase tracking-widest flex-1 shadow-lg shadow-purple-500/20"
                                wire:click="approveExtension({{ $log->id }})">Approve</flux:button>
                        @endif
                        <flux:button size="sm" variant="subtle" color="zinc"
                            class="!rounded-xl font-bold uppercase flex-1 border border-zinc-200 dark:border-white/10"
                            wire:click="resolveIssue({{ $log->id }})">
                            Hapus
                        </flux:button>
                    </div>
                </div>
            </div>
        @empty
            <div
                class="col-span-full py-40 flex flex-col items-center justify-center border-4 border-dashed border-zinc-100 dark:border-white/5 rounded-[4rem]">
                <p class="text-zinc-400 font-black uppercase tracking-[0.2em]">Semua Terkendali</p>
            </div>
        @endforelse
    </div>

    {{-- DETAIL MODAL --}}
    <flux:modal name="issue-detail" class="md:w-[650px] !rounded-[3rem] p-0 overflow-hidden" variant="flyout">
        @if ($selectedIssue)
            @php
                $pureDate = \Carbon\Carbon::parse($selectedIssue->log_date)->format('Y-m-d');
                $reportFullTime = \Carbon\Carbon::parse($pureDate . ' ' . $selectedIssue->log_time);
            @endphp

            <div class="p-8 space-y-8">
                <div class="text-center space-y-2">
                    <flux:heading size="xl" class="!font-black uppercase italic tracking-tighter leading-none">
                        Tinjauan <span class="text-indigo-600">Kendala</span>
                    </flux:heading>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        class="p-5 bg-zinc-50 dark:bg-white/5 rounded-3xl border border-zinc-100 dark:border-white/10 space-y-1">
                        <label class="text-[9px] font-black uppercase text-zinc-400 tracking-widest">Deadline
                            Aktif</label>
                        <p class="font-black text-red-500 text-lg italic uppercase leading-none">
                            {{ $selectedIssue->agenda->deadline->format('d M Y, H:i') }}</p>
                    </div>
                    <div
                        class="p-5 bg-zinc-50 dark:bg-white/5 rounded-3xl border border-zinc-100 dark:border-white/10 space-y-1">
                        <label class="text-[9px] font-black uppercase text-zinc-400 tracking-widest">Kategori</label>
                        <p class="font-black text-indigo-600 text-lg uppercase leading-none">
                            {{ $selectedIssue->issue_category }}</p>
                    </div>
                </div>

                <div class="p-6 bg-zinc-900 text-white rounded-[2rem] shadow-2xl">
                    <label class="text-[9px] font-black uppercase text-zinc-500 tracking-widest block mb-4">Pesan
                        PIC:</label>
                    <p class="text-lg font-medium italic leading-relaxed">
                        "{{ $selectedIssue->issue_description }}"
                    </p>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-center gap-4 pt-4">
                    <div class="text-[10px] font-bold text-zinc-400 uppercase leading-tight tracking-widest">
                        Dilaporkan Oleh {{ $selectedIssue->agenda->user->name }}<br>
                        <span class="text-indigo-500 italic">{{ $reportFullTime->format('d M Y, H:i') }} WIB</span>
                    </div>

                    <div class="flex gap-2 w-full md:w-auto">
                        <flux:modal.close>
                            <flux:button variant="ghost"
                                class="!rounded-2xl font-black uppercase tracking-widest text-xs">Tutup</flux:button>
                        </flux:modal.close>
                        @if ($selectedIssue->issue_category === 'Extension Request')
                            <div wire:click.stop>
                                <flux:button color="purple" wire:click="approveExtension({{ $selectedIssue->id }})"
                                    class="!rounded-2xl font-black uppercase tracking-widest text-xs px-8">Approve
                                </flux:button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
