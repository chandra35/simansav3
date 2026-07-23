<?php

namespace App\Http\Controllers;

use App\Models\OsisElection;
use App\Models\OsisPackage;
use App\Models\TahunPelajaran;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicOsisPollingController extends Controller
{
    public function index(): View
    {
        $election = $this->currentElection();

        return view('public.osis-polling', [
            'election' => $election,
            'initialPolling' => $election ? $this->snapshot($election) : null,
            'schoolLogo' => AppSetting::first()?->logo_sekolah_url
                ?? asset('vendor/adminlte/dist/img/logo-sekolah.png'),
        ]);
    }

    public function data(): JsonResponse
    {
        $election = $this->currentElection();

        if (! $election) {
            return response()->json([
                'active' => false,
                'message' => 'Belum ada live polling yang sedang dibuka.',
                'updated_at' => now()->format('H:i:s'),
            ]);
        }

        return response()->json(Cache::remember(
            "public-osis-polling:{$election->id}",
            now()->addSeconds(3),
            fn () => $this->snapshot($election)
        ));
    }

    private function currentElection(): ?OsisElection
    {
        $activeYearId = TahunPelajaran::active()->value('id');

        if (! $activeYearId) {
            return null;
        }

        return OsisElection::query()
            ->where('tahun_pelajaran_id', $activeYearId)
            ->where('status', 'published')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->latest('published_at')
            ->first();
    }

    private function snapshot(OsisElection $election): array
    {
        $voters = $election->voters()->count();
        $voted = $election->voters()->where('has_voted', true)->count();
        $votes = $election->ballots()->select('package_id', DB::raw('COUNT(*) as total'))
            ->groupBy('package_id')->pluck('total', 'package_id');

        $packages = $election->packages()
            ->with(['chairman.kelasSaatIni', 'secretary.kelasSaatIni', 'treasurer.kelasSaatIni'])
            ->get()
            ->map(fn (OsisPackage $package) => [
                'id' => $package->id,
                'number' => $package->number,
                'name' => $package->name ?: 'Paket '.$package->number,
                'slogan' => $package->slogan,
                'votes' => (int) ($votes[$package->id] ?? 0),
                'percentage' => $voted ? round(((int) ($votes[$package->id] ?? 0) / $voted) * 100, 1) : 0,
                'candidates' => collect([
                    ['role' => 'Ketua', 'student' => $package->chairman],
                    ['role' => 'Sekretaris', 'student' => $package->secretary],
                    ['role' => 'Bendahara', 'student' => $package->treasurer],
                ])->map(fn (array $candidate) => [
                    'role' => $candidate['role'],
                    'name' => $candidate['student']?->nama_lengkap ?: 'Belum ditentukan',
                    'class' => $candidate['student']?->kelasSaatIni?->nama_kelas ?: '-',
                    'photo' => $candidate['student']?->foto_profile_url,
                ])->values(),
            ])->values();

        return [
            'active' => true,
            'title' => $election->title,
            'theme' => $election->theme,
            'ends_at' => $election->ends_at->toIso8601String(),
            'voters' => $voters,
            'voted' => $voted,
            'remaining' => max($voters - $voted, 0),
            'turnout' => $voters ? round(($voted / $voters) * 100, 1) : 0,
            'packages' => $packages,
            'updated_at' => now()->format('H:i:s'),
        ];
    }
}
