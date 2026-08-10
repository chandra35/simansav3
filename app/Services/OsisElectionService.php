<?php

namespace App\Services;

use App\Models\OsisBallot;
use App\Models\OsisElection;
use App\Models\OsisPackage;
use App\Models\OsisVoter;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class OsisElectionService
{
    public function publish(OsisElection $election): OsisElection
    {
        if ($election->status !== 'draft') throw new RuntimeException('Pemilihan ini sudah dipublikasikan.');
        $election->load('packages');
        if ($election->packages->count() < 2) throw new RuntimeException('Minimal dua paket kandidat diperlukan sebelum publikasi.');
        $requiredFields = collect($election->candidateRoleDefinitions())->pluck('field');
        if ($election->packages->contains(fn (OsisPackage $package) => $requiredFields->contains(fn (string $field) => blank($package->{$field})))) {
            throw new RuntimeException('Setiap paket harus memiliki siswa untuk seluruh posisi kandidat yang dipilih.');
        }
        if ($election->ends_at->lte($election->starts_at)) throw new RuntimeException('Waktu selesai harus setelah waktu mulai.');
        if (OsisElection::query()->where('tahun_pelajaran_id', $election->tahun_pelajaran_id)
            ->where('id', '<>', $election->id)->whereIn('status', ['published', 'paused'])->exists()) {
            throw new RuntimeException('Masih ada pemilihan lain yang aktif atau terjadwal pada tahun pelajaran ini. Tutup pemilihan tersebut terlebih dahulu.');
        }

        $candidateIds = $election->packages->flatMap->candidateIds()->unique()->values();
        $studentUsers = $this->eligibleStudentUsers($election, $candidateIds);

        $gtkUsers = $election->include_gtk
            ? User::query()->where('is_active', true)->whereHas('gtk')->with('gtk:id,user_id')->get(['id'])
            : collect();
        $participants = $studentUsers->map(fn (User $user) => [
            'user_id' => $user->id,
            'siswa_id' => $user->siswa?->id,
            'participant_type' => 'student',
            'is_candidate' => $candidateIds->contains($user->siswa?->id),
        ])->concat($gtkUsers->map(fn (User $user) => [
            'user_id' => $user->id,
            'siswa_id' => null,
            'participant_type' => 'gtk',
            'is_candidate' => false,
        ]))->unique('user_id')->values();

        if ($participants->isEmpty()) throw new RuntimeException('Tidak ada siswa atau GTK yang memenuhi kriteria pemilih.');

        DB::transaction(function () use ($election, $participants) {
            $election->voters()->delete();
            $now = now();
            $rows = $participants->map(fn (array $participant) => [
                'id' => (string) Str::uuid(), 'election_id' => $election->id,
                'user_id' => $participant['user_id'], 'siswa_id' => $participant['siswa_id'],
                'participant_type' => $participant['participant_type'], 'is_candidate' => $participant['is_candidate'], 'has_voted' => false,
                'created_at' => $now, 'updated_at' => $now,
            ])->all();
            foreach (array_chunk($rows, 300) as $chunk) DB::table('osis_voters')->insert($chunk);
            $election->update(['status' => 'published', 'published_at' => $now]);
        });

        return $election->fresh();
    }

    /** Add newly eligible students without changing the existing frozen DPT or ballots. */
    public function syncStudentVoters(OsisElection $election): int
    {
        if (! in_array($election->status, ['published', 'paused'], true) || $election->phase === 'closed') {
            throw new RuntimeException('Update data siswa hanya tersedia sebelum pemilihan ditutup.');
        }

        return DB::transaction(function () use ($election) {
            $election = OsisElection::query()->lockForUpdate()->findOrFail($election->id);
            if (! in_array($election->status, ['published', 'paused'], true) || $election->phase === 'closed') {
                throw new RuntimeException('Update data siswa hanya tersedia sebelum pemilihan ditutup.');
            }

            $election->load('packages');
            $candidateIds = $election->packages->flatMap->candidateIds()->unique()->values();
            $existingUserIds = $election->voters()->pluck('user_id');
            $students = $this->eligibleStudentUsers($election, $candidateIds)
                ->reject(fn (User $user) => $existingUserIds->contains($user->id));

            if ($students->isEmpty()) return 0;

            $now = now();
            $rows = $students->map(fn (User $user) => [
                'id' => (string) Str::uuid(), 'election_id' => $election->id,
                'user_id' => $user->id, 'siswa_id' => $user->siswa?->id,
                'participant_type' => 'student', 'is_candidate' => $candidateIds->contains($user->siswa?->id),
                'has_voted' => false, 'created_at' => $now, 'updated_at' => $now,
            ])->all();

            $added = 0;
            foreach (array_chunk($rows, 300) as $chunk) $added += DB::table('osis_voters')->insertOrIgnore($chunk);

            return $added;
        });
    }

    public function vote(OsisElection $election, User $user, OsisPackage $package): string
    {
        if (! $election->is_open) throw new RuntimeException('Pemilihan belum dibuka atau sudah berakhir.');
        if ($package->election_id !== $election->id) throw new RuntimeException('Paket kandidat tidak valid.');
        return DB::transaction(function () use ($election, $user, $package) {
            $voter = OsisVoter::query()->where('election_id', $election->id)
                ->where('user_id', $user->id)->lockForUpdate()->first();
            if (! $voter) throw new RuntimeException('Anda tidak terdaftar sebagai pemilih pada periode ini.');
            if ($voter->has_voted) throw new RuntimeException('Hak suara Anda sudah digunakan dan tidak dapat diubah.');
            if ($voter->is_candidate && $election->candidate_voting_policy === 'not_allowed') {
                throw new RuntimeException('Kandidat tidak diizinkan memberikan suara pada pemilihan ini.');
            }
            if ($voter->is_candidate && in_array($user->siswa?->id, $package->candidateIds(), true)) {
                throw new RuntimeException('Kandidat tidak diperbolehkan memilih paketnya sendiri.');
            }

            OsisBallot::create(['election_id' => $election->id, 'voter_id' => $voter->id, 'package_id' => $package->id, 'cast_at' => now()]);
            do { $receipt = strtoupper(Str::random(10)); }
            while (OsisVoter::query()->where('receipt_code', $receipt)->exists());
            $voter->update(['has_voted' => true, 'voted_at' => now(), 'receipt_code' => $receipt]);

            return $receipt;
        });
    }

    public function unlockVote(OsisElection $election, OsisVoter $voter, User $admin): void
    {
        if (! in_array($election->status, ['published', 'paused'], true)) throw new RuntimeException('Unlock hanya tersedia saat pemilihan masih berlangsung.');
        DB::transaction(function () use ($election, $voter, $admin) {
            $voter = OsisVoter::query()->whereKey($voter->id)->lockForUpdate()->firstOrFail();
            if (! $voter->has_voted) throw new RuntimeException('Pemilih ini belum menggunakan hak suaranya.');
            $ballot = OsisBallot::query()->where('election_id', $election->id)->where('voter_id', $voter->id)->lockForUpdate()->first();
            if (! $ballot) throw new RuntimeException('Suara lama tidak dapat di-unlock karena dibuat sebelum fitur audit unlock tersedia.');
            $ballot->delete();
            $voter->update(['has_voted' => false, 'voted_at' => null, 'receipt_code' => null, 'unlocked_at' => now(), 'unlocked_by' => $admin->id]);
        });
    }

    public function close(OsisElection $election): OsisElection
    {
        if (! in_array($election->status, ['published', 'paused'], true)) throw new RuntimeException('Hanya pemilihan yang telah dipublikasikan yang dapat ditutup.');
        $election->update(['status' => 'closed', 'closed_at' => now()]);
        return $election->fresh();
    }

    public function pause(OsisElection $election): OsisElection
    {
        if ($election->status !== 'published') throw new RuntimeException('Hanya pemilihan yang sedang dipublikasikan yang dapat dijeda.');
        $election->update(['status' => 'paused', 'paused_at' => now()]);

        return $election->fresh();
    }

    public function resume(OsisElection $election): OsisElection
    {
        if ($election->status !== 'paused') throw new RuntimeException('Pemilihan ini tidak sedang dijeda.');
        if ($election->ends_at->lte(now())) throw new RuntimeException('Perpanjang waktu selesai sebelum melanjutkan pemilihan.');
        $election->update(['status' => 'published', 'paused_at' => null]);

        return $election->fresh();
    }

    public function publishResults(OsisElection $election): OsisElection
    {
        if ($election->phase !== 'closed') throw new RuntimeException('Hasil hanya dapat diumumkan setelah pemilihan ditutup.');
        $election->update(['status' => 'closed', 'closed_at' => $election->closed_at ?: now(), 'result_published_at' => now()]);
        return $election->fresh();
    }

    private function eligibleStudentUsers(OsisElection $election, $candidateIds)
    {
        $levels = collect($election->eligible_levels ?? [])->map(fn ($level) => (int) $level)->all();
        $students = $levels === [] ? collect() : User::query()
            ->where('is_active', true)
            ->whereHas('siswa', fn ($student) => $student
                ->where('status_siswa', 'aktif')
                ->whereHas('kelasSaatIni', fn ($class) => $class
                    ->where('tahun_pelajaran_id', $election->tahun_pelajaran_id)
                    ->whereIn('tingkat', $levels)))
            ->with('siswa:id,user_id')
            ->get(['id']);

        return $election->candidate_voting_policy === 'not_allowed'
            ? $students->reject(fn (User $user) => $candidateIds->contains($user->siswa?->id))
            : $students;
    }
}
