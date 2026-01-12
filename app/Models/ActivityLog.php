<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'agenda_id', 'action', 'description', 'icon', 'color'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function agenda(): BelongsTo { return $this->belongsTo(Agenda::class); }
}
