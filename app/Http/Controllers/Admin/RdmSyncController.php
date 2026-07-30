<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RdmSyncRun;
use App\Models\RdmSyncStaging;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use App\Services\RdmSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RdmSyncController extends Controller
{
    public function __construct(private readonly RdmSyncService $rdmSyncService)
    {
    }

    public function index(Request $request): View
    {
        $latestRuns = RdmSyncRun::query()
            ->with('initiatedBy:id,name')
            ->latest('created_at')
            ->limit(15)
            ->get();

        $selectedRun = null;
        if ($request->filled('run')) {
            $selectedRun = RdmSyncRun::query()->find($request->string('run')->toString());
        }

        if (!$selectedRun && $latestRuns->isNotEmpty()) {
            $selectedRun = $latestRuns->first();
        }

        $mismatchRows = collect();
        $sampleRows = collect();
        $actionCounts = collect();
        if ($selectedRun) {
            $mismatchRows = RdmSyncStaging::query()
                ->where('run_id', $selectedRun->id)
                ->where('match_status', '!=', 'matched')
                ->orderBy('match_status')
                ->orderBy('rdm_nama')
                ->limit(150)
                ->get();

            $sampleRows = RdmSyncStaging::query()
                ->with(['siswa:id,nama_lengkap,nisn', 'mataPelajaran:id,nama_mapel,kode_mapel'])
                ->where('run_id', $selectedRun->id)
                ->orderByRaw("FIELD(apply_action, 'conflict', 'insert', 'unchanged', 'skip')")
                ->orderBy('rdm_nama')
                ->limit(80)
                ->get();

            $actionCounts = RdmSyncStaging::query()
                ->where('run_id', $selectedRun->id)
                ->selectRaw('apply_action, COUNT(*) as total')
                ->groupBy('apply_action')
                ->pluck('total', 'apply_action');
        }

        return view('admin.rdm-sync.index', [
            'rdmPeriod' => $this->rdmSyncService->getRdmActivePeriod(),
            'rdmReference' => $this->rdmSyncService->getRdmReference(),
            'latestRuns' => $latestRuns,
            'selectedRun' => $selectedRun,
            'mismatchRows' => $mismatchRows,
            'sampleRows' => $sampleRows,
            'actionCounts' => $actionCounts,
            'simansaTahunList' => TahunPelajaran::orderByDesc('tahun_mulai')->get(),
            'simansaKelasList' => Kelas::with('tahunPelajaran')
                ->whereIn('tingkat', [10, 11, 12])->orderBy('nama_kelas')->get(),
        ]);
    }

    public function preview(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rdm_tahunajaran_id' => ['required', 'integer'],
            'rdm_semester_id' => ['required', 'integer'],
            'rdm_tingkat_id' => ['nullable', 'integer'],
            'rdm_kelas_nama' => ['nullable', 'string', 'max:120'],
            'simansa_tahun_pelajaran_id' => ['required', 'uuid', 'exists:tahun_pelajaran,id'],
            'simansa_kelas_id' => ['nullable', 'uuid', 'exists:kelas,id'],
            'simansa_tingkat' => ['required', 'integer', 'in:10,11,12'],
        ]);

        if (empty($data['rdm_tingkat_id'])) {
            return back()->withInput()->withErrors([
                'rdm_tingkat_id' => 'Tingkat wajib dipilih agar semester leger dapat dipetakan dengan benar.',
            ]);
        }

        $run = $this->rdmSyncService->previewSync($data, Auth::id());

        return redirect()
            ->route('admin.rdm-sync.index', ['run' => $run->id])
            ->with('success', 'Preview sync selesai. Periksa ringkasan dan mismatch sebelum apply.');
    }

    public function apply(Request $request, RdmSyncRun $run): RedirectResponse
    {
        if ($run->status === 'applied') {
            return redirect()
                ->route('admin.rdm-sync.index', ['run' => $run->id])
                ->with('error', 'Run ini sudah pernah di-apply.');
        }

        $updatedRun = $this->rdmSyncService->applySync($run);
        $message = $updatedRun->status === 'applied'
            ? "Apply sync berhasil: {$updatedRun->applied_count} baris nilai terproses."
            : 'Apply sync gagal. Periksa catatan run.';

        return redirect()
            ->route('admin.rdm-sync.index', ['run' => $run->id])
            ->with($updatedRun->status === 'applied' ? 'success' : 'error', $message);
    }
}
