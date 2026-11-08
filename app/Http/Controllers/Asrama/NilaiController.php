<?php

namespace App\Http\Controllers\Asrama;

use App\Http\Controllers\Controller;
use App\Models\AsramaAsatidz;
use App\Models\AsramaKelas;
use App\Models\AsramaKomponenNilai;
use App\Models\AsramaNilai;
use App\Models\AsramaPengampu;
use App\Models\AsramaRapor;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NilaiController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = TahunPelajaran::active()->first();
        $defaultYearId = $activeYear?->id ?: TahunPelajaran::orderByDesc('tahun_mulai')->value('id');
        $tahunId = $request->input('tahun_pelajaran_id', $defaultYearId);
        $selectedSemester = $request->input('semester');
        $selectedKelasId = $request->input('asrama_kelas_id');

        $query = AsramaPengampu::with(['kelas.asrama', 'kelas.tahunPelajaran', 'mapel', 'asatidz.gtk'])
            ->where('is_active', true);

        if ($tahunId) {
            $query->whereHas('kelas', fn ($q) => $q->where('tahun_pelajaran_id', $tahunId));
        }

        if ($selectedSemester && in_array($selectedSemester, ['Ganjil', 'Genap'], true)) {
            $query->where('semester', $selectedSemester);
        }

        if ($selectedKelasId) {
            $query->where('asrama_kelas_id', $selectedKelasId);
        }

        if (! $request->user()->can('manage-asrama') && ! $request->user()->can('manage-asrama-pengampu')) {
            $gtkId = $request->user()->gtk?->id;
            $asatidzIds = AsramaAsatidz::where('gtk_id', $gtkId)->where('is_active', true)->pluck('id');
            $query->where(function ($scope) use ($asatidzIds) {
                $scope->whereIn('asrama_asatidz_id', $asatidzIds)
                    ->orWhereHas('kelas.pengasuhRombel', fn ($q) => $q->whereIn('asrama_asatidz_id', $asatidzIds));
            });
        }

        $years = TahunPelajaran::orderByDesc('tahun_mulai')->get();
        $filterClasses = AsramaKelas::when($tahunId, fn ($q) => $q->where('tahun_pelajaran_id', $tahunId))
            ->where('is_active', true)
            ->orderBy('nama_kelas')
            ->get();

        $components = AsramaKomponenNilai::when($tahunId, fn ($q) => $q->where('tahun_pelajaran_id', $tahunId))
            ->active()
            ->ordered()
            ->get();

        return view('asrama.nilai.index', [
            'assignments' => $query->latest()->get(),
            'years' => $years,
            'selectedYearId' => $tahunId,
            'selectedYear' => $years->firstWhere('id', $tahunId),
            'activeYear' => $activeYear,
            'selectedSemester' => $selectedSemester,
            'selectedKelasId' => $selectedKelasId,
            'filterClasses' => $filterClasses,
            'components' => $components,
            'komponenList' => $components,
            'totalBobot' => (float) $components->sum('bobot'),
        ]);
    }

    public function edit(Request $request, AsramaPengampu $pengampu)
    {
        $this->authorizeAssignment($request, $pengampu);
        $pengampu->load([
            'kelas.anggotaAktif.santri.siswa', 'kelas.tahunPelajaran', 'kelas.asrama',
            'mapel', 'asatidz.gtk', 'nilai',
        ]);
        $values = $pengampu->nilai->keyBy('asrama_kelas_santri_id');

        $components = AsramaKomponenNilai::where('tahun_pelajaran_id', $pengampu->kelas->tahun_pelajaran_id)
            ->active()
            ->ordered()
            ->get();

        $totalBobot = (float) $components->sum('bobot');

        $memberIds = $pengampu->kelas->anggotaAktif()->pluck('id');
        $isPublished = AsramaRapor::whereIn('asrama_kelas_santri_id', $memberIds)
            ->where('semester', $pengampu->semester)
            ->where('status', 'terbit')
            ->exists();

        return view('asrama.nilai.edit', [
            'pengampu' => $pengampu,
            'values' => $values,
            'components' => $components,
            'komponenList' => $components,
            'totalBobot' => $totalBobot,
            'isPublished' => $isPublished,
        ]);
    }

    public function update(Request $request, AsramaPengampu $pengampu)
    {
        $this->authorizeAssignment($request, $pengampu);

        $components = AsramaKomponenNilai::where('tahun_pelajaran_id', $pengampu->kelas->tahun_pelajaran_id)
            ->active()
            ->ordered()
            ->get();

        $rules = [
            'catatan' => ['nullable', 'array'],
            'catatan.*' => ['nullable', 'string', 'max:1000'],
        ];

        if ($components->isNotEmpty()) {
            $rules['detail_nilai'] = ['nullable', 'array'];
            $rules['detail_nilai.*'] = ['nullable', 'array'];
            $rules['komponen'] = ['nullable', 'array'];
            $rules['komponen.*'] = ['nullable', 'array'];
            foreach ($components as $k) {
                $rules['detail_nilai.*.'.$k->kode] = ['nullable', 'numeric', 'min:0', 'max:'.$pengampu->mapel->skala_maksimum];
                $rules['komponen.*.'.$k->kode] = ['nullable', 'numeric', 'min:0', 'max:'.$pengampu->mapel->skala_maksimum];
            }
            $rules['nilai'] = ['nullable', 'array'];
            $rules['nilai.*'] = ['nullable', 'numeric', 'min:0', 'max:'.$pengampu->mapel->skala_maksimum];
        } else {
            $rules['nilai'] = ['required', 'array'];
            $rules['nilai.*'] = ['nullable', 'numeric', 'min:0', 'max:'.$pengampu->mapel->skala_maksimum];
        }

        $data = $request->validate($rules);

        $memberIds = $pengampu->kelas->anggotaAktif()->pluck('id');
        $publishedMemberIds = AsramaRapor::whereIn('asrama_kelas_santri_id', $memberIds)
            ->where('semester', $pengampu->semester)->where('status', 'terbit')
            ->pluck('asrama_kelas_santri_id')->all();

        DB::transaction(function () use ($data, $request, $pengampu, $memberIds, $publishedMemberIds, $components) {
            foreach ($memberIds as $memberId) {
                if (in_array($memberId, $publishedMemberIds, true)) {
                    continue;
                }

                $detailNilai = null;
                $finalScore = null;

                if ($components->isNotEmpty()) {
                    $rawKomponen = $data['detail_nilai'][$memberId] ?? $data['komponen'][$memberId] ?? [];
                    $hasAnyScore = false;
                    $weightedSum = 0;
                    $totalWeight = 0;
                    $componentsDetail = [];

                    foreach ($components as $k) {
                        $compVal = $rawKomponen[$k->kode] ?? null;
                        if ($compVal !== null && $compVal !== '') {
                            $hasAnyScore = true;
                            $scoreFloat = (float) $compVal;
                            $weight = (float) $k->bobot;
                            $componentsDetail[$k->kode] = $scoreFloat;
                            $weightedSum += ($scoreFloat * $weight);
                            $totalWeight += $weight;
                        }
                    }

                    if ($hasAnyScore) {
                        $detailNilai = $componentsDetail;
                        $finalScore = $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 0;
                    }
                } else {
                    $score = $data['nilai'][$memberId] ?? null;
                    if ($score !== null && $score !== '') {
                        $finalScore = (float) $score;
                    }
                }

                $catatan = $data['catatan'][$memberId] ?? null;

                if ($finalScore === null && empty($catatan)) {
                    AsramaNilai::where([
                        'asrama_pengampu_id' => $pengampu->id,
                        'asrama_kelas_santri_id' => $memberId,
                    ])->delete();

                    continue;
                }

                $nilai = AsramaNilai::withTrashed()->firstOrNew([
                    'asrama_pengampu_id' => $pengampu->id,
                    'asrama_kelas_santri_id' => $memberId,
                ]);

                if ($nilai->trashed()) {
                    $nilai->restore();
                }

                $nilai->fill([
                    'nilai' => $finalScore,
                    'detail_nilai' => $detailNilai,
                    'catatan' => $catatan,
                    'input_by' => $request->user()->id,
                    'input_at' => now(),
                ])->save();
            }
        });

        return back()->with('success', 'Nilai asrama berhasil disimpan.')
            ->with('warning', $publishedMemberIds ? count($publishedMemberIds).' rapor terbit tidak diubah.' : null);
    }

    private function authorizeAssignment(Request $request, AsramaPengampu $pengampu): void
    {
        if ($request->user()->can('manage-asrama') || $request->user()->can('manage-asrama-pengampu') || $request->user()->can('manage-nilai-asrama')) {
            return;
        }
        $gtkId = $request->user()->gtk?->id;
        $asatidzIds = AsramaAsatidz::where('gtk_id', $gtkId)->where('is_active', true)->pluck('id');
        abort_unless(
            $asatidzIds->contains($pengampu->asrama_asatidz_id)
            || $pengampu->kelas->pengasuhRombel()->whereIn('asrama_asatidz_id', $asatidzIds)->exists(),
            403
        );
    }
}
