<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Illuminate\Support\Facades\Event;
use App\Models\CustomMenu;
use App\Models\CustomMenuSiswa;
use App\Models\User;
use App\Observers\UserObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Register User Observer untuk sync password ke RADIUS
        User::observe(UserObserver::class);
        RateLimiter::for('exam-browser-config', function (Request $request) {
            return [
                Limit::perMinute(1200)->by($request->ip())->response(fn () => response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak request config. Silakan tunggu beberapa saat.',
                ], 429)),
            ];
        });

        // Menu items are now configured in config/adminlte.php
        // with proper permission filtering using 'can' => 'siswa-access'
        
        // Register dynamic custom menus for siswa
        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {
            $user = Auth::user();
            
            // Only for siswa users
            if ($user && $user->siswa) {
                $siswa = $user->siswa;
                
                // Get active custom menus assigned to this siswa
                $customMenus = CustomMenu::where('is_active', true)
                    ->whereHas('menuSiswa', function ($q) use ($siswa) {
                        $q->where('siswa_id', $siswa->id);
                    })
                    ->with(['menuSiswa' => function ($q) use ($siswa) {
                        $q->where('siswa_id', $siswa->id);
                    }])
                    ->ordered()
                    ->get()
                    ->groupBy(function ($menu) {
                        return $menu->menu_group ?: 'lainnya';
                    });

                // Group labels with icons
                $groupLabels = [
                    'akademik' => ['label' => 'AKADEMIK', 'icon' => 'fas fa-graduation-cap'],
                    'administrasi' => ['label' => 'ADMINISTRASI', 'icon' => 'fas fa-file-alt'],
                    'hotspot' => ['label' => 'HOTSPOT & AKUN', 'icon' => 'fas fa-wifi'],
                    'lainnya' => ['label' => 'LAINNYA', 'icon' => 'fas fa-ellipsis-h'],
                ];

                // Add menus by group
                foreach ($customMenus as $groupKey => $menus) {
                    // Add group header
                    $groupInfo = $groupLabels[$groupKey] ?? $groupLabels['lainnya'];
                    $event->menu->addAfter('siswa-lulusan', [
                        'type' => 'header',
                        'text' => $groupInfo['label'],
                        'icon' => $groupInfo['icon'],
                        'key' => 'custom-menu-header-' . $groupKey,
                    ]);

                    // Add menu items under this group
                    foreach ($menus as $menu) {
                        $assignment = $menu->menuSiswa->first();
                        $isUnread = $assignment && !$assignment->is_read;

                        $menuItem = [
                            'text' => $menu->judul,
                            'url' => route('siswa.menu.show', $menu->slug),
                            'icon' => $menu->icon ?: 'fas fa-file-alt',
                            'key' => 'custom-menu-' . $menu->id,
                        ];

                        // Add badge if unread
                        if ($isUnread) {
                            $menuItem['label'] = 'NEW';
                            $menuItem['label_color'] = 'danger';
                        }

                        $event->menu->addAfter('custom-menu-header-' . $groupKey, $menuItem);
                    }
                }
            }
        });
    }
}
