<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MutasiGtk extends Model
{
    use HasUuids;

    protected $table = 'mutasi_gtk';

    protected $fillable = [
        'gtk_id', 'status_sebelumnya', 'status_baru', 'alasan', 'tanggal_efektif',
        'instansi_asal_tujuan', 'keterangan', 'dampak_operasional', 'created_by',
    ];

    protected $casts = [
        'status_sebelumnya' => 'boolean',
        'status_baru' => 'boolean',
        'tanggal_efektif' => 'date',
        'dampak_operasional' => 'array',
    ];

    public function gtk()
    {
        return $this->belongsTo(Gtk::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
