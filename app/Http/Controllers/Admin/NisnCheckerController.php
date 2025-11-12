<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EmisNisnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NisnCheckerController extends Controller
{
    protected $nisnService;

    public function __construct(EmisNisnService $nisnService)
    {
        $this->nisnService = $nisnService;
    }

    public function index()
    {
        // Cek permission atau role Super Admin
        if (!auth()->user()->can('manage-settings') && !auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        return view('admin.pengaturan.cek-nisn');
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
            'nisn' => 'required|string|size:10'
        ], [
            'nisn.required' => 'NISN wajib diisi',
            'nisn.size' => 'NISN harus tepat 10 digit'
        ]);

        $nisn = $request->nisn;

        // Validate NISN format
        if (!$this->nisnService->validateNisnFormat($nisn)) {
            return response()->json([
                'success' => false,
                'message' => 'Format NISN tidak valid. NISN harus berupa angka dengan panjang tepat 10 digit.'
            ]);
        }

        // Log activity
        Log::info('NISN Check Request', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'nisn' => $nisn,
            'timestamp' => now()
        ]);

        // Call API through service
        $result = $this->nisnService->cekNisn($nisn);

        // Log result
        Log::info('NISN Check Result', [
            'user_id' => auth()->id(),
            'nisn' => $nisn,
            'success' => $result['success'],
            'message' => $result['message']
        ]);

        // Return response
        return response()->json($result);
    }
}
