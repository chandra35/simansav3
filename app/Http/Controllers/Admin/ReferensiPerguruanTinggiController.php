<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferensiPerguruanTinggi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReferensiPerguruanTinggiController extends Controller
{
    public function index()
    {
        $references = ReferensiPerguruanTinggi::orderBy('jenis')
            ->orderBy('nama')
            ->get();

        return view('admin.referensi-perguruan-tinggi.index', [
            'references' => $references,
            'jenisOptions' => ReferensiPerguruanTinggi::JENIS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:referensi_perguruan_tinggi,nama',
            'jenis' => ['required', Rule::in(ReferensiPerguruanTinggi::JENIS)],
            'sumber_referensi' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        ReferensiPerguruanTinggi::create([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.referensi-perguruan-tinggi.index')
            ->with('success', 'Referensi kampus berhasil ditambahkan.');
    }

    public function update(Request $request, ReferensiPerguruanTinggi $referensiPerguruanTinggi)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('referensi_perguruan_tinggi', 'nama')->ignore($referensiPerguruanTinggi->id)],
            'jenis' => ['required', Rule::in(ReferensiPerguruanTinggi::JENIS)],
            'sumber_referensi' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $referensiPerguruanTinggi->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.referensi-perguruan-tinggi.index')
            ->with('success', 'Referensi kampus berhasil diperbarui.');
    }

    public function destroy(ReferensiPerguruanTinggi $referensiPerguruanTinggi)
    {
        if ($referensiPerguruanTinggi->siswaLulusan()->exists()) {
            return redirect()->route('admin.referensi-perguruan-tinggi.index')
                ->with('error', 'Referensi kampus ini sudah dipakai siswa dan tidak bisa dihapus.');
        }

        $referensiPerguruanTinggi->delete();

        return redirect()->route('admin.referensi-perguruan-tinggi.index')
            ->with('success', 'Referensi kampus berhasil dihapus.');
    }
}
