<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->string('inactive_reason_code', 40)->nullable()->after('is_active')->index();
            $table->string('inactive_reason')->nullable()->after('inactive_reason_code');
            $table->timestamp('deactivated_at')->nullable()->after('inactive_reason');
        });

        $this->backfillReason('blocked', 'Diblokir oleh admin.', fn ($query) => $query->whereNotNull('hu.blocked_at'));
        $this->backfillReason('guest_expired', 'Masa berlaku akun tamu telah berakhir.', fn ($query) => $query
            ->where('hu.role', 'tamu')
            ->whereNotNull('hu.expired_at')
            ->where('hu.expired_at', '<=', now()));
        $this->backfillReason('user_removed', 'Akun pengguna SIMANSA telah dihapus.', fn ($query) => $query->whereNotNull('u.deleted_at'));
        $this->backfillReason('alumni', 'Siswa telah lulus dan diarsipkan sebagai alumni.', fn ($query) => $query->where('s.status_siswa', 'lulus'));
        $this->backfillReason('mutation', 'Siswa telah mutasi keluar.', fn ($query) => $query->where('s.status_siswa', 'mutasi_keluar'));
        $this->backfillReason('credentials_missing', 'Password SIMANSA belum tersedia untuk sinkronisasi Hotspot.', fn ($query) => $query
            ->where('s.status_siswa', 'aktif')
            ->whereNull('u.deleted_at')
            ->where(function ($password) {
                $password->whereNull('u.encrypted_password')->orWhere('u.encrypted_password', '');
            }));

        DB::table('hotspot_users')
            ->where('is_active', false)
            ->whereNull('inactive_reason_code')
            ->update([
                'inactive_reason_code' => 'manual',
                'inactive_reason' => 'Akun dinonaktifkan secara manual.',
                'deactivated_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->dropIndex(['inactive_reason_code']);
            $table->dropColumn(['inactive_reason_code', 'inactive_reason', 'deactivated_at']);
        });
    }

    private function backfillReason(string $code, string $reason, callable $scope): void
    {
        $query = DB::table('hotspot_users as hu')
            ->leftJoin('users as u', 'u.id', '=', 'hu.user_id')
            ->leftJoin('siswa as s', 's.user_id', '=', 'u.id')
            ->whereNull('hu.deleted_at')
            ->where('hu.is_active', false)
            ->whereNull('hu.inactive_reason_code');

        $scope($query);
        $ids = $query->pluck('hu.id');

        foreach ($ids->chunk(500) as $chunk) {
            DB::table('hotspot_users')->whereIn('id', $chunk)->update([
                'inactive_reason_code' => $code,
                'inactive_reason' => $reason,
                'deactivated_at' => now(),
            ]);
        }
    }
};
