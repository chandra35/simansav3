<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\CustomMenu;
use App\Models\CustomMenuSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomMenuController extends Controller
{
    /**
     * Display a listing of custom menus for this siswa
     */
    public function index()
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (!$siswa) {
            abort(403, 'User tidak terdaftar sebagai siswa');
        }

        // Get all active custom menus assigned to this siswa
        $customMenus = CustomMenu::where('is_active', true)
            ->whereHas('siswaAssigned', function ($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id);
            })
            ->with(['siswaAssigned' => function ($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id);
            }])
            ->ordered()
            ->get()
            ->groupBy('menu_group');

        // Count unread menus
        $unreadCount = CustomMenuSiswa::where('siswa_id', $siswa->id)
            ->where('is_read', false)
            ->whereHas('customMenu', function ($q) {
                $q->where('is_active', true);
            })
            ->count();

        return view('siswa.custom-menu.index', compact('customMenus', 'unreadCount'));
    }

    /**
     * Display the specified custom menu
     */
    public function show($slug)
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (!$siswa) {
            abort(403, 'User tidak terdaftar sebagai siswa');
        }

        // Find menu by slug
        $menu = CustomMenu::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Get assignment data for this siswa
        $assignment = CustomMenuSiswa::where('custom_menu_id', $menu->id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        // Mark as read if not yet read
        if (!$assignment->is_read) {
            $assignment->markAsRead();
        }

        // Get personal data (already decoded by model cast)
        $personalData = $assignment->personal_data ?? [];
        
        // Get custom fields configuration
        $customFields = $menu->getCustomFieldsArray();

        return view('siswa.custom-menu.show', compact('menu', 'assignment', 'personalData', 'customFields'));
    }

    /**
     * Mark a menu as read (AJAX)
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terdaftar sebagai siswa'
            ], 403);
        }

        try {
            $assignment = CustomMenuSiswa::where('custom_menu_id', $id)
                ->where('siswa_id', $siswa->id)
                ->firstOrFail();

            $assignment->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Menu berhasil ditandai sebagai sudah dibaca'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai menu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread count (for AJAX/notification)
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json(['count' => 0]);
        }

        $count = CustomMenuSiswa::where('siswa_id', $siswa->id)
            ->where('is_read', false)
            ->whereHas('customMenu', function ($q) {
                $q->where('is_active', true);
            })
            ->count();

        return response()->json(['count' => $count]);
    }
}
