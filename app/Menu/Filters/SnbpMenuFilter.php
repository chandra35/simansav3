<?php

namespace App\Menu\Filters;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class SnbpMenuFilter implements FilterInterface
{
    /**
     * Filter SNBP menu visibility based on whether user is kelas 12 siswa
     */
    public function transform($item)
    {
        // Check if this is the SNBP menu item
        if (isset($item['key']) && $item['key'] === 'siswa-snbp') {
            // Check if current user is a siswa with kelas 12
            $user = auth()->user();
            
            if (!$user) {
                $item['restricted'] = true;
                return $item;
            }

            // Get associated siswa
            $siswa = $user->siswa;
            
            if (!$siswa) {
                $item['restricted'] = true;
                return $item;
            }

            // Check if siswa is in kelas 12
            $kelas = $siswa->kelasSaatIni;
            
            if (!$kelas) {
                $item['restricted'] = true;
                return $item;
            }

            // Check tingkat (level) - must be 12
            $tingkat = $kelas->tingkat ?? null;
            
            if ($tingkat != 12) {
                $item['restricted'] = true;
                return $item;
            }

            // Check if there's an active SNBP menu
            $activeMenu = \App\Models\SnbpMenu::getActiveMenu();
            
            if (!$activeMenu) {
                $item['restricted'] = true;
                return $item;
            }
        }

        return $item;
    }
}
