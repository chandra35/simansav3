<?php

namespace App\Services;

use App\Models\FaceEncoding;
use Illuminate\Support\Collection;

class FaceDescriptorService
{
    public function forType(string $userType, bool $verifiedOnly = true): Collection
    {
        $query = FaceEncoding::query()
            ->where('user_type', $userType)
            ->where('is_active', true)
            ->whereHas('user', function ($user) use ($userType) {
                $user->where('is_active', true)
                    ->when($userType === 'gtk', fn ($query) => $query->whereHas('gtk', fn ($gtk) => $gtk->where('status_aktif', true)))
                    ->when($userType === 'siswa', fn ($query) => $query->whereHas('siswa', fn ($siswa) => $siswa->where('status_siswa', 'aktif')));
            });

        if ($verifiedOnly) {
            $query->where('is_verified', true);
        }

        return $query->with([
            'user:id,name',
            'user.gtk:id,user_id,nama_lengkap,nip,foto_profile',
            'user.siswa:id,user_id,nama_lengkap,nisn,foto_profile',
        ])->get()->map(function (FaceEncoding $face) {
            $gtk = $face->user->gtk ?? null;
            $siswa = $face->user->siswa ?? null;
            $profile = $face->user_type === 'gtk' ? $gtk : $siswa;

            return [
                'user_id' => $face->user_id,
                'user_type' => $face->user_type,
                'name' => $profile?->nama_lengkap ?? $face->user->name ?? 'Unknown',
                'identifier' => $face->user_type === 'gtk' ? $gtk?->nip : $siswa?->nisn,
                'foto' => $profile?->foto_profile_url,
                'descriptors' => $face->descriptors,
            ];
        });
    }
}
