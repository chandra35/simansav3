<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MoodleIntegration;
use App\Models\MoodleSyncRun;
use App\Services\MoodleSyncService;
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

    public function test()
    {
        try {
            $result = $this->service->test(MoodleIntegration::current());
            return back()->with('toastr_success', 'Koneksi berhasil: '.($result['sitename'] ?? 'Moodle'));
        } catch (\Throwable $e) {
            MoodleIntegration::current()->update(['last_tested_at' => now(), 'last_test_status' => 'failed', 'last_test_message' => $e->getMessage()]);
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
}
