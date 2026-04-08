<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AcademicAuditService;

class AcademicHealthController extends Controller
{
    public function __construct(private readonly AcademicAuditService $auditService)
    {
    }

    public function index()
    {
        $this->authorize('manage-settings');

        $audit = $this->auditService->run();
        $checks = collect($audit['checks'] ?? []);
        $samples = collect($audit['samples'] ?? [])->filter(fn ($rows) => !empty($rows));

        $criticalChecks = $checks->where('count', '>', 0)->values();
        $healthyChecks = $checks->where('count', 0)->values();

        return view('admin.settings.academic-health', compact(
            'audit',
            'checks',
            'criticalChecks',
            'healthyChecks',
            'samples'
        ));
    }
}
