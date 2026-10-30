<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use App\Models\TeacherJournal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;

class TeacherJournalController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-teacher-journal');
        [$year, $gtk, $waliClasses] = $this->context();
        [$dari, $sampai] = $this->period($request);
        $jenis = $request->string('jenis')->toString();
        abort_unless(in_array($jenis, ['', TeacherJournal::JENIS_MENGAJAR, TeacherJournal::JENIS_WALI_KELAS], true), 422);

        $journals = $this->journalQuery($request, $year, $dari, $sampai, $jenis)->paginate(15);
        $schedules = $gtk && $year ? JadwalPelajaran::query()->with(['kelas', 'mataPelajaran'])->aktif()
            ->where('tahun_pelajaran_id', $year->id)->where('gtk_id', $gtk->id)->orderBy('hari')->orderBy('jam_ke')->get() : collect();

        return view('admin.gtk.journals.index', compact('journals', 'schedules', 'waliClasses', 'jenis', 'dari', 'sampai'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-teacher-journal');
        [$year, $gtk, $waliClasses] = $this->context();
        abort_unless($year && $gtk, 422, 'Tahun pelajaran aktif atau profil GTK belum tersedia.');
        $data = $this->validatedData($request);
        abort_unless(Carbon::parse($data['tanggal'])->betweenIncluded($year->tanggal_mulai, $year->tanggal_selesai), 422, 'Tanggal jurnal harus berada dalam tahun pelajaran aktif.');
        [$schedule, $kelas] = $this->resolveJournalContext($data, $year, $gtk, $waliClasses);

        TeacherJournal::create(array_merge($data, [
            'tahun_pelajaran_id' => $year->id, 'user_id' => Auth::id(), 'gtk_id' => $gtk->id,
            'kelas_id' => $kelas?->id, 'jadwal_pelajaran_id' => $schedule?->id,
            'mapel_snapshot' => $schedule?->mataPelajaran?->nama_mapel,
            'kelas_snapshot' => $kelas?->nama_lengkap ?? $kelas?->nama_kelas,
        ]));

        return redirect()->route('admin.gtk.journals.index')->with('success', 'Jurnal berhasil disimpan.');
    }

    public function edit(TeacherJournal $journal)
    {
        $this->authorize('edit-own-teacher-journal');
        abort_unless($journal->user_id === Auth::id(), 403);
        [$year, $gtk, $waliClasses] = $this->context();
        $schedules = $gtk && $year ? JadwalPelajaran::query()->with(['kelas', 'mataPelajaran'])->aktif()
            ->where('tahun_pelajaran_id', $year->id)->where('gtk_id', $gtk->id)->orderBy('hari')->orderBy('jam_ke')->get() : collect();
        return view('admin.gtk.journals.edit', compact('journal', 'schedules', 'waliClasses'));
    }

    public function update(Request $request, TeacherJournal $journal)
    {
        $this->authorize('edit-own-teacher-journal');
        abort_unless($journal->user_id === Auth::id(), 403);
        [$year, $gtk, $waliClasses] = $this->context();
        abort_unless($year && $gtk, 422, 'Tahun pelajaran aktif atau profil GTK belum tersedia.');
        $data = $this->validatedData($request);
        abort_unless(Carbon::parse($data['tanggal'])->betweenIncluded($year->tanggal_mulai, $year->tanggal_selesai), 422, 'Tanggal jurnal harus berada dalam tahun pelajaran aktif.');
        [$schedule, $kelas] = $this->resolveJournalContext($data, $year, $gtk, $waliClasses);
        $journal->update(array_merge($data, ['kelas_id' => $kelas?->id, 'jadwal_pelajaran_id' => $schedule?->id, 'mapel_snapshot' => $schedule?->mataPelajaran?->nama_mapel, 'kelas_snapshot' => $kelas?->nama_lengkap ?? $kelas?->nama_kelas]));
        return redirect()->route('admin.gtk.journals.index')->with('success', 'Jurnal berhasil diperbarui.');
    }

    public function destroy(TeacherJournal $journal)
    {
        $this->authorize('delete-own-teacher-journal');
        abort_unless($journal->user_id === Auth::id(), 403);
        $journal->delete();
        return back()->with('success', 'Jurnal berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $this->authorize('export-teacher-journal');
        [$year] = $this->context();
        [$dari, $sampai] = $this->period($request);
        $jenis = $request->string('jenis')->toString();
        $journals = $this->journalQuery($request, $year, $dari, $sampai, $jenis)->get();
        return PDF::loadView('admin.gtk.journals.pdf', ['journals' => $journals, 'year' => $year, 'user' => Auth::user(), 'dari' => $dari, 'sampai' => $sampai])
            ->setPaper('a4', 'portrait')->download('jurnal-guru-'.$dari->format('Ymd').'-'.$sampai->format('Ymd').'.pdf');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'jenis' => ['required', 'in:mengajar,wali_kelas'], 'tanggal' => ['required', 'date'],
            'jadwal_pelajaran_id' => ['nullable', 'uuid'], 'kelas_id' => ['nullable', 'uuid'],
            'topik' => ['required', 'string', 'max:255'], 'kegiatan' => ['required', 'string', 'max:5000'],
            'asesmen_tindak_lanjut' => ['nullable', 'string', 'max:5000'], 'durasi_menit' => ['nullable', 'integer', 'min:1', 'max:720'],
            'catatan' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function resolveJournalContext(array $data, TahunPelajaran $year, Gtk $gtk, $waliClasses): array
    {
        if ($data['jenis'] === TeacherJournal::JENIS_MENGAJAR) {
            $schedule = filled($data['jadwal_pelajaran_id']) ? JadwalPelajaran::query()->with(['kelas', 'mataPelajaran'])->aktif()
                ->where('tahun_pelajaran_id', $year->id)->where('gtk_id', $gtk->id)->findOrFail($data['jadwal_pelajaran_id']) : null;
            return [$schedule, $schedule?->kelas];
        }
        abort_unless($waliClasses->isNotEmpty(), 403, 'Jurnal wali kelas hanya tersedia untuk wali kelas aktif.');
        $kelas = $waliClasses->firstWhere('id', $data['kelas_id']);
        abort_unless($kelas, 403, 'Kelas bukan rombel wali Anda.');
        return [null, $kelas];
    }

    private function context(): array
    {
        $year = TahunPelajaran::query()->active()->first();
        $gtk = Auth::user()->gtk;
        $waliClasses = $year ? Kelas::query()->where('tahun_pelajaran_id', $year->id)->where('wali_kelas_id', Auth::id())->where('is_active', true)->orderBy('tingkat')->orderBy('nama_kelas')->get() : collect();
        return [$year, $gtk, $waliClasses];
    }

    private function period(Request $request): array
    {
        $dari = Carbon::parse($request->input('dari', now()->startOfMonth()->toDateString()))->startOfDay();
        $sampai = Carbon::parse($request->input('sampai', now()->toDateString()))->endOfDay();
        abort_if($dari->gt($sampai) || $dari->diffInDays($sampai) > 366, 422, 'Periode jurnal tidak valid.');
        return [$dari, $sampai];
    }

    private function journalQuery(Request $request, ?TahunPelajaran $year, Carbon $dari, Carbon $sampai, string $jenis)
    {
        $query = TeacherJournal::query()->where('user_id', Auth::id())->with('kelas')->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])->latest('tanggal')->latest('created_at');
        if ($year) $query->where('tahun_pelajaran_id', $year->id);
        if ($jenis !== '') $query->where('jenis', $jenis);
        return $query;
    }
}
