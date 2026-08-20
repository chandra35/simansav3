<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('mata_pelajaran')
            ->select(['id', 'tingkat', 'semester'])
            ->orderBy('id')
            ->get()
            ->each(function (object $mapel): void {
                $updates = [];

                foreach (['tingkat', 'semester'] as $column) {
                    if ($mapel->{$column} === null) {
                        continue;
                    }

                    $values = json_decode($mapel->{$column}, true);
                    if (! is_array($values)) {
                        continue;
                    }

                    $normalized = collect($values)
                        ->filter(fn ($value) => is_numeric($value))
                        ->map(fn ($value) => (int) $value)
                        ->unique()
                        ->values()
                        ->all();

                    if ($values !== $normalized) {
                        $updates[$column] = json_encode($normalized);
                    }
                }

                if ($updates) {
                    DB::table('mata_pelajaran')->where('id', $mapel->id)->update($updates);
                }
            });
    }

    public function down(): void
    {
        // Normalisasi tipe angka aman dan tidak perlu dibalik.
    }
};
