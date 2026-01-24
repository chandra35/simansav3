<?php

namespace App\View\Composers;

use App\Models\CustomMenu;
use App\Models\CustomMenuSiswa;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class CustomMenuComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $user = Auth::user();
        
        // Check if user is siswa
        if (!$user || !$user->siswa) {
            $view->with('customMenus', collect());
            $view->with('customMenuGroups', collect());
            return;
        }

        $siswa = $user->siswa;

        // Get all active custom menus assigned to this siswa
        $customMenus = CustomMenu::where('is_active', true)
            ->whereHas('siswaAssigned', function ($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id);
            })
            ->with(['siswaAssigned' => function ($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id);
            }])
            ->ordered()
            ->get();

        // Group menus by menu_group
        $customMenuGroups = $customMenus->groupBy(function ($menu) {
            return $menu->menu_group ?: 'lainnya';
        });

        // Pass to view
        $view->with('customMenus', $customMenus);
        $view->with('customMenuGroups', $customMenuGroups);
    }
}
