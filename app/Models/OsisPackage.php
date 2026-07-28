<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OsisPackage extends Model
{
    use HasUuid;

    protected $fillable = [
        'election_id', 'number', 'name', 'slogan', 'vision', 'mission', 'programs', 'message',
        'chairman_id', 'vice_chairman_id', 'secretary_id', 'treasurer_id',
    ];

    public function election(): BelongsTo { return $this->belongsTo(OsisElection::class, 'election_id'); }
    public function chairman(): BelongsTo { return $this->belongsTo(Siswa::class, 'chairman_id'); }
    public function viceChairman(): BelongsTo { return $this->belongsTo(Siswa::class, 'vice_chairman_id'); }
    public function secretary(): BelongsTo { return $this->belongsTo(Siswa::class, 'secretary_id'); }
    public function treasurer(): BelongsTo { return $this->belongsTo(Siswa::class, 'treasurer_id'); }
    public function ballots(): HasMany { return $this->hasMany(OsisBallot::class, 'package_id'); }

    public function candidateIds(): array
    {
        return array_values(array_filter([
            $this->chairman_id,
            $this->vice_chairman_id,
            $this->secretary_id,
            $this->treasurer_id,
        ]));
    }

    public function candidateAssignments(): array
    {
        return collect($this->election->candidateRoleDefinitions())
            ->map(function (array $definition, string $key) {
                return [
                    'key' => $key,
                    'label' => $definition['label'],
                    'field' => $definition['field'],
                    'student' => $this->{$definition['relation']},
                ];
            })
            ->filter(fn (array $assignment) => $assignment['student'] !== null)
            ->values()
            ->all();
    }
}
