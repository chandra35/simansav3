<?php

namespace App\Providers;

use App\Models\CustomMenu;
use App\Models\OsisElection;
use App\Models\TahunPelajaran;
use App\Models\User;
use App\Observers\UserObserver;
use App\Services\PollingAudienceService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PollingAudienceService::class);

        // Cross-semester leggers contain tens of thousands of cells. Spill cells
        // to a dedicated cache in batches so exports fit PHP's 128 MB limit.
        config([
            'cache.stores.excel' => [
                'driver' => 'file',
                'path' => storage_path('framework/cache/excel'),
                'lock_path' => storage_path('framework/cache/excel'),
            ],
            'excel.cache.driver' => 'batch',
            'excel.cache.batch.memory_limit' => 1000,
            'excel.cache.illuminate.store' => 'excel',
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // AdminLTE menggunakan Bootstrap 4; jangan render pagination Tailwind
        // karena ikon SVG-nya tidak memiliki utility sizing pada layout ini.
        Paginator::useBootstrapFour();

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

        RateLimiter::for('emis-student-sync', function (Request $request) {
            $userKey = (string) ($request->user()?->getAuthIdentifier() ?: $request->ip());
            $routeStudent = $request->route('siswa');
            $studentKey = is_object($routeStudent) && method_exists($routeStudent, 'getRouteKey')
                ? (string) $routeStudent->getRouteKey()
                : (string) ($routeStudent ?: 'unknown');
            $response = fn (Request $request, array $headers) => response()->json([
                'success' => false,
                'message' => 'Permintaan sync untuk siswa ini terlalu cepat. Tunggu '.($headers['Retry-After'] ?? 60).' detik lalu coba kembali.',
                'retry_after' => (int) ($headers['Retry-After'] ?? 60),
            ], 429, $headers);

            return [
                // Klik ganda pada siswa yang sama tidak boleh membanjiri API EMIS.
                Limit::perMinute(4)->by("emis-student:{$userKey}:{$studentKey}")->response($response),
                // Admin tetap dapat memperbarui beberapa siswa berbeda secara berurutan.
                Limit::perMinute(30)->by("emis-user:{$userKey}")->response($response),
            ];
        });

        View::composer([
            'adminlte::partials.navbar.navbar',
            'adminlte::partials.navbar.navbar-layout-topnav',
        ], function ($view) {
            $view->with('navbarActiveAcademicYear', TahunPelajaran::query()
                ->active()
                ->select(['id', 'nama', 'semester_aktif'])
                ->first());
        });

        View::composer('partials.student-election-overlay', function ($view) {
            $user = Auth::user();
            $notice = null;

            if ($user?->isSiswa() && ! $user->is_first_login) {
                $election = OsisElection::query()
                    ->whereHas('tahunPelajaran', fn ($query) => $query->active())
                    ->whereIn('status', ['published', 'paused'])
                    ->where('ends_at', '>', now())
                    ->orderBy('starts_at')
                    ->first();

                if ($election) {
                    $voter = $election->voters()
                        ->where('user_id', $user->id)
                        ->first(['has_voted']);

                    $notice = [
                        'id' => $election->id,
                        'title' => $election->title,
                        'theme' => $election->theme,
                        'phase' => $election->phase,
                        'starts_at' => $election->starts_at->toIso8601String(),
                        'ends_at' => $election->ends_at->toIso8601String(),
                        'has_voted' => (bool) $voter?->has_voted,
                        'url' => route('siswa.osis-election.index'),
                    ];
                }
            }

            $view->with('studentElectionNotice', $notice);
        });

        View::composer('partials.polling-reminder', function ($view) {
            $notice = null;
            $user = Auth::user();

            if ($user && ! $user->is_first_login) {
                $audience = app(PollingAudienceService::class);
                $polling = $audience->pendingForUser($user)->first(function ($candidate) use ($user) {
                    $state = $candidate->notificationStates()->where('user_id', $user->id)->first();
                    return ! $state?->snoozed_until || $state->snoozed_until->lte(now());
                });

                if ($polling) {
                    $context = $audience->respondentContext($user);
                    $routePrefix = $context['type'] === 'siswa' ? 'siswa.polling' : 'admin.gtk.polling';
                    $notice = [
                        'id' => $polling->id,
                        'title' => $polling->title,
                        'description' => $polling->description,
                        'ends_at' => $polling->ends_at->translatedFormat('d F Y H:i'),
                        'url' => route($routePrefix.'.show', $polling),
                        'snooze_url' => route($routePrefix.'.snooze', $polling),
                    ];
                }
            }

            $view->with('pendingPollingNotice', $notice);
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
                        'key' => 'custom-menu-header-'.$groupKey,
                    ]);

                    // Add menu items under this group
                    foreach ($menus as $menu) {
                        $assignment = $menu->menuSiswa->first();
                        $isUnread = $assignment && ! $assignment->is_read;

                        $menuItem = [
                            'text' => $menu->judul,
                            'url' => route('siswa.menu.show', $menu->slug),
                            'icon' => $menu->icon ?: 'fas fa-file-alt',
                            'key' => 'custom-menu-'.$menu->id,
                        ];

                        // Add badge if unread
                        if ($isUnread) {
                            $menuItem['label'] = 'NEW';
                            $menuItem['label_color'] = 'danger';
                        }

                        $event->menu->addAfter('custom-menu-header-'.$groupKey, $menuItem);
                    }
                }
            }
        });
    }
}
