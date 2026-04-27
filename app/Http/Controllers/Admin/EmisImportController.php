<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EmisExcelImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmisImportController extends Controller
{
    /**
     * Halaman form import EMIS
     */
    public function form()
    {
        $this->authorize('create-siswa');
        return view('admin.siswa.import-emis');
    }

    /**
     * Parse file EMIS → kembalikan preview JSON
     * Simpan hasil parse ke session agar execute tidak perlu terima data besar dari client.
     */
    public function parse(Request $request): JsonResponse
    {
        $this->authorize('create-siswa');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'File Excel wajib dipilih.',
            'file.mimes'    => 'File harus berformat Excel (.xlsx atau .xls).',
            'file.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            $service = new EmisExcelImportService();
            $preview = $service->parse($request->file('file'));

            // Simpan ke session — execute hanya butuh indeks yang dipilih
            session(['emis_import_preview' => $preview]);

            $stats = [
                'total'  => count($preview),
                'baru'   => count(array_filter($preview, fn($r) => $r['action'] === 'baru')),
                'update' => count(array_filter($preview, fn($r) => $r['action'] === 'update')),
                'fuzzy'  => count(array_filter($preview, fn($r) => $r['action'] === 'fuzzy')),
                'skip'   => count(array_filter($preview, fn($r) => $r['action'] === 'skip')),
            ];

            return response()->json([
                'success' => true,
                'preview' => $preview,
                'stats'   => $stats,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Execute: simpan baris yang dipilih user
     * Client mengirim array indeks yang dicentang.
     */
    public function execute(Request $request): JsonResponse
    {
        $this->authorize('create-siswa');

        $request->validate([
            'selected_indices'   => 'required|array|min:1',
            'selected_indices.*' => 'integer|min:0',
        ], [
            'selected_indices.required' => 'Tidak ada baris yang dipilih.',
            'selected_indices.min'      => 'Pilih minimal 1 baris untuk disimpan.',
        ]);

        $preview = session('emis_import_preview');
        if (!$preview) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi preview sudah kadaluarsa. Silakan upload ulang file.',
            ], 422);
        }

        $indices  = $request->input('selected_indices');
        $selected = [];
        foreach ($indices as $idx) {
            if (isset($preview[$idx])) {
                $selected[] = $preview[$idx];
            }
        }

        if (empty($selected)) {
            return response()->json([
                'success' => false,
                'message' => 'Indeks baris tidak valid.',
            ], 422);
        }

        $service = new EmisExcelImportService();
        $result  = $service->execute($selected);

        // Hapus session setelah berhasil
        session()->forget('emis_import_preview');

        return response()->json([
            'success' => true,
            'done'    => $result['done'],
            'total'   => count($selected),
            'errors'  => $result['errors'],
        ]);
    }
}
