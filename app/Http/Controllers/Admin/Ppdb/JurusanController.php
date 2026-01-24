<?php

namespace App\Http\Controllers\Admin\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\JurusanPpdb;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    /**
     * Display a listing of jurusan.
     */
    public function index()
    {
        $jurusan = JurusanPpdb::ordered()->get();
        return view('admin.ppdb.jurusan.index', compact('jurusan'));
    }

    /**
     * Show the form for creating a new jurusan.
     */
    public function create()
    {
        return view('admin.ppdb.jurusan.create');
    }

    /**
     * Store a newly created jurusan.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:20|unique:jurusan_ppdb,kode',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kuota' => 'required|integer|min:0',
            'urutan' => 'nullable|integer|min:0',
        ]);

        JurusanPpdb::create([
            'kode' => strtoupper($request->kode),
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'kuota' => $request->kuota,
            'urutan' => $request->urutan ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('admin.ppdb.jurusan.index')
            ->with('success', 'Jurusan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified jurusan.
     */
    public function edit(JurusanPpdb $jurusan)
    {
        return view('admin.ppdb.jurusan.edit', compact('jurusan'));
    }

    /**
     * Update the specified jurusan.
     */
    public function update(Request $request, JurusanPpdb $jurusan)
    {
        $request->validate([
            'kode' => 'required|string|max:20|unique:jurusan_ppdb,kode,' . $jurusan->id,
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kuota' => 'required|integer|min:0',
            'urutan' => 'nullable|integer|min:0',
        ]);

        $jurusan->update([
            'kode' => strtoupper($request->kode),
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'kuota' => $request->kuota,
            'urutan' => $request->urutan ?? 0,
        ]);

        return redirect()->route('admin.ppdb.jurusan.index')
            ->with('success', 'Jurusan berhasil diperbarui.');
    }

    /**
     * Toggle jurusan status.
     */
    public function toggleStatus(JurusanPpdb $jurusan)
    {
        $jurusan->update(['is_active' => !$jurusan->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $jurusan->is_active,
        ]);
    }

    /**
     * Remove the specified jurusan.
     */
    public function destroy(JurusanPpdb $jurusan)
    {
        if ($jurusan->terisi > 0) {
            return back()->with('error', 'Jurusan tidak dapat dihapus karena sudah ada siswa yang terdaftar.');
        }

        $jurusan->delete();

        return redirect()->route('admin.ppdb.jurusan.index')
            ->with('success', 'Jurusan berhasil dihapus.');
    }
}
