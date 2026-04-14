<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BknNikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NikCheckerController extends Controller
{
    protected BknNikService $nikService;

    public function __construct(BknNikService $nikService)
    {
        $this->nikService = $nikService;
    }

    public function index()
    {
        if (!auth()->user()->can('manage-settings') && !auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        return view('admin.pengaturan.cek-nik');
    }

    public function check(Request $request)
    {
        if (!auth()->user()->can('manage-settings') && !auth()->user()->hasRole('Super Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'nik'            => ['required', 'string', 'size:16', 'regex:/^\d{16}$/'],
            'nokk'           => ['required', 'string', 'size:16', 'regex:/^\d{16}$/'],
            'nama'           => ['required', 'string', 'max:255'],
            'tgl_lahir'      => ['required', 'date_format:Y-m-d'],
            'agama'          => ['required', 'string'],
            'jenis_kelamin'  => ['required', 'in:M,F'],
            'id_usulan'      => ['nullable', 'uuid'],
        ], [
            'nik.size'           => 'NIK harus tepat 16 digit',
            'nik.regex'          => 'NIK hanya boleh berisi angka',
            'nokk.size'          => 'Nomor KK harus tepat 16 digit',
            'nokk.regex'         => 'Nomor KK hanya boleh berisi angka',
            'jenis_kelamin.in'   => 'Jenis kelamin tidak valid',
        ]);

        Log::info('NIK Check Request', [
            'user_id'   => auth()->id(),
            'user_name' => auth()->user()->name,
            'nik'       => $request->nik,
        ]);

        $result = $this->nikService->cekNik($request->only(
            'nik', 'nokk', 'nama', 'tgl_lahir', 'agama', 'jenis_kelamin', 'id_usulan'
        ));

        return response()->json($result);
    }
}
