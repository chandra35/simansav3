<?php

namespace App\Http\Controllers\Asrama;

use App\Http\Controllers\Controller;
use App\Models\AsramaAsatidz;
use App\Models\AsramaNilai;
use App\Models\AsramaPengampu;
use App\Models\AsramaRapor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NilaiController extends Controller
{
    public function index(Request $request)
    {
        $query = AsramaPengampu::with(['kelas.asrama', 'kelas.tahunPelajaran', 'mapel', 'asatidz.gtk'])
            ->where('is_active', true);
        if (! $request->user()->can('manage-asrama') && ! $request->user()->can('manage-asrama-pengampu')) {
            $gtkId = $request->user()->gtk?->id;
            $asatidzIds = AsramaAsatidz::where('gtk_id', $gtkId)->where('is_active', true)->pluck('id');
            $query->where(function ($scope) use ($asatidzIds) {
                $scope->whereIn('asrama_asatidz_id', $asatidzIds)
                    ->orWhereHas('kelas.pengasuhRombel', fn ($q) => $q->whereIn('asrama_asatidz_id', $asatidzIds));
            });
        }

        return view('asrama.nilai.index', ['assignments' => $query->latest()->get()]);
    }

    public function edit(Request $request, AsramaPengampu $pengampu)
    {
        $this->authorizeAssignment($request, $pengampu);
        $pengampu->load([
            'kelas.anggotaAktif.santri.siswa', 'kelas.tahunPelajaran', 'kelas.asrama',
            'mapel', 'asatidz.gtk', 'nilai',
        ]);
        $values = $pengampu->nilai->keyBy('asrama_kelas_santri_id');

        return view('asrama.nilai.edit', compact('pengampu', 'values'));
    }

    public function update(Request $request, AsramaPengampu $pengampu)
    {
        $this->authorizeAssignment($request, $pengampu);
        $data = $request->validate([
            'nilai' => ['required', 'array'],
            'nilai.*' => ['nullable', 'numeric', 'min:0', 'max:'.$pengampu->mapel->skala_maksimum],
            'catatan' => ['nullable', 'array'],
            'catatan.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $memberIds = $pengampu->kelas->anggotaAktif()->pluck('id');
        $publishedMemberIds = AsramaRapor::whereIn('asrama_kelas_santri_id', $memberIds)
            ->where('semester', $pengampu->semester)->where('status', 'terbit')
            ->pluck('asrama_kelas_santri_id')->all();

        DB::transaction(function () use ($data, $request, $pengampu, $memberIds, $publishedMemberIds) {
            foreach ($data['nilai'] as $memberId => $score) {
                if (! $memberIds->contains($memberId) || in_array($memberId, $publishedMemberIds, true)) {
                    continue;
                }
                if ($score === null || $score === '') {
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
                    'nilai' => $score,
                    'catatan' => $data['catatan'][$memberId] ?? null,
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
        if ($request->user()->can('manage-asrama') || $request->user()->can('manage-asrama-pengampu')) {
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
