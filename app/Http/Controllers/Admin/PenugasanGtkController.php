<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use App\Models\JenisPenugasanGtk;
use App\Models\Kelas;
use App\Models\PenugasanGtk;
use App\Models\TahunPelajaran;
use App\Models\TugasTambahan;
use App\Services\GtkWorkloadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PenugasanGtkController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-penugasan-gtk');
        $years = TahunPelajaran::query()->orderByDesc('tahun_mulai')->get();
        $year = $request->filled('tahun_pelajaran_id')
            ? $years->firstWhere('id', $request->tahun_pelajaran_id)
            : $years->firstWhere('is_active', true);
        $year ??= $years->first();
        $statusFilter = in_array($request->input('status'), ['all', 'active', 'ended', 'draft', 'cancelled'], true)
            ? $request->input('status')
            : 'active';

        $query = PenugasanGtk::query()->with(['gtk.user', 'jenis', 'tahunPelajaran'])
            ->when($year, fn ($q) => $q->where('tahun_pelajaran_id', $year->id))
            ->when($statusFilter !== 'all', fn ($q) => $q->where('status', $statusFilter))
            ->when($request->filled('jenis_penugasan_id'), fn ($q) => $q->where('jenis_penugasan_id', $request->jenis_penugasan_id))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim($request->q);
                $q->where(fn ($search) => $search
                    ->whereHas('gtk', fn ($gtk) => $gtk->where('nama_lengkap', 'like', "%{$term}%")->orWhere('nip', 'like', "%{$term}%"))
                    ->orWhere('unit_nama', 'like', "%{$term}%"));
            })
            ->latest('mulai_tugas');

        $assignments = $query->paginate(25)->withQueryString();
        $types = JenisPenugasanGtk::query()->with('role')->orderBy('kategori')->orderBy('nama')->get();
        $gtks = Gtk::query()->with('user')
            ->eligibleForAssignment()
            ->orderBy('nama_lengkap')->get([
                'id', 'user_id', 'nama_lengkap', 'nip', 'jenis_ptk', 'kategori_ptk',
                'jenis_kelamin', 'foto_profile',
            ]);

        $base = PenugasanGtk::query()->when($year, fn ($q) => $q->where('tahun_pelajaran_id', $year->id));
        $stats = [
            'aktif' => (clone $base)->where('status', 'active')->count(),
            'gtk' => (clone $base)->where('status', 'active')->distinct('gtk_id')->count('gtk_id'),
            'utama' => (clone $base)->where('status', 'active')->whereHas('jenis', fn ($q) => $q->whereIn('kategori', ['utama', 'penuh']))->count(),
            'jtm' => (clone $base)->where('status', 'active')->sum('ekuivalensi_jtm'),
        ];
        $occupiedLeadershipTypeIds = (clone $base)->where('status', 'active')
            ->whereHas('jenis', fn ($q) => $q->whereIn('kelompok', ['kepala_madrasah', 'waka']))
            ->pluck('jenis_penugasan_id');
        $vacantLeadershipTypes = $types->where('is_active', true)
            ->whereIn('kelompok', ['kepala_madrasah', 'waka'])
            ->filter(fn ($type) => (int) $type->maks_pemegang > 0)
            ->whereNotIn('id', $occupiedLeadershipTypeIds->all())
            ->values();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.penugasan-gtk.partials.assignment-table', compact('assignments', 'statusFilter'))->render(),
            ]);
        }

        return view('admin.penugasan-gtk.index', compact(
            'assignments', 'types', 'gtks', 'years', 'year', 'stats', 'statusFilter', 'vacantLeadershipTypes'
        ));
    }

    public function workload(Request $request, GtkWorkloadService $service)
    {
        $this->authorize('view-beban-kerja-gtk');
        $years = TahunPelajaran::query()->orderByDesc('tahun_mulai')->get();
        $year = $request->filled('tahun_pelajaran_id')
            ? $years->firstWhere('id', $request->tahun_pelajaran_id)
            : $years->firstWhere('is_active', true);
        $year ??= $years->first();
        $semester = (int) ($request->semester ?: ($year?->semester_aktif === 'Genap' ? 2 : 1));
        $gtk = $request->filled('gtk_id') ? Gtk::findOrFail($request->gtk_id) : null;
        $search = trim((string) $request->q);
        $rows = $year ? $service->summarize($year, $semester, $gtk?->id, $search ?: null) : collect();
        $stats = [
            'gtk' => $rows->count(),
            'memenuhi' => $rows->where('status', 'memenuhi')->count(),
            'kurang' => $rows->where('status', 'kurang')->count(),
            'review' => $rows->whereIn('status', ['review', 'lebih'])->count(),
        ];

        return view('admin.penugasan-gtk.workload', compact('rows', 'stats', 'years', 'year', 'semester', 'gtk', 'search'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-penugasan-gtk');
        $data = $this->validatedAssignment($request);

        DB::transaction(function () use ($data) {
            $type = JenisPenugasanGtk::query()->lockForUpdate()->findOrFail($data['jenis_penugasan_id']);
            $type->load('role');
            $gtk = Gtk::query()->with('user')->lockForUpdate()->findOrFail($data['gtk_id']);
            $this->guardAssignmentRules($data, $type, $gtk);
            $data['ekuivalensi_jtm'] = $type->ekuivalensi_jtm;
            $data['status'] = 'active';
            $data['role_diberikan_otomatis'] = false;
            if ($type->role && $gtk->user && ! $gtk->user->hasRole($type->role)) {
                $gtk->user->assignRole($type->role);
                $data['role_diberikan_otomatis'] = true;
            }
            PenugasanGtk::create($data);
        });

        return back()->with('success', 'Penugasan GTK berhasil diaktifkan dan ekuivalensi jam telah dicatat.');
    }

    public function update(Request $request, PenugasanGtk $penugasanGtk)
    {
        $this->authorize('edit-penugasan-gtk');
        if ($penugasanGtk->status !== 'active') {
            return back()->with('error', 'Hanya penugasan aktif yang dapat diperbarui.');
        }

        $data = $this->validatedAssignment($request, $penugasanGtk);

        DB::transaction(function () use ($data, $penugasanGtk) {
            $penugasanGtk = PenugasanGtk::query()->lockForUpdate()->findOrFail($penugasanGtk->id);
            $type = JenisPenugasanGtk::query()->lockForUpdate()->findOrFail($data['jenis_penugasan_id']);
            $type->load('role');
            $gtk = Gtk::query()->with('user')->lockForUpdate()->findOrFail($data['gtk_id']);
            $this->guardAssignmentRules($data, $type, $gtk, $penugasanGtk);
            $data['ekuivalensi_jtm'] = $penugasanGtk->ekuivalensi_jtm;
            if ($type->role && $gtk->user && ! $gtk->user->hasRole($type->role)) {
                $gtk->user->assignRole($type->role);
                $data['role_diberikan_otomatis'] = true;
            }
            $penugasanGtk->update($data);
        });

        return back()->with('success', 'Penugasan GTK berhasil diperbarui tanpa mengubah histori ekuivalensi.');
    }

    public function end(Request $request, PenugasanGtk $penugasanGtk)
    {
        $this->authorize('end-penugasan-gtk');
        $data = $request->validate([
            'alasan' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($penugasanGtk, $data) {
            $penugasanGtk->load(['jenis.role', 'gtk.user']);
            $penugasanGtk->update([
                'status' => 'ended',
                'selesai_tugas' => today()->lt($penugasanGtk->mulai_tugas) ? $penugasanGtk->mulai_tugas : today(),
                'keterangan' => trim(($penugasanGtk->keterangan ? $penugasanGtk->keterangan."\n" : '').'Diakhiri: '.$data['alasan']),
            ]);
            $this->removeAutomaticallyGrantedRole($penugasanGtk);
        });

        return back()->with('success', 'Penugasan telah diakhiri dan historinya tetap tersimpan.');
    }

    public function destroy(PenugasanGtk $penugasanGtk)
    {
        $this->authorize('delete-penugasan-gtk');
        if ($penugasanGtk->status === 'active') {
            return back()->with('error', 'Akhiri penugasan aktif terlebih dahulu agar histori tidak terputus.');
        }
        $penugasanGtk->delete();

        return back()->with('success', 'Penugasan nonaktif dipindahkan ke arsip.');
    }

    public function types()
    {
        $this->authorize('manage-jenis-penugasan-gtk');
        $types = JenisPenugasanGtk::query()->withCount('assignments')->orderBy('kategori')->orderBy('nama')->get();

        return view('admin.penugasan-gtk.types', compact('types'));
    }

    public function storeType(Request $request)
    {
        $this->authorize('manage-jenis-penugasan-gtk');
        JenisPenugasanGtk::create($this->validatedType($request));

        return back()->with('success', 'Jenis penugasan baru berhasil ditambahkan.');
    }

    public function updateType(Request $request, JenisPenugasanGtk $jenisPenugasanGtk)
    {
        $this->authorize('manage-jenis-penugasan-gtk');
        $jenisPenugasanGtk->update($this->validatedType($request, $jenisPenugasanGtk));

        return back()->with('success', 'Standar penugasan diperbarui untuk penugasan berikutnya; histori lama tidak berubah.');
    }

    private function validatedAssignment(Request $request, ?PenugasanGtk $assignment = null): array
    {
        $type = $request->filled('jenis_penugasan_id') ? JenisPenugasanGtk::find($request->jenis_penugasan_id) : null;
        $data = $request->validate([
            'gtk_id' => ['required', 'exists:gtks,id'],
            'jenis_penugasan_id' => ['required', 'exists:jenis_penugasan_gtk,id', ...($assignment ? [Rule::in([$assignment->jenis_penugasan_id])] : [])],
            'tahun_pelajaran_id' => ['required', 'exists:tahun_pelajaran,id', ...($assignment ? [Rule::in([$assignment->tahun_pelajaran_id])] : [])],
            'semester' => ['nullable', 'integer', Rule::in([1, 2])],
            'unit_nama' => [$type?->jenis_unit ? 'required' : 'nullable', 'nullable', 'string', 'max:150'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);
        if ($assignment && $data['gtk_id'] !== $assignment->gtk_id) {
            throw ValidationException::withMessages(['gtk_id' => 'GTK pada histori penugasan tidak dapat diganti. Akhiri penugasan lama dan buat penugasan baru.']);
        }
        $data['semester'] = $data['semester'] ?? null;
        $year = TahunPelajaran::findOrFail($data['tahun_pelajaran_id']);
        $data['mulai_tugas'] = $assignment?->mulai_tugas
            ?? $year->tanggal_mulai
            ?? now()->setDate((int) $year->tahun_mulai, 7, 1)->startOfDay();
        $data['selesai_tugas'] = $assignment?->selesai_tugas;

        return $data;
    }

    private function guardAssignmentRules(array $data, JenisPenugasanGtk $type, Gtk $gtk, ?PenugasanGtk $except = null): void
    {
        $latestMutation = $gtk->mutasi()->reorder()->latest('created_at')->latest('id')->first();
        if (! $gtk->status_aktif || ! $gtk->user || ! $gtk->user->is_active || $latestMutation?->status_baru === false) {
            throw ValidationException::withMessages(['gtk_id' => 'Penugasan hanya dapat diberikan kepada guru dengan akun aktif.']);
        }

        $active = PenugasanGtk::query()->with('jenis')->where('status', 'active')
            ->where('tahun_pelajaran_id', $data['tahun_pelajaran_id'])
            ->where('gtk_id', $gtk->id)
            ->when($except, fn ($q) => $q->whereKeyNot($except->id))
            ->when(
                $data['semester'] ?? null,
                fn ($q, $semester) => $q->where(fn ($period) => $period->whereNull('semester')->orWhere('semester', $semester))
            );

        if ((clone $active)->where('jenis_penugasan_id', $type->id)->exists()) {
            throw ValidationException::withMessages(['jenis_penugasan_id' => 'Guru sudah memiliki jenis penugasan ini pada periode yang sama.']);
        }

        $existing = $active->get();
        if (in_array($type->kategori, ['utama', 'penuh'], true) && $existing->isNotEmpty()) {
            throw ValidationException::withMessages(['jenis_penugasan_id' => 'Tugas tambahan utama tidak dapat dirangkap dengan penugasan aktif lain pada periode yang sama.']);
        }
        if ($type->kategori === 'lain' && $existing->contains(fn ($item) => in_array($item->jenis?->kategori, ['utama', 'penuh'], true))) {
            throw ValidationException::withMessages(['jenis_penugasan_id' => 'Guru yang memegang tugas tambahan utama tidak dapat memperoleh ekuivalensi tugas tambahan lain.']);
        }

        $periodQuery = PenugasanGtk::query()->where('status', 'active')
            ->where('tahun_pelajaran_id', $data['tahun_pelajaran_id'])
            ->when($except, fn ($q) => $q->whereKeyNot($except->id));
        $holderLimit = $type->maks_pemegang ?: ($type->kelompok === 'waka' ? 1 : null);
        if ($holderLimit) {
            $holders = (clone $periodQuery)->where('jenis_penugasan_id', $type->id)->count();
            if ($holders >= $holderLimit) {
                throw ValidationException::withMessages(['jenis_penugasan_id' => "Jabatan {$type->nama} sudah dipegang GTK lain pada periode ini."]);
            }
        }

        if ($type->kelompok === 'waka') {
            $rombel = Kelas::query()->where('tahun_pelajaran_id', $data['tahun_pelajaran_id'])->where('is_active', true)->count();
            $limit = $rombel <= 3 ? 1 : ($rombel <= 6 ? 2 : ($rombel <= 9 ? 3 : 4));
            $holders = (clone $periodQuery)->whereHas('jenis', fn ($q) => $q->where('kelompok', 'waka'))->count();
            if ($holders >= $limit) {
                throw ValidationException::withMessages(['jenis_penugasan_id' => "Jumlah Waka telah mencapai batas {$limit} orang untuk {$rombel} rombel."]);
            }
        }

        if ($type->jenis_unit && ! empty($data['unit_nama'])) {
            $duplicateUnit = (clone $periodQuery)->where('jenis_penugasan_id', $type->id)
                ->whereRaw('LOWER(unit_nama) = ?', [mb_strtolower(trim($data['unit_nama']))])->exists();
            if ($duplicateUnit) {
                throw ValidationException::withMessages(['unit_nama' => 'Unit tersebut sudah memiliki penanggung jawab aktif pada periode yang sama.']);
            }
        }
    }

    private function removeAutomaticallyGrantedRole(PenugasanGtk $assignment): void
    {
        if (! $assignment->role_diberikan_otomatis || ! $assignment->jenis?->role || ! $assignment->gtk?->user) {
            return;
        }
        $hasOtherNew = PenugasanGtk::query()->where('status', 'active')->where('gtk_id', $assignment->gtk_id)
            ->whereKeyNot($assignment->id)->whereHas('jenis', fn ($q) => $q->where('role_id', $assignment->jenis->role_id))->exists();
        $hasLegacy = TugasTambahan::query()->where('user_id', $assignment->gtk->user_id)->where('role_id', $assignment->jenis->role_id)->where('is_active', true)->exists();
        if (! $hasOtherNew && ! $hasLegacy) {
            $assignment->gtk->user->removeRole($assignment->jenis->role);
        }
    }

    private function validatedType(Request $request, ?JenisPenugasanGtk $type = null): array
    {
        return $request->validate([
            'kode' => ['required', 'alpha_dash', 'max:80', Rule::unique('jenis_penugasan_gtk', 'kode')->ignore($type?->id)],
            'nama' => ['required', 'string', 'max:150'],
            'kelompok' => ['nullable', 'string', 'max:80'],
            'kategori' => ['required', Rule::in(['penuh', 'utama', 'lain'])],
            'ekuivalensi_jtm' => ['required', 'integer', 'min:0', 'max:40'],
            'minimal_jtm_mengajar' => ['required', 'integer', 'min:0', 'max:40'],
            'jenis_unit' => ['nullable', 'string', 'max:40'],
            'maks_pemegang' => ['nullable', 'integer', 'min:1', 'max:99'],
            'dapat_dirangkap' => ['nullable', 'boolean'],
            'dasar_hukum' => ['nullable', 'string', 'max:255'],
            'berlaku_mulai' => ['nullable', 'date'],
            'berlaku_selesai' => ['nullable', 'date', 'after_or_equal:berlaku_mulai'],
            'is_active' => ['nullable', 'boolean'],
        ]) + [
            'wajib_sk' => false,
            'dapat_dirangkap' => false,
            'is_active' => false,
        ];
    }
}
