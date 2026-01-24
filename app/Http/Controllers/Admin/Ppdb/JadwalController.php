<?php

namespace App\Http\Controllers\Admin\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\JadwalPpdb;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    /**
     * Display a listing of jadwal.
     */
    public function index()
    {
        $jadwals = JadwalPpdb::ordered()->get();
        return view('admin.ppdb.jadwal.index', compact('jadwals'));
    }

    /**
     * Show the form for creating a new jadwal.
     */
    public function create()
    {
        return view('admin.ppdb.jadwal.create');
    }

    /**
     * Store a newly created jadwal.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'warna' => 'required|string|max:7',
            'icon' => 'nullable|string|max:100',
            'urutan' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['icon'] = $request->input('icon', 'fas fa-calendar');

        JadwalPpdb::create($data);

        // Log activity
        activity()
            ->causedBy(auth()->user())
            ->withProperties(['nama_kegiatan' => $request->nama_kegiatan])
            ->log('Created jadwal PPDB');

        return redirect()->route('admin.ppdb.jadwal.index')
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified jadwal.
     */
    public function edit(JadwalPpdb $jadwal)
    {
        return view('admin.ppdb.jadwal.edit', compact('jadwal'));
    }

    /**
     * Update the specified jadwal.
     */
    public function update(Request $request, JadwalPpdb $jadwal)
    {
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'warna' => 'required|string|max:7',
            'icon' => 'nullable|string|max:100',
            'urutan' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active', true);

        $jadwal->update($data);

        // Log activity
        activity()
            ->performedOn($jadwal)
            ->causedBy(auth()->user())
            ->log('Updated jadwal PPDB');

        return redirect()->route('admin.ppdb.jadwal.index')
            ->with('success', 'Jadwal berhasil diupdate.');
    }

    /**
     * Remove the specified jadwal.
     */
    public function destroy(JadwalPpdb $jadwal)
    {
        $jadwal->delete();

        // Log activity
        activity()
            ->causedBy(auth()->user())
            ->withProperties(['nama_kegiatan' => $jadwal->nama_kegiatan])
            ->log('Deleted jadwal PPDB');

        return redirect()->route('admin.ppdb.jadwal.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * Toggle jadwal status.
     */
    public function toggleStatus(JadwalPpdb $jadwal)
    {
        $jadwal->update(['is_active' => !$jadwal->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diubah.',
            'is_active' => $jadwal->is_active,
        ]);
    }

    /**
     * Update jadwal order.
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|uuid|exists:jadwal_ppdb,id',
            'orders.*.urutan' => 'required|integer|min:0',
        ]);

        foreach ($request->orders as $order) {
            JadwalPpdb::where('id', $order['id'])->update(['urutan' => $order['urutan']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Urutan berhasil diupdate.',
        ]);
    }
}
