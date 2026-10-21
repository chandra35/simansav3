<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SholatRecord extends Model
{
    use HasUuids;

    protected $table = 'sholat_records';

    public const STATUS_BERJAMAAH = 'berjamaah';
    public const STATUS_MASBUQ = 'masbuq';
    public const STATUS_MUNFARID = 'munfarid';
    public const STATUS_UDZUR = 'udzur';
    public const STATUS_SAKIT = 'sakit';
    public const STATUS_IZIN = 'izin';
    public const STATUS_ALPA = 'alpa';

    protected $fillable = [
        'sholat_session_id',
        'siswa_id',
        'status',
        'catatan',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(SholatSession::class, 'sholat_session_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_BERJAMAAH => [
                'label' => 'Berjamaah',
                'short' => 'H',
                'badge' => 'badge-success',
                'color' => '#28a745',
            ],
            self::STATUS_MASBUQ => [
                'label' => 'Masbuq',
                'short' => 'M',
                'badge' => 'badge-info',
                'color' => '#17a2b8',
            ],
            self::STATUS_MUNFARID => [
                'label' => 'Munfarid',
                'short' => 'S',
                'badge' => 'badge-secondary',
                'color' => '#6c757d',
            ],
            self::STATUS_UDZUR => [
                'label' => 'Udzur Syar\'i',
                'short' => 'U',
                'badge' => 'badge-purple',
                'color' => '#6f42c1',
            ],
            self::STATUS_SAKIT => [
                'label' => 'Sakit',
                'short' => 'Skt',
                'badge' => 'badge-warning',
                'color' => '#ffc107',
            ],
            self::STATUS_IZIN => [
                'label' => 'Izin',
                'short' => 'Izn',
                'badge' => 'badge-primary',
                'color' => '#007bff',
            ],
            self::STATUS_ALPA => [
                'label' => 'Alpa / Tidak Sholat',
                'short' => 'A',
                'badge' => 'badge-danger',
                'color' => '#dc3545',
            ],
        ];
    }
}
