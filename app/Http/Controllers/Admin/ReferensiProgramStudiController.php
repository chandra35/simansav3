<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferensiPerguruanTinggi;
use App\Models\ReferensiProgramStudi;
use Illuminate\Http\Request;

class ReferensiProgramStudiController extends Controller
{
    public function index(Request $request)
    {
        $campusId = $request->get('referensi_perguruan_tinggi_id');

        $campuses = ReferensiPerguruanTinggi::query()
            ->orderBy('jenis')
            ->orderBy('nama')
            ->get(['id', 'nama', 'jenis']);

        $studyPrograms = ReferensiProgramStudi::query()
            ->with('perguruanTinggi:id,nama')
            ->when($campusId, fn ($query) => $query->where('referensi_perguruan_tinggi_id', $campusId))
            ->orderBy('nama')
            ->orderBy('jenjang')
            ->paginate(50)
            ->withQueryString();

        $viewData = [
            'campuses' => $campuses,
            'selectedCampusId' => $campusId,
            'studyPrograms' => $studyPrograms,
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.referensi-program-studi.partials.table', $viewData)->render(),
            ]);
        }

        return view('admin.referensi-program-studi.index', $viewData);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'referensi_perguruan_tinggi_id' => 'required|exists:referensi_perguruan_tinggi,id',
            'nama' => 'required|string|max:255',
            'jenjang' => 'nullable|string|max:50',
            'fakultas' => 'nullable|string|max:255',
            'sumber_referensi' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $exists = ReferensiProgramStudi::query()
            ->where('referensi_perguruan_tinggi_id', $validated['referensi_perguruan_tinggi_id'])
            ->where('nama', $validated['nama'])
            ->where('jenjang', $validated['jenjang'] ?? null)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'nama' => 'Program studi untuk kampus dan jenjang tersebut sudah ada.',
                ]);
        }

        ReferensiProgramStudi::create([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.referensi-program-studi.index', [
            'referensi_perguruan_tinggi_id' => $validated['referensi_perguruan_tinggi_id'],
        ])->with('success', 'Referensi program studi berhasil ditambahkan.');
    }

    public function update(Request $request, ReferensiProgramStudi $referensiProgramStudi)
    {
        $validated = $request->validate([
            'referensi_perguruan_tinggi_id' => 'required|exists:referensi_perguruan_tinggi,id',
            'nama' => 'required|string|max:255',
            'jenjang' => 'nullable|string|max:50',
            'fakultas' => 'nullable|string|max:255',
            'sumber_referensi' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $exists = ReferensiProgramStudi::query()
            ->where('referensi_perguruan_tinggi_id', $validated['referensi_perguruan_tinggi_id'])
            ->where('nama', $validated['nama'])
            ->where('jenjang', $validated['jenjang'] ?? null)
            ->whereKeyNot($referensiProgramStudi->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors([
                    'nama' => 'Program studi untuk kampus dan jenjang tersebut sudah ada.',
                ]);
        }

        $referensiProgramStudi->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.referensi-program-studi.index', [
            'referensi_perguruan_tinggi_id' => $validated['referensi_perguruan_tinggi_id'],
        ])->with('success', 'Referensi program studi berhasil diperbarui.');
    }

    public function destroy(ReferensiProgramStudi $referensiProgramStudi)
    {
        $campusId = $referensiProgramStudi->referensi_perguruan_tinggi_id;

        if ($referensiProgramStudi->siswaLulusan()->exists()) {
            return redirect()->route('admin.referensi-program-studi.index', [
                'referensi_perguruan_tinggi_id' => $campusId,
            ])->with('error', 'Referensi program studi ini sudah dipakai siswa dan tidak bisa dihapus.');
        }

        $referensiProgramStudi->delete();

        return redirect()->route('admin.referensi-program-studi.index', [
            'referensi_perguruan_tinggi_id' => $campusId,
        ])->with('success', 'Referensi program studi berhasil dihapus.');
    }
}
