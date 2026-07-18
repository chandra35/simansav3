<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsisVoter extends Model
{
    use HasUuid;

    protected $fillable = ['election_id', 'user_id', 'siswa_id', 'participant_type', 'is_candidate', 'has_voted', 'voted_at', 'receipt_code'];
    protected $casts = ['is_candidate' => 'boolean', 'has_voted' => 'boolean', 'voted_at' => 'datetime'];
    public function election(): BelongsTo { return $this->belongsTo(OsisElection::class, 'election_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function siswa(): BelongsTo { return $this->belongsTo(Siswa::class); }
}
