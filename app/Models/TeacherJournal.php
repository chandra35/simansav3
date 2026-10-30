<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TeacherJournal extends Model
{
    use HasUuids;

    public const JENIS_MENGAJAR = 'mengajar';
    public const JENIS_WALI_KELAS = 'wali_kelas';

    protected $fillable = [
        'tahun_pelajaran_id', 'user_id', 'gtk_id', 'kelas_id', 'jadwal_pelajaran_id',
        'jenis', 'tanggal', 'mapel_snapshot', 'kelas_snapshot', 'topik', 'kegiatan',
        'asesmen_tindak_lanjut', 'durasi_menit', 'catatan',
    ];

    protected $casts = ['tanggal' => 'date'];

    public function tahunPelajaran() { return $this->belongsTo(TahunPelajaran::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function gtk() { return $this->belongsTo(Gtk::class); }
    public function kelas() { return $this->belongsTo(Kelas::class); }
    public function jadwalPelajaran() { return $this->belongsTo(JadwalPelajaran::class); }
}
