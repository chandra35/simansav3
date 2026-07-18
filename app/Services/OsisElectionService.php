<?php

namespace App\Services;

use App\Models\OsisBallot;
use App\Models\OsisElection;
use App\Models\OsisPackage;
use App\Models\OsisVoter;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class OsisElectionService
{
    public function publish(OsisElection $election): OsisElection
    {
        if ($election->status !== 'draft') throw new RuntimeException('Pemilihan ini sudah dipublikasikan.');
        $election->load('packages');
        if ($election->packages->count() < 2) throw new RuntimeException('Minimal dua paket kandidat diperlukan sebelum publikasi.');
        if ($election->ends_at->lte($election->starts_at)) throw new RuntimeException('Waktu selesai harus setelah waktu mulai.');
        if (OsisElection::query()->where('tahun_pelajaran_id', $election->tahun_pelajaran_id)
            ->where('id', '<>', $election->id)->where('status', 'published')->exists()) {
            throw new RuntimeException('Masih ada pemilihan lain yang aktif atau terjadwal pada tahun pelajaran ini. Tutup pemilihan tersebut terlebih dahulu.');
        }

        $candidateIds = $election->packages->flatMap->candidateIds()->unique()->values();
        $levels = collect($election->eligible_levels ?: [10, 11, 12])->map(fn ($level) => (int) $level)->all();
        $students = Siswa::query()
            ->where('status_siswa', 'aktif')
            ->whereHas('kelasSaatIni', fn ($query) => $query
                ->where('tahun_pelajaran_id', $election->tahun_pelajaran_id)
                ->whereIn('tingkat', $levels))
            ->get(['id']);

        if ($election->candidate_voting_policy === 'not_allowed') {
            $students = $students->whereNotIn('id', $candidateIds);
        }
        if ($students->isEmpty()) throw new RuntimeException('Tidak ada siswa yang memenuhi kriteria pemilih.');

        DB::transaction(function () use ($election, $students, $candidateIds) {
            $election->voters()->delete();
            $now = now();
            $rows = $students->map(fn ($student) => [
                'id' => (string) Str::uuid(), 'election_id' => $election->id, 'siswa_id' => $student->id,
                'is_candidate' => $candidateIds->contains($student->id), 'has_voted' => false,
                'created_at' => $now, 'updated_at' => $now,
            ])->all();
            foreach (array_chunk($rows, 300) as $chunk) DB::table('osis_voters')->insert($chunk);
            $election->update(['status' => 'published', 'published_at' => $now]);
        });

        return $election->fresh();
    }

    public function vote(OsisElection $election, Siswa $siswa, OsisPackage $package, string $password): string
    {
        if (! $election->is_open) throw new RuntimeException('Pemilihan belum dibuka atau sudah berakhir.');
        if ($package->election_id !== $election->id) throw new RuntimeException('Paket kandidat tidak valid.');
        if (! $siswa->user || ! Hash::check($password, $siswa->user->password)) {
            throw new RuntimeException('Password akun tidak sesuai. Suara belum disimpan.');
        }

        return DB::transaction(function () use ($election, $siswa, $package) {
            $voter = OsisVoter::query()->where('election_id', $election->id)
                ->where('siswa_id', $siswa->id)->lockForUpdate()->first();
            if (! $voter) throw new RuntimeException('Anda tidak terdaftar sebagai pemilih pada periode ini.');
            if ($voter->has_voted) throw new RuntimeException('Hak suara Anda sudah digunakan dan tidak dapat diubah.');
            if ($voter->is_candidate && $election->candidate_voting_policy === 'not_allowed') {
                throw new RuntimeException('Kandidat tidak diizinkan memberikan suara pada pemilihan ini.');
            }
            if ($voter->is_candidate && in_array($siswa->id, $package->candidateIds(), true)) {
                throw new RuntimeException('Kandidat tidak diperbolehkan memilih paketnya sendiri.');
            }

            OsisBallot::create(['election_id' => $election->id, 'package_id' => $package->id, 'cast_at' => now()]);
            do { $receipt = strtoupper(Str::random(10)); }
            while (OsisVoter::query()->where('receipt_code', $receipt)->exists());
            $voter->update(['has_voted' => true, 'voted_at' => now(), 'receipt_code' => $receipt]);

            return $receipt;
        });
    }

    public function close(OsisElection $election): OsisElection
    {
        if ($election->status !== 'published') throw new RuntimeException('Hanya pemilihan yang telah dipublikasikan yang dapat ditutup.');
        $election->update(['status' => 'closed', 'closed_at' => now()]);
        return $election->fresh();
    }

    public function publishResults(OsisElection $election): OsisElection
    {
        if ($election->phase !== 'closed') throw new RuntimeException('Hasil hanya dapat diumumkan setelah pemilihan ditutup.');
        $election->update(['status' => 'closed', 'closed_at' => $election->closed_at ?: now(), 'result_published_at' => now()]);
        return $election->fresh();
    }
}
