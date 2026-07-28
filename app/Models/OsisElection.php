<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OsisElection extends Model
{
    use HasUuid;

    public const CANDIDATE_ROLE_DEFINITIONS = [
        'chairman' => ['field' => 'chairman_id', 'relation' => 'chairman', 'label' => 'Ketua', 'icon' => 'fa-crown'],
        'vice_chairman' => ['field' => 'vice_chairman_id', 'relation' => 'viceChairman', 'label' => 'Wakil Ketua', 'icon' => 'fa-user-shield'],
        'secretary' => ['field' => 'secretary_id', 'relation' => 'secretary', 'label' => 'Sekretaris', 'icon' => 'fa-file-signature'],
        'treasurer' => ['field' => 'treasurer_id', 'relation' => 'treasurer', 'label' => 'Bendahara', 'icon' => 'fa-wallet'],
    ];

    protected $fillable = [
        'tahun_pelajaran_id', 'title', 'slug', 'theme', 'description', 'instructions',
        'candidate_roles', 'eligible_levels', 'include_gtk', 'candidate_voting_policy', 'status', 'starts_at', 'ends_at',
        'published_at', 'paused_at', 'closed_at', 'result_published_at', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'eligible_levels' => 'array',
        'candidate_roles' => 'array',
        'include_gtk' => 'boolean',
        'starts_at' => 'datetime', 'ends_at' => 'datetime', 'published_at' => 'datetime',
        'paused_at' => 'datetime', 'closed_at' => 'datetime', 'result_published_at' => 'datetime',
    ];

    public function tahunPelajaran(): BelongsTo { return $this->belongsTo(TahunPelajaran::class); }
    public function packages(): HasMany { return $this->hasMany(OsisPackage::class, 'election_id')->orderBy('number'); }
    public function voters(): HasMany { return $this->hasMany(OsisVoter::class, 'election_id'); }
    public function ballots(): HasMany { return $this->hasMany(OsisBallot::class, 'election_id'); }

    public function getPhaseAttribute(): string
    {
        if ($this->status === 'draft') return 'draft';
        if ($this->status === 'paused') return 'paused';
        if ($this->status === 'closed' || now()->greaterThan($this->ends_at)) return 'closed';
        if (now()->lt($this->starts_at)) return 'scheduled';
        return 'open';
    }

    public function getIsOpenAttribute(): bool
    {
        return $this->status === 'published' && now()->betweenIncluded($this->starts_at, $this->ends_at);
    }

    public function getResultsVisibleAttribute(): bool
    {
        return $this->result_published_at !== null;
    }

    public function candidateRoleKeys(): array
    {
        $roles = array_values(array_intersect(
            $this->candidate_roles ?: ['chairman', 'vice_chairman'],
            array_keys(self::CANDIDATE_ROLE_DEFINITIONS)
        ));

        return $roles === [] ? ['chairman', 'vice_chairman'] : $roles;
    }

    public function candidateRoleDefinitions(): array
    {
        return collect($this->candidateRoleKeys())
            ->mapWithKeys(fn (string $key) => [$key => self::CANDIDATE_ROLE_DEFINITIONS[$key]])
            ->all();
    }
}
