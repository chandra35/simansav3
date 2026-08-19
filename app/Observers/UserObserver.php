<?php

namespace App\Observers;

use App\Models\User;
use App\Models\HotspotUser;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Ketika user diupdate (termasuk reset password).
     * Sync ke RADIUS jika encrypted_password berubah.
     */
    public function updated(User $user): void
    {
        // Cek apakah encrypted_password berubah
        if (!$user->wasChanged('encrypted_password')) {
            return;
        }

        $hotspotUser = HotspotUser::where('user_id', $user->id)->first();

        if (!$hotspotUser) {
            return;
        }

        $plainPassword = $this->decryptPassword($user);
        if ($plainPassword === null) {
            return;
        }

        $hotspotUser->loadMissing('user.siswa');
        if ($hotspotUser->isEligibleForRadius() && $hotspotUser->isSecurePassword($plainPassword)) {
            $hotspotUser->update([
                'is_active' => true,
                'inactive_reason_code' => null,
                'inactive_reason' => null,
                'deactivated_at' => null,
                'sync_status' => 'pending',
                'sync_error' => null,
            ]);
        }

        Log::info('[UserObserver] Password changed, syncing to RADIUS', [
            'user_id' => $user->id,
            'hotspot_username' => $hotspotUser->username,
        ]);

        $hotspotUser->syncToRadius($plainPassword);
    }

    private function decryptPassword(User $user): ?string
    {
        if (empty($user->encrypted_password)) {
            return null;
        }

        try {
            return Crypt::decryptString($user->encrypted_password);
        } catch (\Exception $e) {
            Log::warning('[UserObserver] Cannot decrypt password', ['user_id' => $user->id]);
            return null;
        }
    }
}
