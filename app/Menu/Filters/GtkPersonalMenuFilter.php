<?php

namespace App\Menu\Filters;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class GtkPersonalMenuFilter implements FilterInterface
{
    /**
     * Hide GTK personal menu items (Dashboard Saya, Profil Saya) from users
     * who also have admin-dashboard-access (admins, operators, Super Admin).
     * These items should only appear for pure GTK users.
     */
    public function transform($item)
    {
        $gtkOnlyKeys = ['gtk-dashboard', 'gtk-account-menu', 'gtk-profile', 'gtk-school-activity-menu', 'gtk-osis-election'];

        if (isset($item['key']) && in_array($item['key'], $gtkOnlyKeys)) {
            $user = auth()->user();

            if ($user && $user->can('admin-dashboard-access')) {
                $item['restricted'] = true;
            }
        }

        return $item;
    }
}
