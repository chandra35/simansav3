<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KalenderAkademik;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KalenderAkademikController extends Controller
{
    public function index()
    {
        $tahunPelajaran = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $kategori = KalenderAkademik::KATEGORI;

        return view('admin.kalender-akademik.index', compact('tahunPelajaran', 'kategori'));
    }

    public function getEvents(Request $request)
    {
        $start = $request->input('start');
        $end = $request->input('end');
        $tahunPelajaranId = $request->input('tahun_pelajaran_id');

        $query = KalenderAkademik::query()
            ->when($tahunPelajaranId, function ($q) use ($tahunPelajaranId) {
                return $q->where('tahun_pelajaran_id', $tahunPelajaranId);
            })
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('tanggal_mulai', [$start, $end])
                    ->orWhereBetween('tanggal_selesai', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('tanggal_mulai', '<=', $start)
                            ->where('tanggal_selesai', '>=', $end);
                    });
            });

        $events = $query->get()->map(function ($event) {
            return $event->toCalendarEvent();
        });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'nama_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:akademik,libur,kegiatan,ujian,rapat,lainnya',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'waktu_mulai' => 'nullable|date_format:H:i',
            'waktu_selesai' => 'nullable|date_format:H:i',
            'warna' => 'nullable|string|max:7',
            'is_all_day' => 'boolean',
            'is_libur' => 'boolean',
            'is_recurring' => 'boolean',
            'recurring_type' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurring_until' => 'nullable|date|after:tanggal_mulai',
        ]);

        $validated['is_all_day'] = $request->boolean('is_all_day');
        $validated['is_libur'] = $request->boolean('is_libur');
        $validated['is_recurring'] = $request->boolean('is_recurring');
        $validated['created_by'] = Auth::id();

        $event = KalenderAkademik::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kegiatan berhasil ditambahkan',
            'event' => $event->toCalendarEvent(),
        ]);
    }

    public function show(KalenderAkademik $kalenderAkademik)
    {
        return response()->json([
            'success' => true,
            'data' => $kalenderAkademik->load('tahunPelajaran'),
        ]);
    }

    public function update(Request $request, KalenderAkademik $kalenderAkademik)
    {
        $validated = $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'nama_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:akademik,libur,kegiatan,ujian,rapat,lainnya',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'waktu_mulai' => 'nullable|date_format:H:i',
            'waktu_selesai' => 'nullable|date_format:H:i',
            'warna' => 'nullable|string|max:7',
            'is_all_day' => 'boolean',
            'is_libur' => 'boolean',
            'is_recurring' => 'boolean',
            'recurring_type' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurring_until' => 'nullable|date|after:tanggal_mulai',
        ]);

        $validated['is_all_day'] = $request->boolean('is_all_day');
        $validated['is_libur'] = $request->boolean('is_libur');
        $validated['is_recurring'] = $request->boolean('is_recurring');

        $kalenderAkademik->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kegiatan berhasil diperbarui',
            'event' => $kalenderAkademik->toCalendarEvent(),
        ]);
    }

    public function updateDates(Request $request, KalenderAkademik $kalenderAkademik)
    {
        $validated = $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $kalenderAkademik->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tanggal kegiatan berhasil diperbarui',
        ]);
    }

    public function destroy(KalenderAkademik $kalenderAkademik)
    {
        $kalenderAkademik->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kegiatan berhasil dihapus',
        ]);
    }
}
