<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HotspotRadiusNas extends Model
{
    protected $table = 'hotspot_radius_nas';

    protected $fillable = [
        'name',
        'nasname',
        'shortname',
        'type',
        'ports',
        'secret',
        'server',
        'community',
        'description',
        'is_active',
        'last_synced_at',
        'sync_status',
        'sync_error',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
        'secret' => 'encrypted',
    ];

    public function syncToRadius(): bool
    {
        $result = $this->syncToRadiusWithLog();

        return $result['success'];
    }

    public function syncToRadiusWithLog(): array
    {
        $steps = [];

        try {
            $steps[] = ['status' => 'running', 'message' => 'Membuka koneksi database FreeRADIUS.'];
            $db = DB::connection('mysql_radius');
            $db->getPdo();
            $steps[] = ['status' => 'success', 'message' => 'Koneksi database FreeRADIUS berhasil.'];

            $steps[] = ['status' => 'running', 'message' => 'Memeriksa tabel nas pada database FreeRADIUS.'];
            if (!$this->radiusTableExists($db, 'nas')) {
                throw new \RuntimeException('Tabel nas tidak ditemukan di database FreeRADIUS.');
            }
            $steps[] = ['status' => 'success', 'message' => 'Tabel nas tersedia.'];

            if (!$this->is_active) {
                $steps[] = ['status' => 'running', 'message' => "Menghapus NAS {$this->nasname} dari FreeRADIUS karena status nonaktif."];
                $db->table('nas')->where('nasname', $this->nasname)->delete();
                $this->markSynced();
                $steps[] = ['status' => 'success', 'message' => 'NAS nonaktif berhasil dihapus dari FreeRADIUS.'];

                return ['success' => true, 'steps' => $steps];
            }

            $steps[] = ['status' => 'running', 'message' => "Menulis/update NAS {$this->nasname} ke tabel nas."];
            $db->table('nas')->updateOrInsert(
                ['nasname' => $this->nasname],
                [
                    'shortname' => $this->shortname ?: $this->name,
                    'type' => $this->type ?: 'mikrotik',
                    'ports' => $this->ports,
                    'secret' => $this->secret ?: '',
                    'server' => $this->server,
                    'community' => $this->community,
                    'description' => $this->description,
                ]
            );

            $this->markSynced();
            $steps[] = ['status' => 'success', 'message' => 'NAS berhasil ditulis ke FreeRADIUS.'];
            $steps[] = ['status' => 'success', 'message' => 'Status sinkronisasi lokal diperbarui.'];

            return ['success' => true, 'steps' => $steps];
        } catch (\Throwable $e) {
            $steps[] = ['status' => 'error', 'message' => $e->getMessage()];
            Log::error('[HotspotRadiusNas] Sync failed', [
                'nasname' => $this->nasname,
                'error' => $e->getMessage(),
            ]);

            $this->update([
                'sync_status' => 'error',
                'sync_error' => $e->getMessage(),
            ]);

            return ['success' => false, 'steps' => $steps, 'error' => $e->getMessage()];
        }
    }

    public function maskedSecret(): string
    {
        if (!$this->secret) {
            return '-';
        }

        return str_repeat('*', max(8, min(16, strlen($this->secret))));
    }

    private function markSynced(): void
    {
        $this->update([
            'last_synced_at' => now(),
            'sync_status' => 'synced',
            'sync_error' => null,
        ]);
    }

    private function radiusTableExists($db, string $table): bool
    {
        return $db->getSchemaBuilder()->hasTable($table);
    }
}
