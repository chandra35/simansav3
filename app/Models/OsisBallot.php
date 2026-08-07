<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsisBallot extends Model
{
    use HasUuid;

    protected $fillable = ['election_id', 'voter_id', 'package_id', 'cast_at'];
    protected $casts = ['cast_at' => 'datetime'];
    public function election(): BelongsTo { return $this->belongsTo(OsisElection::class, 'election_id'); }
    public function package(): BelongsTo { return $this->belongsTo(OsisPackage::class, 'package_id'); }
}
