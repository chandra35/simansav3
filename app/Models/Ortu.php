<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use App\Traits\HasActivityLog;
use App\Traits\HasCreatedUpdatedBy;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class Ortu extends Model
{
    use HasUuid, HasActivityLog, HasCreatedUpdatedBy, SoftDeletes;

    protected $table = 'ortu';

    protected $fillable = [
        'siswa_id',
        'no_kk',
        'status_ayah',
        'nik_ayah',
        'nama_ayah',
        'pekerjaan_ayah',
        'penghasilan_ayah',
        'hp_ayah',
        'status_ibu',
        'nik_ibu',
        'nama_ibu',
        'pekerjaan_ibu',
        'penghasilan_ibu',
        'hp_ibu',
        'alamat_ortu',
        'rt_ortu',
        'rw_ortu',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'kelurahan_id',
        'kodepos',
        'created_by',
        'updated_by',
    ];

    // Relations
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function provinsi()
    {
        return $this->belongsTo(Province::class, 'provinsi_id', 'code');
    }

    public function kabupaten()
    {
        return $this->belongsTo(City::class, 'kabupaten_id', 'code');
    }

    public function kecamatan()
    {
        return $this->belongsTo(District::class, 'kecamatan_id', 'code');
    }

    public function kelurahan()
    {
        return $this->belongsTo(Village::class, 'kelurahan_id', 'code');
    }

    // Helper methods
    public function getAlamatLengkap()
    {
        $alamat = $this->alamat_ortu;
        if ($this->rt_ortu) {
            $alamat .= ', RT ' . $this->rt_ortu;
        }
        if ($this->rw_ortu) {
            $alamat .= '/RW ' . $this->rw_ortu;
        }
        if ($this->kelurahan) {
            $alamat .= ', ' . $this->kelurahan->name;
        }
        if ($this->kecamatan) {
            $alamat .= ', ' . $this->kecamatan->name;
        }
        if ($this->kabupaten) {
            $alamat .= ', ' . $this->kabupaten->name;
        }
        if ($this->provinsi) {
            $alamat .= ', ' . $this->provinsi->name;
        }
        if ($this->kodepos) {
            $alamat .= ' ' . $this->kodepos;
        }
        
        return $alamat;
    }

    public function isAyahHidup()
    {
        return $this->status_ayah === 'masih_hidup';
    }

    public function isIbuHidup()
    {
        return $this->status_ibu === 'masih_hidup';
    }
}
