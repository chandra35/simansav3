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
        $latestChecks = [
            'students' => $this->service->latestCheckSnapshot($integration, 'students'),
            'gtk' => $this->service->latestCheckSnapshot($integration, 'gtk'),
            'cohorts' => $this->service->latestCheckSnapshot($integration, 'cohorts'),
        ];
        return view('admin.moodle-sync.index', compact('integration', 'preview', 'latestChecks'));
    }

    public function settings()
    {
        return view('admin.moodle-sync.settings', ['integration' => MoodleIntegration::current()]);
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
        abort(410, 'Sinkronisasi langsung dinonaktifkan. Jalankan preview dan konfirmasi terlebih dahulu.');
    }

    public function previewSync(Request $request)
    {
        $type = $request->validate(['type' => ['required', 'in:users,cohorts,categories,all']])['type'];
        $comparison = $this->service->comparePreview(MoodleIntegration::current(), $type);
        return response()->json(['type' => $type, 'preview' => $comparison['local'], 'plan' => $comparison['plan'], 'actions' => $comparison['actions'], 'preview_token' => $comparison['preview_token'], 'comparison_complete' => $comparison['comparison_complete'], 'message' => $comparison['message']]);
    }

    public function smartCheckUsers(Request $request)
    {
        try {
            $subject = $request->validate(['subject' => ['nullable', 'in:students,gtk,cohorts']])['subject'] ?? 'students';
            $result = match ($subject) {
                'gtk' => $this->service->smartCheckGtk(MoodleIntegration::current()),
                'cohorts' => $this->service->smartCheckCohorts(MoodleIntegration::current()),
                default => $this->service->smartCheckUsers(MoodleIntegration::current()),
            };
            return response()->json(array_merge($result, ['subject' => $subject, 'saved_snapshot' => true]));
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function resolveConflict(Request $request)
    {
        $validated = $request->validate(['subject' => ['required', 'in:students,gtk'], 'local_id' => ['required', 'string'], 'resolution' => ['required', 'in:correct_username,update_name,verified,ignored'], 'new_username' => ['nullable', 'string', 'max:100'], 'note' => ['nullable', 'string', 'max:500']]);
        try {
            return response()->json($this->service->resolveConflict(MoodleIntegration::current(), $validated['subject'], $validated['local_id'], $validated['resolution'], $validated['new_username'] ?? null, $validated['note'] ?? null));
        } catch (\Throwable $e) { return response()->json(['message' => $e->getMessage()], 422); }
    }

    public function createMissingUser(Request $request)
    {
        $validated = $request->validate(['subject' => ['required', 'in:students,gtk'], 'local_id' => ['required', 'string'], 'confirmed' => ['accepted']]);
        try { return response()->json($this->service->createMissingUser(MoodleIntegration::current(), $validated['subject'], $validated['local_id'])); }
        catch (\Throwable $e) { return response()->json(['message' => $e->getMessage()], 422); }
    }

    public function updateCohortIds(Request $request)
    {
        $validated = $request->validate(['local_ids' => ['required', 'array', 'min:1', 'max:100'], 'local_ids.*' => ['required', 'string'], 'confirmed' => ['accepted']]);
        try { return response()->json($this->service->updateCohortIds(MoodleIntegration::current(), $validated['local_ids'])); }
        catch (\Throwable $e) { return response()->json(['message' => $e->getMessage()], 422); }
    }

    public function start(Request $request)
    {
        $validated = $request->validate(['type' => ['required', 'in:users,cohorts,categories,all'], 'preview_token' => ['required', 'string'], 'confirmed' => ['accepted']]);
        if (MoodleSyncRun::whereIn('status', ['queued', 'running'])->exists()) return response()->json(['message' => 'Masih ada sinkronisasi Moodle yang berjalan.'], 409);
        try {
            $preview = $this->service->consumePreview(MoodleIntegration::current(), $validated['preview_token'], $validated['type']);
            $run = $this->service->createRun(MoodleIntegration::current(), $validated['type'], auth()->id(), $preview);
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
