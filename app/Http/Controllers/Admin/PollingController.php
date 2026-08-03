<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PollingReportExport;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Gtk;
use App\Models\Kelas;
use App\Models\Polling;
use App\Models\TahunPelajaran;
use App\Services\PollingReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class PollingController extends Controller
{
    public function __construct(private PollingReportService $reports) {}

    public function index()
    {
        abort_unless(auth()->user()->can('view-polling-results') || auth()->user()->can('manage-polling'), 403);

        $pollings = Polling::query()->with('sourcePolling:id,title')->withCount('responses')->latest()->paginate(15);
        $summary = [
            'total' => Polling::count(),
            'open' => Polling::where('status', 'published')->where('starts_at', '<=', now())->where('ends_at', '>=', now())->count(),
            'scheduled' => Polling::where('status', 'published')->where('starts_at', '>', now())->count(),
            'closed' => Polling::where(fn ($query) => $query->where('status', 'closed')->orWhere('ends_at', '<', now()))->count(),
        ];

        return view('admin.polling.index', compact('pollings', 'summary'));
    }

    public function create()
    {
        $this->authorizeManage();
        return view('admin.polling.form', $this->formOptions());
    }

    public function store(Request $request)
    {
        $this->authorizeManage();
        $data = $this->validatePayload($request);
        $polling = DB::transaction(fn () => $this->persist(new Polling(), $data, $request));
        $this->log($polling, 'create_polling', 'Membuat polling '.$polling->title.'.');

        return redirect()->route('admin.polling.show', $polling)->with('success', 'Polling berhasil dibuat.');
    }

    public function duplicate(Polling $polling)
    {
        $this->authorizeManage();
        $polling->load(['questions.options', 'targets']);

        $template = $polling->replicate();
        $template->setRelations($polling->getRelations());
        $template->title = 'Salinan '.$polling->title;
        $template->status = 'draft';
        $template->starts_at = now()->addHour();
        $template->ends_at = now()->addDays(7);
        $template->published_at = null;
        $template->source_polling_id = $polling->id;

        return view('admin.polling.form', array_merge($this->formOptions(), [
            'polling' => $template,
            'sourcePolling' => $polling,
        ]));
    }

    public function show(Polling $polling)
    {
        abort_unless(auth()->user()->can('view-polling-results') || auth()->user()->can('manage-polling'), 403);
        $polling->load('sourcePolling:id,title');
        $report = $this->reports->build($polling);
        return view('admin.polling.show', compact('polling', 'report'));
    }

    public function edit(Polling $polling)
    {
        $this->authorizeManage();
        if ($polling->responses()->exists()) {
            return redirect()->route('admin.polling.show', $polling)
                ->with('warning', 'Struktur polling dikunci karena sudah memiliki respons. Tutup polling jika diperlukan.');
        }
        $polling->load(['questions.options', 'targets']);
        return view('admin.polling.form', array_merge($this->formOptions(), compact('polling')));
    }

    public function update(Request $request, Polling $polling)
    {
        $this->authorizeManage();
        abort_if($polling->responses()->exists(), 422, 'Polling yang sudah memiliki respons tidak dapat diubah.');
        $data = $this->validatePayload($request);
        DB::transaction(fn () => $this->persist($polling, $data, $request));
        $this->log($polling, 'update_polling', 'Memperbarui polling '.$polling->title.'.');

        return redirect()->route('admin.polling.show', $polling)->with('success', 'Polling berhasil diperbarui.');
    }

    public function publish(Polling $polling)
    {
        $this->authorizeManage();
        abort_if($polling->questions()->doesntExist() || $polling->targets()->doesntExist(), 422, 'Polling belum memiliki pertanyaan atau target.');
        $polling->update(['status' => 'published', 'published_at' => now(), 'updated_by' => auth()->id()]);
        $this->log($polling, 'publish_polling', 'Menerbitkan polling '.$polling->title.'.');
        return back()->with('success', 'Polling diterbitkan dan akan muncul sesuai jadwal.');
    }

    public function close(Polling $polling)
    {
        $this->authorizeManage();
        $polling->update(['status' => 'closed', 'updated_by' => auth()->id()]);
        $this->log($polling, 'close_polling', 'Menutup polling '.$polling->title.'.');
        return back()->with('success', 'Polling telah ditutup.');
    }

    public function destroy(Polling $polling)
    {
        $this->authorizeManage();
        $polling->update(['status' => 'closed', 'updated_by' => auth()->id()]);
        $this->log($polling, 'archive_polling', 'Mengarsipkan polling '.$polling->title.' tanpa menghapus riwayat.');
        return redirect()->route('admin.polling.index')->with('success', 'Polling diarsipkan dan tetap tersedia sebagai riwayat/preset.');
    }

    public function export(Polling $polling)
    {
        abort_unless(auth()->user()->can('view-polling-results') || auth()->user()->can('manage-polling'), 403);
        $report = $this->reports->build($polling);
        return Excel::download(
            new PollingReportExport($polling, $report['rows']),
            'hasil-polling-'.$polling->slug.'.xlsx'
        );
    }

    public function pdf(Polling $polling)
    {
        abort_unless(auth()->user()->can('view-polling-results') || auth()->user()->can('manage-polling'), 403);
        $report = $this->reports->build($polling);
        return Pdf::loadView('admin.polling.pdf', compact('polling', 'report'))
            ->setPaper('a4', 'landscape')
            ->download('hasil-polling-'.$polling->slug.'.pdf');
    }

    private function validatePayload(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
            'audience' => ['required', 'in:siswa,gtk,both'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'allow_changes' => ['nullable', 'boolean'],
            'show_results_after_submit' => ['nullable', 'boolean'],
            'require_consent' => ['nullable', 'boolean'],
            'consent_text' => ['nullable', 'string', 'max:2000'],
            'reminder_interval_hours' => ['required', 'integer', 'min:1', 'max:168'],
            'action' => ['required', 'in:draft,publish'],
            'source_polling_id' => ['nullable', 'uuid', 'exists:pollings,id'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.prompt' => ['required', 'string', 'max:2000'],
            'questions.*.type' => ['required', 'in:single,multiple,short_text,long_text,yes_no'],
            'questions.*.is_required' => ['nullable', 'boolean'],
            'questions.*.options_text' => ['nullable', 'string', 'max:10000'],
            'questions.*.min_selections' => ['nullable', 'integer', 'min:1', 'max:100'],
            'questions.*.max_selections' => ['nullable', 'integer', 'min:1', 'max:100'],
            'student_all' => ['nullable', 'boolean'],
            'student_grades' => ['nullable', 'array'],
            'student_grades.*' => ['in:10,11,12'],
            'student_classes' => ['nullable', 'array'],
            'student_classes.*' => ['uuid', 'exists:kelas,id'],
            'gtk_all' => ['nullable', 'boolean'],
            'gtk_categories' => ['nullable', 'array'],
            'gtk_categories.*' => ['in:Pendidik,Tenaga Kependidikan'],
            'gtks' => ['nullable', 'array'],
            'gtks.*' => ['uuid', 'exists:gtks,id'],
        ]);

        if ($request->boolean('require_consent') && blank($data['consent_text'] ?? null)) {
            throw ValidationException::withMessages(['consent_text' => 'Teks persetujuan wajib diisi.']);
        }

        foreach ($data['questions'] as $index => $question) {
            if (in_array($question['type'], ['single', 'multiple'], true)) {
                $options = $this->optionLines($question['options_text'] ?? '');
                if (count($options) < 2) {
                    throw ValidationException::withMessages([
                        "questions.{$index}.options_text" => 'Pertanyaan pilihan memerlukan minimal dua opsi.',
                    ]);
                }
            }
            if ($question['type'] === 'multiple'
                && filled($question['min_selections'] ?? null)
                && filled($question['max_selections'] ?? null)
                && (int) $question['min_selections'] > (int) $question['max_selections']) {
                throw ValidationException::withMessages([
                    "questions.{$index}.max_selections" => 'Maksimum pilihan tidak boleh lebih kecil dari minimum.',
                ]);
            }
        }

        $needsStudents = in_array($data['audience'], ['siswa', 'both'], true);
        $needsGtk = in_array($data['audience'], ['gtk', 'both'], true);
        if ($needsStudents && ! $request->boolean('student_all') && empty($data['student_grades']) && empty($data['student_classes'])) {
            throw ValidationException::withMessages(['student_grades' => 'Pilih semua siswa, tingkat, atau rombel target.']);
        }
        if ($needsGtk && ! $request->boolean('gtk_all') && empty($data['gtk_categories']) && empty($data['gtks'])) {
            throw ValidationException::withMessages(['gtk_categories' => 'Pilih semua GTK, kategori Guru/Staf, atau GTK tertentu.']);
        }

        return $data;
    }

    private function persist(Polling $polling, array $data, Request $request): Polling
    {
        $creating = ! $polling->exists;
        $activeYear = $creating ? TahunPelajaran::query()->active()->first() : null;
        $polling->fill([
            'slug' => $creating ? $this->uniqueSlug($data['title']) : $polling->slug,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'audience' => $data['audience'],
            'status' => $data['action'] === 'publish' ? 'published' : 'draft',
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'allow_changes' => $request->boolean('allow_changes'),
            'show_results_after_submit' => $request->boolean('show_results_after_submit'),
            'require_consent' => $request->boolean('require_consent'),
            'consent_text' => $data['consent_text'] ?? null,
            'reminder_interval_hours' => $data['reminder_interval_hours'],
            'published_at' => $data['action'] === 'publish' ? ($polling->published_at ?: now()) : null,
            'created_by' => $creating ? auth()->id() : $polling->created_by,
            'updated_by' => auth()->id(),
            'tahun_pelajaran_id' => $creating ? $activeYear?->id : $polling->tahun_pelajaran_id,
            'tahun_pelajaran_snapshot' => $creating ? $activeYear?->nama : $polling->tahun_pelajaran_snapshot,
            'semester_snapshot' => $creating ? $activeYear?->semester_aktif : $polling->semester_snapshot,
            'source_polling_id' => $creating ? ($data['source_polling_id'] ?? null) : $polling->source_polling_id,
        ])->save();

        $polling->questions()->delete();
        foreach (array_values($data['questions']) as $index => $questionData) {
            $question = $polling->questions()->create([
                'prompt' => $questionData['prompt'],
                'type' => $questionData['type'],
                'is_required' => ! empty($questionData['is_required']),
                'min_selections' => $questionData['type'] === 'multiple' ? ($questionData['min_selections'] ?? null) : null,
                'max_selections' => $questionData['type'] === 'multiple' ? ($questionData['max_selections'] ?? null) : null,
                'sort_order' => $index,
            ]);
            $options = $questionData['type'] === 'yes_no'
                ? ['Ya', 'Tidak']
                : $this->optionLines($questionData['options_text'] ?? '');
            foreach ($options as $optionIndex => $label) {
                $question->options()->create(['label' => $label, 'sort_order' => $optionIndex]);
            }
        }

        $polling->targets()->delete();
        if (in_array($data['audience'], ['siswa', 'both'], true)) {
            $this->saveTargets($polling, 'siswa', $request->boolean('student_all'), [
                'tingkat' => $data['student_grades'] ?? [],
                'kelas' => $data['student_classes'] ?? [],
            ]);
        }
        if (in_array($data['audience'], ['gtk', 'both'], true)) {
            $this->saveTargets($polling, 'gtk', $request->boolean('gtk_all'), [
                'kategori_ptk' => $data['gtk_categories'] ?? [],
                'gtk' => $data['gtks'] ?? [],
            ]);
        }

        return $polling->fresh(['questions.options', 'targets']);
    }

    private function saveTargets(Polling $polling, string $audience, bool $all, array $scopes): void
    {
        if ($all) {
            $polling->targets()->create(['audience_type' => $audience, 'scope_type' => 'all']);
            return;
        }
        foreach ($scopes as $type => $values) {
            foreach (array_unique($values) as $value) {
                $polling->targets()->create(['audience_type' => $audience, 'scope_type' => $type, 'scope_value' => $value]);
            }
        }
    }

    private function optionLines(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text))->map(fn ($line) => trim($line))->filter()->unique()->values()->all();
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'polling';
        $slug = $base;
        for ($suffix = 2; Polling::withTrashed()->where('slug', $slug)->exists(); $suffix++) $slug = $base.'-'.$suffix;
        return $slug;
    }

    private function formOptions(): array
    {
        return [
            'classes' => Kelas::query()->with('tahunPelajaran')->where('is_active', true)
                ->whereHas('tahunPelajaran', fn ($query) => $query->active())
                ->orderBy('tingkat')->orderBy('nama_kelas')->get(),
            'gtks' => Gtk::query()->whereHas('user', fn ($query) => $query->where('is_active', true))
                ->orderBy('nama_lengkap')->get([
                    'id', 'nama_lengkap', 'nik', 'peg_id', 'foto_profile', 'jenis_kelamin',
                    'kategori_ptk', 'jenis_ptk', 'user_id',
                ]),
        ];
    }

    private function authorizeManage(): void
    {
        abort_unless(auth()->user()->can('manage-polling'), 403);
    }

    private function log(Polling $polling, string $type, string $description): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(), 'activity_type' => $type,
            'model_type' => Polling::class, 'model_id' => $polling->id,
            'description' => $description, 'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(), 'url' => request()->fullUrl(), 'method' => request()->method(),
        ]);
    }
}
