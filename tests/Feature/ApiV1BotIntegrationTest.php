<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiV1BotIntegrationTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Find or create an admin user for testing
        $this->user = User::where('is_active', true)->first();
        if ($this->user) {
            Sanctum::actingAs($this->user, ['*']);
        }
    }

    public function test_auth_me_returns_profile(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found');
        }

        $response = $this->getJson('/api/v1/auth/me');
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name', 'username'],
            ]);
    }

    public function test_siswa_endpoints_return_data_and_whatsapp_format(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found');
        }

        $response = $this->getJson('/api/v1/siswa');
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'message', 'data', 'whatsapp_text']);

        $stats = $this->getJson('/api/v1/siswa/stats/summary');
        $stats->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'data' => ['total_aktif', 'gender', 'per_tingkat'], 'whatsapp_text']);
    }

    public function test_gtk_endpoints_return_data_and_whatsapp_format(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found');
        }

        $response = $this->getJson('/api/v1/gtk');
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'message', 'data', 'whatsapp_text']);

        $stats = $this->getJson('/api/v1/gtk/stats/summary');
        $stats->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'data' => ['total_aktif', 'kategori', 'sertifikasi'], 'whatsapp_text']);
    }

    public function test_jadwal_endpoints_return_data_and_whatsapp_format(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found');
        }

        $response = $this->getJson('/api/v1/jadwal/hari-ini?hari=senin');
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'data' => ['hari', 'jadwal'], 'whatsapp_text']);
    }

    public function test_presensi_and_sholat_endpoints(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found');
        }

        $sholat = $this->getJson('/api/v1/presensi/sholat/summary');
        $sholat->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'data' => ['tanggal', 'rekap'], 'whatsapp_text']);

        $statusKelas = $this->getJson('/api/v1/presensi/sholat/status-kelas');
        $statusKelas->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'data' => ['total_kelas', 'sudah_input', 'belum_input'], 'whatsapp_text']);

        $harian = $this->getJson('/api/v1/presensi/harian');
        $harian->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'data' => ['tanggal', 'gtk', 'siswa'], 'whatsapp_text']);
    }

    public function test_surat_endpoints(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found');
        }

        $suratKeterangan = $this->getJson('/api/v1/surat/keterangan');
        $suratKeterangan->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'message', 'data', 'whatsapp_text']);

        $suratMasuk = $this->getJson('/api/v1/surat/masuk');
        $suratMasuk->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'message', 'data', 'whatsapp_text']);
    }
}
