<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agenda extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi secara massal.
     */
    protected $fillable = [
        'user_id',
        'approver_id',
        'title',
        'description',
        'deadline',
        'status',
        'manager_note',
    ];

    /**
     * Casting tipe data kolom.
     * Penting agar 'deadline' otomatis menjadi objek Carbon (tanggal).
     */
    protected $casts = [
        'deadline' => 'datetime',
    ];

    /**
     * Relasi: Agenda ini milik siapa? (Staff)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Siapa yang harus menyetujui agenda ini? (Manager)
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Relasi: Daftar tahapan checklist dalam agenda ini.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(AgendaStep::class);
    }

    /**
     * Relasi: Catatan progres harian untuk agenda ini.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(AgendaLog::class);
    }
}
