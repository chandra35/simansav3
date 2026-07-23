<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalGuruAlias extends Model
{
    use HasUuids;

    protected $fillable = [
        'tahun_pelajaran_id',
        'source',
        'external_code',
        'external_name',
        'normalized_name',
        'gtk_id',
        'confidence',
        'match_method',
        'status',
        'context',
        'notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'confidence' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function gtk(): BelongsTo
    {
        return $this->belongsTo(Gtk::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
