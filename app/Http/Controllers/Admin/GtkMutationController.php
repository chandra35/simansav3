<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use App\Models\MutasiGtk;
use App\Services\GtkStatusService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GtkMutationController extends Controller
{
    public const ACTIVE_REASONS = ['mutasi_masuk', 'aktif_kembali', 'lainnya'];

    public const INACTIVE_REASONS = ['pensiun', 'meninggal', 'mengundurkan_diri', 'mutasi_keluar', 'pemutusan_hubungan_kerja', 'kontrak_selesai', 'lainnya'];

    public function index(Request $request)
    {
        $this->authorize('view-mutasi-gtk');
        $history = MutasiGtk::query()->with(['gtk.user', 'creator'])
            ->when($request->filled('status'), fn ($q) => $q->where('status_baru', $request->status === 'aktif'))
            ->when($request->filled('alasan'), fn ($q) => $q->where('alasan', $request->alasan))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim($request->q);
                $q->whereHas('gtk', fn ($gtk) => $gtk->where('nama_lengkap', 'like', "%{$term}%")->orWhere('nip', 'like', "%{$term}%")->orWhere('nik', 'like', "%{$term}%"));
            })->latest('tanggal_efektif')->latest()->paginate(25)->withQueryString();

        return view('admin.gtk-mutation.index', [
            'history' => $history,
            'gtks' => Gtk::query()->with('user')->orderBy('nama_lengkap')->get(['id', 'user_id', 'nama_lengkap', 'nip', 'nik', 'jenis_ptk', 'foto_profile', 'status_aktif', 'alasan_nonaktif']),
            'stats' => ['aktif' => Gtk::where('status_aktif', true)->count(), 'nonaktif' => Gtk::where('status_aktif', false)->count(), 'pensiun' => Gtk::where('alasan_nonaktif', 'pensiun')->count(), 'mutasi' => Gtk::where('alasan_nonaktif', 'mutasi_keluar')->count()],
            'reasonLabels' => $this->reasonLabels(),
            'selectedGtkId' => $request->gtk_id,
        ]);
    }

    public function store(Request $request, GtkStatusService $service)
    {
        $this->authorize('manage-status-gtk');
        $newStatus = $request->boolean('status_baru');
        $allowedReasons = $newStatus ? self::ACTIVE_REASONS : self::INACTIVE_REASONS;
        $data = $request->validate([
            'gtk_id' => ['required', 'exists:gtks,id'],
            'status_baru' => ['required', 'boolean'],
            'alasan' => ['required', Rule::in($allowedReasons)],
            'tanggal_efektif' => ['required', 'date', 'before_or_equal:today'],
            'instansi_asal_tujuan' => ['nullable', 'string', 'max:255', Rule::requiredIf(in_array($request->alasan, ['mutasi_masuk', 'mutasi_keluar'], true))],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);
        $mutation = $service->change(Gtk::findOrFail($data['gtk_id']), $data);

        return redirect()->route('admin.mutasi-gtk.index')->with('success', 'Status '.$mutation->gtk->nama_lengkap.' berhasil diperbarui dan seluruh dampak operasional telah disinkronkan.');
    }

    private function reasonLabels(): array
    {
        return ['mutasi_masuk' => 'Mutasi masuk', 'aktif_kembali' => 'Aktif kembali', 'pensiun' => 'Pensiun', 'meninggal' => 'Meninggal dunia', 'mengundurkan_diri' => 'Mengundurkan diri', 'mutasi_keluar' => 'Mutasi keluar', 'pemutusan_hubungan_kerja' => 'Pemutusan hubungan kerja', 'kontrak_selesai' => 'Kontrak selesai', 'lainnya' => 'Lainnya'];
    }
}
