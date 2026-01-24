<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\KemenagNipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NipCheckerController extends Controller
{
    protected $nipService;

    public function __construct(KemenagNipService $nipService)
    {
        $this->nipService = $nipService;
    }

    public function index()
    {
        // Cek permission atau role Super Admin
        if (!auth()->user()->can('manage-settings') && !auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        return view('admin.pengaturan.cek-nip');
    }

    public function check(Request $request)
    {
        // Cek permission atau role Super Admin
        if (!auth()->user()->can('manage-settings') && !auth()->user()->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $request->validate([
            'nip' => 'required|string|size:18'
        ], [
            'nip.required' => 'NIP wajib diisi',
            'nip.size' => 'NIP harus tepat 18 digit'
        ]);

        $nip = $request->nip;

        // Validate NIP format
        if (!$this->nipService->validateNipFormat($nip)) {
            return response()->json([
                'success' => false,
                'message' => 'Format NIP tidak valid. NIP harus berupa angka dengan panjang tepat 18 digit.'
            ]);
        }

        // Log activity
        Log::info('NIP Check Request', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'nip' => $nip,
            'timestamp' => now()
        ]);

        // Call API through service
        $result = $this->nipService->cekNip($nip);

        // Log result
        Log::info('NIP Check Result', [
            'user_id' => auth()->id(),
            'nip' => $nip,
            'success' => $result['success'],
            'message' => $result['message']
        ]);

        // Return response
        return response()->json($result);
    }
}
