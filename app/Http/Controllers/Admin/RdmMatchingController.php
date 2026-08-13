<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RdmMatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RdmMatchingController extends Controller
{
    public function __construct(private readonly RdmMatchingService $service)
    {
    }

    public function index(): View
    {
        $this->authorize('view-rdm');

        $activeTahun = $this->service->getActiveTahunAjaran();

        return view('admin.rdm-matching.index', [
            'activeTahun' => $activeTahun,
            'tingkatOptions' => [
                ''   => 'Semua Tingkat',
                '12' => 'Kelas X',
                '13' => 'Kelas XI',
                '14' => 'Kelas XII',
            ],
        ]);
    }

    /**
     * AJAX: jalankan matching dan kembalikan JSON.
     * Proses ini melakukan HTTP call ke cipher endpoint RDM (bisa 30-60 detik untuk data besar).
     */
    public function run(Request $request): JsonResponse
    {
        $this->authorize('manage-rdm');

        $tingkatId = $request->filled('tingkat_id')
            ? (int) $request->input('tingkat_id')
            : null;

        // Durasi proses bisa lama karena batch decrypt ke VM RDM
        set_time_limit(300);

        try {
            $result = $this->service->runMatching($tingkatId);
            return response()->json(['status' => 'success', 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menjalankan matching: ' . $e->getMessage(),
            ], 500);
        }
    }
}
