<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ApplyUserImpersonation;
use App\Models\Gtk;
use App\Models\Siswa;
use App\Models\User;
use App\Models\UserImpersonation;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserImpersonationController extends Controller
{
    private const DURATION_MINUTES = 60;

    public function startSiswa(Request $request, Siswa $siswa): RedirectResponse
    {
        $target = $siswa->user;
        $this->validateTarget($request->user(), $target, 'siswa');

        return $this->start($request, $target, 'siswa', route('siswa.dashboard'));
    }

    public function startGtk(Request $request, Gtk $gtk): RedirectResponse
    {
        $target = $gtk->user;
        $this->validateTarget($request->user(), $target, 'gtk');

        return $this->start($request, $target, 'gtk', route('admin.gtk.dashboard'));
    }

    public function stopSiswa(Request $request): RedirectResponse
    {
        return $this->stop($request, 'siswa');
    }

    public function stopGtk(Request $request): RedirectResponse
    {
        return $this->stop($request, 'gtk');
    }

    private function stop(Request $request, string $targetType): RedirectResponse
    {
        abort_unless(isset(ApplyUserImpersonation::COOKIE_NAMES[$targetType]), 404);

        /** @var UserImpersonation|null $impersonation */
        $impersonation = $request->attributes->get('impersonation');
        abort_unless($impersonation && $impersonation->target_type === $targetType, 403);

        $impersonation->forceFill([
            'ended_at' => now(),
            'ended_reason' => 'manual',
        ])->save();

        ActivityLogService::log([
            'user_id' => $impersonation->impersonator_id,
            'activity_type' => 'impersonation_ended',
            'model_type' => User::class,
            'model_id' => $impersonation->target_user_id,
            'description' => "Mengakhiri Login As {$targetType}: {$impersonation->targetUser->name}",
            'properties' => [
                'impersonation_id' => $impersonation->id,
                'target_type' => $targetType,
            ],
        ]);

        $redirectRoute = $targetType === 'siswa' ? 'admin.siswa.index' : 'admin.gtk.index';

        return redirect()
            ->route($redirectRoute)
            ->with('success', 'Mode Login As telah ditutup. Sesi admin tetap aktif.')
            ->withoutCookie(
                ApplyUserImpersonation::COOKIE_NAMES[$targetType],
                ApplyUserImpersonation::COOKIE_PATHS[$targetType]
            );
    }

    private function start(
        Request $request,
        User $target,
        string $targetType,
        string $destination
    ): RedirectResponse {
        $plainToken = Str::random(80);

        $impersonation = DB::transaction(function () use ($request, $target, $targetType, $plainToken) {
            UserImpersonation::query()
                ->where('impersonator_id', $request->user()->id)
                ->where('target_type', $targetType)
                ->whereNull('ended_at')
                ->update([
                    'ended_at' => now(),
                    'ended_reason' => 'replaced',
                    'updated_at' => now(),
                ]);

            return UserImpersonation::create([
                'impersonator_id' => $request->user()->id,
                'target_user_id' => $target->id,
                'target_type' => $targetType,
                'token_hash' => hash('sha256', $plainToken),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'expires_at' => now()->addMinutes(self::DURATION_MINUTES),
            ]);
        });

        ActivityLogService::log([
            'user_id' => $request->user()->id,
            'activity_type' => 'impersonation_started',
            'model_type' => User::class,
            'model_id' => $target->id,
            'description' => "Memulai Login As {$targetType}: {$target->name}",
            'properties' => [
                'impersonation_id' => $impersonation->id,
                'target_type' => $targetType,
                'expires_at' => $impersonation->expires_at->toIso8601String(),
            ],
        ]);

        return redirect($destination)
            ->with('success', "Login As {$target->name} aktif selama ".self::DURATION_MINUTES.' menit.')
            ->cookie(
                ApplyUserImpersonation::COOKIE_NAMES[$targetType],
                $plainToken,
                self::DURATION_MINUTES,
                ApplyUserImpersonation::COOKIE_PATHS[$targetType],
                null,
                true,
                true,
                false,
                'lax'
            );
    }

    private function validateTarget(User $administrator, ?User $target, string $targetType): void
    {
        abort_unless(
            ($administrator->hasAnyRole(['Super Admin', 'Admin'])
                || in_array($administrator->role, ['super_admin', 'admin'], true))
            && $administrator->can('impersonate-users'),
            403
        );

        abort_unless($target && $target->is_active !== false, 422, 'Akun tujuan belum tersedia atau tidak aktif.');
        abort_if($administrator->is($target), 422, 'Tidak dapat Login As ke akun sendiri.');

        if ($targetType === 'siswa') {
            abort_unless(
                $target->hasRole('Siswa') || $target->role === 'siswa',
                422,
                'Akun siswa tidak valid.'
            );

            return;
        }

        abort_if(
            $target->hasAnyRole(['Super Admin', 'Admin', 'Operator'])
                || in_array($target->role, ['super_admin', 'admin', 'operator'], true),
            422,
            'Login As ke akun admin atau operator tidak diizinkan.'
        );
    }
}
