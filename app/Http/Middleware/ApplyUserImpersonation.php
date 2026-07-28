<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserImpersonation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApplyUserImpersonation
{
    public const COOKIE_NAMES = [
        'siswa' => 'simansa_siswa_impersonation',
        'gtk' => 'simansa_gtk_impersonation',
    ];

    public const COOKIE_PATHS = [
        'siswa' => '/siswa',
        'gtk' => '/admin/gtk',
    ];

    public function handle(Request $request, Closure $next, string $targetType): Response
    {
        abort_unless(isset(self::COOKIE_NAMES[$targetType]), 500, 'Tipe impersonasi tidak dikenal.');

        /** @var User|null $sessionUser */
        $sessionUser = Auth::user();
        $token = $request->cookie(self::COOKIE_NAMES[$targetType]);

        if (! $token) {
            return $next($request);
        }

        $impersonation = UserImpersonation::query()
            ->with(['impersonator', 'targetUser.siswa', 'targetUser.gtk'])
            ->where('token_hash', hash('sha256', $token))
            ->where('target_type', $targetType)
            ->whereNull('ended_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $impersonation || ! $sessionUser
            || $sessionUser->getKey() !== $impersonation->impersonator_id
            || ! $this->isAuthorizedAdministrator($sessionUser)) {
            return $next($request);
        }

        $target = $impersonation->targetUser;
        abort_unless($target && $target->is_active !== false, 403, 'Akun tujuan tidak aktif.');
        abort_unless($this->matchesTargetType($target, $targetType), 403, 'Akun tujuan tidak sesuai.');

        if ($this->isSecurityRoute($request, $targetType)) {
            abort(403, 'Perubahan password dinonaktifkan selama mode Login As.');
        }

        $impersonation->forceFill(['last_used_at' => now()])->save();

        $request->attributes->set('impersonation', $impersonation);
        $request->attributes->set('impersonator', $sessionUser);
        $request->setUserResolver(fn () => $target);
        Auth::guard('web')->setUser($target);

        try {
            return $next($request);
        } finally {
            Auth::guard('web')->setUser($sessionUser);
            $request->setUserResolver(fn () => $sessionUser);
        }
    }

    private function isAuthorizedAdministrator(User $user): bool
    {
        $isAdminRole = $user->hasAnyRole(['Super Admin', 'Admin'])
            || in_array($user->role, ['super_admin', 'admin'], true);

        return $isAdminRole && $user->can('impersonate-users');
    }

    private function matchesTargetType(User $user, string $targetType): bool
    {
        return match ($targetType) {
            'siswa' => $user->siswa !== null
                && ($user->hasRole('Siswa') || $user->role === 'siswa'),
            'gtk' => $user->gtk !== null
                && ! $user->hasAnyRole(['Super Admin', 'Admin', 'Operator']),
            default => false,
        };
    }

    private function isSecurityRoute(Request $request, string $targetType): bool
    {
        $routeName = (string) $request->route()?->getName();

        if ($targetType === 'siswa') {
            return str_starts_with($routeName, 'siswa.force-setup')
                || str_starts_with($routeName, 'siswa.profile.password')
                || str_starts_with($routeName, 'siswa.profile.change-password');
        }

        return str_starts_with($routeName, 'admin.gtk.profile.password');
    }
}
