<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class BukuTamuQrToken extends Model
{
    use HasUuid;

    protected $table = 'buku_tamu_qr_tokens';

    protected $fillable = ['token', 'created_by', 'revoked_at'];

    protected $casts = ['revoked_at' => 'datetime'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }

    public function getRouteKeyName()
    {
        return 'token';
    }
}
