<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MoodleIntegration;
use App\Models\MoodleSyncRun;
use App\Services\MoodleSyncService;
use App\Jobs\MoodleSyncJob;
use Illuminate\Http\Request;

class MoodleSyncController extends Controller
{
    public function __construct(private readonly MoodleSyncService $service) {}

    public function index()
    {
        $integration = MoodleIntegration::current();
        $preview = $this->service->preview();
        $runs = MoodleSyncRun::query()->with('items')->latest()->limit(15)->get();

        return view('admin.moodle-sync.index', compact('integration', 'preview', 'runs'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'base_url' => ['required', 'url', 'max:255'],
            'webservice_token' => ['nullable', 'string', 'max:500'],
            'student_email_domain' => ['required', 'string', 'max:120'],
        ]);
        $integration = MoodleIntegration::current();
        if (blank($validated['webservice_token'])) unset($validated['webservice_token']);
        $validated['enabled'] = $request->boolean('enabled');
        $validated['sync_users'] = $request->boolean('sync_users');
        $validated['sync_cohorts'] = $request->boolean('sync_cohorts');
        $validated['sync_categories'] = $request->boolean('sync_categories');
        $integration->update($validated);

        return back()->with('toastr_success', 'Konfigurasi integrasi Moodle disimpan.');
    }

    public function test(Request $request)
    {
        try {
            $result = $this->service->test(MoodleIntegration::current());
            if ($request->expectsJson()) return response()->json(['message' => 'Koneksi berhasil: '.($result['sitename'] ?? 'Moodle')]);
            return back()->with('toastr_success', 'Koneksi berhasil: '.($result['sitename'] ?? 'Moodle'));
        } catch (\Throwable $e) {
            MoodleIntegration::current()->update(['last_tested_at' => now(), 'last_test_status' => 'failed', 'last_test_message' => $e->getMessage()]);
            if ($request->expectsJson()) return response()->json(['message' => 'Tes koneksi gagal: '.$e->getMessage()], 422);
            return back()->with('toastr_error', 'Tes koneksi gagal: '.$e->getMessage());
        }
    }

    public function sync(Request $request)
    {
        $type = $request->validate(['type' => ['required', 'in:users,cohorts,categories,all']])['type'];
        try {
            $run = $this->service->run(MoodleIntegration::current(), $type, auth()->id());
            $summary = $run->summary ?: [];
            return back()->with('toastr_success', 'Sinkronisasi selesai. Dibuat: '.($summary['created'] ?? 0).', diperbarui: '.($summary['updated'] ?? 0).', gagal: '.($summary['failed'] ?? 0).'.');
        } catch (\Throwable $e) {
            return back()->with('toastr_error', 'Sinkronisasi gagal: '.$e->getMessage());
        }
    }

    public function previewSync(Request $request)
    {
        $type = $request->validate(['type' => ['required', 'in:users,cohorts,categories,all']])['type'];
        $preview = $this->service->preview();
        $total = match ($type) {
            'users' => $preview['total_users'], 'cohorts' => $preview['total_cohorts'],
            'categories' => $preview['total_categories'],
            default => $preview['total_users'] + $preview['total_cohorts'] + $preview['total_categories'],
        };
        return response()->json(['type' => $type, 'preview' => $preview, 'total' => $total, 'message' => 'Preview dihitung dari data SIMANSA terbaru. Moodle hanya akan dibuat atau diperbarui; tidak ada penghapusan otomatis.']);
    }

    public function start(Request $request)
    {
        $type = $request->validate(['type' => ['required', 'in:users,cohorts,categories,all']])['type'];
        if (MoodleSyncRun::whereIn('status', ['queued', 'running'])->exists()) return response()->json(['message' => 'Masih ada sinkronisasi Moodle yang berjalan.'], 409);
        try {
            $run = $this->service->createRun(MoodleIntegration::current(), $type, auth()->id());
            MoodleSyncJob::dispatch($run->id);
            return response()->json(['run_id' => $run->id, 'message' => 'Sinkronisasi masuk antrean.']);
        } catch (\Throwable $e) { return response()->json(['message' => $e->getMessage()], 422); }
    }

    public function progress(MoodleSyncRun $run)
    {
        abort_unless($run->moodle_integration_id === MoodleIntegration::current()->id, 404);
        $summary = $run->summary ?: [];
        $percent = $run->total_items > 0 ? min(100, (int) round(($run->processed_items / $run->total_items) * 100)) : ($run->status === 'success' ? 100 : 0);
        return response()->json(['run_id' => $run->id, 'status' => $run->status, 'stage' => $run->current_stage, 'processed' => $run->processed_items, 'total' => $run->total_items, 'percent' => $percent, 'summary' => $summary, 'error' => $run->error]);
    }
}
