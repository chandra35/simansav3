<?php

namespace App\Http\Controllers\Asrama;

use App\Http\Controllers\Controller;
use App\Models\AsramaAsatidz;
use App\Models\AsramaKelas;
use App\Models\AsramaKelasSantri;
use App\Models\AsramaRapor;
use App\Services\AsramaRaporService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RaporController extends Controller
{
    public function index(Request $request)
    {
        $classQuery = AsramaKelas::with(['asrama', 'tahunPelajaran'])->where('is_active', true);
        if (! $request->user()->can('manage-rapor-asrama') && ! $request->user()->can('manage-asrama')) {
            $asatidzIds = AsramaAsatidz::where('gtk_id', $request->user()->gtk?->id)
                ->where('is_active', true)->pluck('id');
            $classQuery->whereIn('wali_asatidz_id', $asatidzIds);
        }
        $classes = $classQuery->latest()->get();
        $kelasId = $request->input('kelas_id') ?: $classes->first()?->id;
        $semester = in_array($request->input('semester'), ['Ganjil', 'Genap'], true)
            ? $request->input('semester')
            : ($classes->firstWhere('id', $kelasId)?->tahunPelajaran?->semester_aktif ?? 'Ganjil');
        $members = $kelasId
            ? AsramaKelasSantri::with(['santri.siswa', 'rapor' => fn ($q) => $q->where('semester', $semester)])
                ->where('asrama_kelas_id', $kelasId)->where('status', 'aktif')
                ->get()->sortBy('santri.siswa.nama_lengkap')
            : collect();

        return view('asrama.rapor.index', compact('classes', 'kelasId', 'semester', 'members'));
    }

    public function edit(Request $request, AsramaKelasSantri $anggota, AsramaRaporService $service)
    {
        $semester = in_array($request->input('semester'), ['Ganjil', 'Genap'], true)
            ? $request->input('semester') : ($anggota->kelas->tahunPelajaran->semester_aktif ?? 'Ganjil');
        $this->authorizeMembership($request, $anggota, true);
        $rapor = AsramaRapor::firstOrCreate(
            ['asrama_kelas_santri_id' => $anggota->id, 'semester' => $semester],
            ['status' => 'draft']
        );
        $report = $rapor->status === 'terbit' && $rapor->snapshot
            ? $rapor->snapshot : $service->build($anggota, $semester);

        return view('asrama.rapor.edit', compact('anggota', 'semester', 'rapor', 'report'));
    }

    public function update(Request $request, AsramaKelasSantri $anggota)
    {
        $data = $request->validate([
            'semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
            'nilai_kebersihan' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'nilai_kelakuan' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'nilai_kerajinan' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'sakit' => ['required', 'integer', 'min:0', 'max:365'],
            'izin' => ['required', 'integer', 'min:0', 'max:365'],
            'lain_lain' => ['required', 'integer', 'min:0', 'max:365'],
            'predikat' => ['nullable', 'string', 'max:80'],
            'keputusan' => ['nullable', 'string', 'max:80'],
            'catatan_wali' => ['nullable', 'string', 'max:2000'],
            'tanggal_rapor' => ['nullable', 'date'],
            'tanggal_hijriah' => ['nullable', 'string', 'max:255'],
        ]);
        $this->authorizeMembership($request, $anggota, true);
        $rapor = AsramaRapor::firstOrCreate([
            'asrama_kelas_santri_id' => $anggota->id, 'semester' => $data['semester'],
        ]);
        abort_if($rapor->status === 'terbit', 422, 'Rapor sudah diterbitkan. Batalkan terbit sebelum mengubahnya.');
        $rapor->update($data);

        return back()->with('success', 'Komponen rapor berhasil disimpan.');
    }

    public function publish(Request $request, AsramaRapor $rapor, AsramaRaporService $service)
    {
        abort_unless($request->user()->can('publish-rapor-asrama'), 403);
        $rapor->load('kelasSantri');
        $snapshot = $service->publishSnapshot($rapor);
        $expected = $rapor->kelasSantri->kelas->pengampu()
            ->where('semester', $rapor->semester)
            ->where('is_active', true)
            ->count();
        abort_if($expected === 0, 422, 'Kelas belum memiliki penugasan mata pelajaran pada semester ini.');
        abort_if(
            count($snapshot['scores']) < $expected,
            422,
            'Nilai rapor belum lengkap: '.count($snapshot['scores']).' dari '.$expected.' mata pelajaran.'
        );
        $rapor->update([
            'snapshot' => $snapshot, 'status' => 'terbit',
            'published_by' => $request->user()->id, 'published_at' => now(),
        ]);

        return back()->with('success', 'Rapor berhasil diterbitkan dan dikunci.');
    }

    public function unpublish(Request $request, AsramaRapor $rapor)
    {
        abort_unless($request->user()->can('publish-rapor-asrama'), 403);
        $rapor->update([
            'status' => 'draft', 'snapshot' => null, 'published_by' => null, 'published_at' => null,
        ]);

        return back()->with('success', 'Penerbitan rapor dibatalkan. Nilai dapat diperbarui kembali.');
    }

    public function print(Request $request, AsramaRapor $rapor, AsramaRaporService $service)
    {
        $rapor->load('kelasSantri');
        $this->authorizeMembership($request, $rapor->kelasSantri, false);
        if ($request->user()->isSiswa()) {
            abort_unless($rapor->status === 'terbit', 403);
        }
        $report = $rapor->status === 'terbit' && $rapor->snapshot
            ? $rapor->snapshot : $service->build($rapor->kelasSantri, $rapor->semester);

        return view('asrama.rapor.print', compact('rapor', 'report', 'service'));
    }

    private function authorizeMembership(Request $request, AsramaKelasSantri $membership, bool $editing): void
    {
        $user = $request->user();
        if ($user->can('manage-asrama') || $user->can('manage-rapor-asrama')) {
            return;
        }
        if (! $editing && $user->siswa && $membership->santri->siswa_id === $user->siswa->id) {
            return;
        }
        if ($user->gtk) {
            $ids = AsramaAsatidz::where('gtk_id', $user->gtk->id)->where('is_active', true)->pluck('id');
            if ($ids->contains($membership->kelas->wali_asatidz_id)) {
                return;
            }
        }
        abort(403);
    }
}
