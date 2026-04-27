<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerifikasiIjazahLog extends Model
{
    use HasUuid;

    protected $table = 'verifikasi_ijazah_logs';

    public $timestamps = true;
    const UPDATED_AT = null; // hanya created_at, tidak perlu updated_at

    protected $fillable = [
        'verifikasi_id',
        'user_id',
        'user_nama',
        'aksi',
        'status_lama',
        'status_baru',
        'keterangan',
    ];

    public function verifikasi(): BelongsTo
    {
        return $this->belongsTo(VerifikasiIjazah::class, 'verifikasi_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAksiLabelAttribute(): string
    {
        return match ($this->aksi) {
            'created'          => 'Verifikasi dibuat',
            'status_changed'   => 'Status diubah',
            'catatan_updated'  => 'Catatan diperbarui',
            'data_refreshed'   => 'Data EMIS diperbarui',
            default            => $this->aksi,
        };
    }
}
