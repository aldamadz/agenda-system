<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\WithPagination;
use function Livewire\Volt\{state, computed, layout, title, usesPagination};

layout('components.layouts.app');
title('Pengaturan Pengguna');

usesPagination();

state(['search' => '']);

$updateRole = function ($userId, $newRole) {
    User::findOrFail($userId)->update(['role' => $newRole]);
    session()->flash('status', 'Perubahan role berhasil disimpan.');
};

$updateParent = function ($userId, $parentId) {
    User::findOrFail($userId)->update(['parent_id' => $parentId ?: null]);
    session()->flash('status', 'Struktur organisasi diperbarui.');
};

$users = computed(function () {
    return User::with(['atasan'])
        ->where('id', '!=', auth()->id())
        ->when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
        })
        ->latest()
        ->paginate(15);
});

$managers = computed(function () {
    return User::whereIn('role', ['manager', 'admin'])->get();
});
?>

<div class="p-6 lg:p-10 max-w-6xl mx-auto space-y-6">
    {{-- Header Sederhana --}}
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-zinc-800 pb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pengaturan Pengguna</h1>
            <p class="text-sm text-slate-500 mt-1">Manajemen hak akses dan struktur pelaporan tim.</p>
        </div>
        <div class="w-full sm:w-64">
            <flux:input wire:model.live.debounce.400ms="search" placeholder="Cari nama pengguna..." icon="magnifying-glass"
                size="sm" />
        </div>
    </div>

    @if (session('status'))
        <div
            class="bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 p-3 rounded-lg text-sm font-medium border border-blue-100 dark:border-blue-500/20">
            {{ session('status') }}
        </div>
    @endif

    {{-- Daftar List --}}
    <div class="space-y-1">
        @foreach ($this->users as $user)
            <div
                class="flex flex-col md:flex-row md:items-center justify-between p-4 bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 rounded-lg hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors gap-4">

                {{-- Info Utama --}}
                <div class="flex items-center gap-4 min-w-[250px]">
                    <div
                        class="size-10 rounded-full bg-slate-200 dark:bg-zinc-700 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-zinc-300">
                        {{ $user->initials() }}
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white leading-tight">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                    </div>
                </div>

                {{-- Kontrol Role & Atasan --}}
                <div class="flex flex-1 flex-col sm:flex-row items-center gap-4">
                    <div class="w-full sm:w-40">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Role</p>
                        <flux:select wire:change="updateRole({{ $user->id }}, $event.target.value)" size="sm">
                            <option value="staff" @selected($user->role == 'staff')>Staff</option>
                            <option value="manager" @selected($user->role == 'manager')>Manager</option>
                            <option value="admin" @selected($user->role == 'admin')>Admin</option>
                        </flux:select>
                    </div>

                    <div class="w-full sm:w-56">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Atasan Langsung</p>
                        <flux:select wire:change="updateParent({{ $user->id }}, $event.target.value)"
                            size="sm">
                            <option value="">(Tanpa Atasan)</option>
                            @foreach ($this->managers as $manager)
                                <option value="{{ $manager->id }}" @selected($user->parent_id == $manager->id)>
                                    {{ $manager->name }}
                                </option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>

                {{-- Badge Status --}}
                <div class="hidden md:flex flex-col items-end gap-1">
                    <span
                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $user->role == 'admin' ? 'bg-red-100 text-red-600' : ($user->role == 'manager' ? 'bg-indigo-100 text-indigo-600' : 'bg-emerald-100 text-emerald-600') }}">
                        {{ $user->role }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium">Gabung:
                        {{ $user->created_at->format('d/m/y') }}</span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination Sederhana --}}
    <div class="pt-6">
        {{ $this->users->links() }}
    </div>
</div>
