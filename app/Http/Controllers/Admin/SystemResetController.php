<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemResetService;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;

class SystemResetController extends Controller
{
    protected $resetService;
    protected $backupService;

    public function __construct(
        SystemResetService $resetService,
        DatabaseBackupService $backupService
    ) {
        $this->resetService = $resetService;
        $this->backupService = $backupService;
    }

    /**
     * Show reset system page
     */
    public function index()
    {
        // Only Super Admin
        if (!Auth::user()->hasRole('Super Admin')) {
            abort(403, 'Hanya Super Admin yang dapat mengakses halaman ini');
        }

        $counts = $this->resetService->countAffectedData();
        $backups = $this->backupService->listBackups();
        $backupStats = $this->backupService->getTotalBackupSize();

        return view('admin.pengaturan.reset-system', compact('counts', 'backups', 'backupStats'));
    }

    /**
     * Verify password before showing delete confirmation
     */
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password salah',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password benar',
        ]);
    }

    /**
     * Delete ALL data
     */
    public function deleteAll(Request $request)
    {
        if (!Auth::user()->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'password' => 'required',
            'confirmation' => 'required|in:HAPUS SEMUA DATA',
            'mode' => 'required|in:permanent,archive',
            'auto_backup' => 'boolean',
        ]);

        // Re-verify password
        if (!Hash::check($request->password, Auth::user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password salah',
            ]);
        }

        $result = $this->resetService->resetAllData(
            $request->mode,
            $request->auto_backup ?? true
        );

        return response()->json($result);
    }

    /**
     * Delete SISWA only
     */
    public function deleteSiswa(Request $request)
    {
        if (!Auth::user()->hasRole('Super Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'password' => 'required',
            'confirmation' => 'required|in:HAPUS DATA SISWA',
            'mode' => 'required|in:permanent,archive',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return response()->json(['success' => false, 'message' => 'Password salah']);
        }

        $result = $this->resetService->deleteSiswaOnly($request->mode, true);
        return response()->json($result);
    }

    /**
     * Delete GTK only
     */
    public function deleteGtk(Request $request)
    {
        if (!Auth::user()->hasRole('Super Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'password' => 'required',
            'confirmation' => 'required|in:HAPUS DATA GTK',
            'mode' => 'required|in:permanent,archive',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return response()->json(['success' => false, 'message' => 'Password salah']);
        }

        $result = $this->resetService->deleteGtkOnly($request->mode, true);
        return response()->json($result);
    }

    /**
     * Delete KELAS only
     */
    public function deleteKelas(Request $request)
    {
        if (!Auth::user()->hasRole('Super Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'password' => 'required',
            'confirmation' => 'required|in:HAPUS DATA KELAS',
            'mode' => 'required|in:permanent,archive',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return response()->json(['success' => false, 'message' => 'Password salah']);
        }

        $result = $this->resetService->deleteKelasOnly($request->mode, true);
        return response()->json($result);
    }

    // ========== BACKUP & RESTORE ==========

    /**
     * Create manual backup
     */
    public function createBackup()
    {
        if (!Auth::user()->hasRole('Super Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $result = $this->backupService->createBackup('manual');
        return response()->json($result);
    }

    /**
     * Download backup file
     */
    public function downloadBackup($filename)
    {
        if (!Auth::user()->hasRole('Super Admin')) {
            abort(403);
        }

        $filepath = storage_path('app/backups/database/' . $filename);

        if (!file_exists($filepath)) {
            abort(404, 'Backup file not found');
        }

        return response()->download($filepath);
    }

    /**
     * Delete backup file
     */
    public function deleteBackup(Request $request, $filename)
    {
        if (!Auth::user()->hasRole('Super Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $result = $this->backupService->deleteBackup($filename);
        return response()->json($result);
    }

    /**
     * Restore from backup
     */
    public function restoreBackup(Request $request)
    {
        if (!Auth::user()->hasRole('Super Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'filename' => 'required',
            'password' => 'required',
            'confirmation' => 'required|in:RESTORE DATABASE',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return response()->json(['success' => false, 'message' => 'Password salah']);
        }

        // Create safety backup before restore
        $safetyBackup = $this->backupService->createBackup('before_restore');

        $result = $this->backupService->restoreBackup($request->filename);

        if ($result['success']) {
            $result['safety_backup'] = $safetyBackup['filename'] ?? null;
        }

        return response()->json($result);
    }
}
