<?php

namespace Tests\Feature;

use App\Models\Asrama;
use App\Models\AsramaAsatidz;
use App\Models\AsramaKelas;
use App\Models\AsramaKelasSantri;
use App\Models\AsramaKomponenNilai;
use App\Models\AsramaMapel;
use App\Models\AsramaNilai;
use App\Models\AsramaPengampu;
use App\Models\AsramaSantri;
use App\Models\Gtk;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AsramaKomponenNilaiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('manage-asrama-komponen-nilai', 'web');
        Permission::findOrCreate('input-nilai-asrama', 'web');
        Permission::findOrCreate('manage-asrama-pengampu', 'web');
        Permission::findOrCreate('manage-asrama', 'web');
    }

    public function test_can_manage_komponen_nilai_crud(): void
    {
        $admin = User::factory()->create(['username' => 'test_admin_'.Str::random(6)]);
        $admin->givePermissionTo('manage-asrama-komponen-nilai');

        $year = TahunPelajaran::first() ?? TahunPelajaran::create([
            'nama' => '2026/2027',
            'tahun_mulai' => 2026,
            'tahun_selesai' => 2027,
            'semester_aktif' => 'Ganjil',
            'is_active' => true,
        ]);

        // Store
        $response = $this->actingAs($admin)->post(route('asrama.komponen-nilai.store'), [
            'tahun_pelajaran_id' => $year->id,
            'kode' => 'ATS',
            'nama' => 'Asesmen Tengah Semester',
            'bobot' => 30,
            'urutan' => 1,
            'is_active' => '1',
            'keterangan' => 'Tes tengah semester',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('asrama_komponen_nilai', [
            'tahun_pelajaran_id' => $year->id,
            'kode' => 'ATS',
            'nama' => 'Asesmen Tengah Semester',
            'bobot' => 30.00,
        ]);

        $component = AsramaKomponenNilai::where('tahun_pelajaran_id', $year->id)->where('kode', 'ATS')->first();

        // Update
        $updateResponse = $this->actingAs($admin)->put(route('asrama.komponen-nilai.update', $component), [
            'kode' => 'ATS',
            'nama' => 'Asesmen Tengah Semester Terpadu',
            'bobot' => 35,
            'urutan' => 2,
            'is_active' => '1',
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('asrama_komponen_nilai', [
            'id' => $component->id,
            'nama' => 'Asesmen Tengah Semester Terpadu',
            'bobot' => 35.00,
        ]);

        // Destroy
        $destroyResponse = $this->actingAs($admin)->delete(route('asrama.komponen-nilai.destroy', $component));
        $destroyResponse->assertRedirect();
        $this->assertSoftDeleted('asrama_komponen_nilai', [
            'id' => $component->id,
        ]);
    }

    public function test_can_copy_components_across_academic_years(): void
    {
        $admin = User::factory()->create(['username' => 'test_admin_'.Str::random(6)]);
        $admin->givePermissionTo('manage-asrama-komponen-nilai');

        $kurikulumId = \App\Models\Kurikulum::first()?->id ?? (string) Str::uuid();

        $sourceYear = TahunPelajaran::create([
            'nama' => '2024/2025 Test',
            'tahun_mulai' => 2024,
            'tahun_selesai' => 2025,
            'tanggal_mulai' => '2024-07-15',
            'tanggal_selesai' => '2025-06-20',
            'semester_aktif' => 'Ganjil',
            'is_active' => false,
            'kurikulum_id' => $kurikulumId,
        ]);

        $targetYear = TahunPelajaran::create([
            'nama' => '2025/2026 Test',
            'tahun_mulai' => 2025,
            'tahun_selesai' => 2026,
            'tanggal_mulai' => '2025-07-15',
            'tanggal_selesai' => '2026-06-20',
            'semester_aktif' => 'Ganjil',
            'is_active' => true,
            'kurikulum_id' => $kurikulumId,
        ]);

        AsramaKomponenNilai::create([
            'tahun_pelajaran_id' => $sourceYear->id,
            'kode' => 'HARIAN',
            'nama' => 'Nilai Harian',
            'bobot' => 30,
            'urutan' => 1,
            'is_active' => true,
        ]);

        AsramaKomponenNilai::create([
            'tahun_pelajaran_id' => $sourceYear->id,
            'kode' => 'AAS',
            'nama' => 'Asesmen Akhir Semester',
            'bobot' => 70,
            'urutan' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('asrama.komponen-nilai.copy'), [
            'from_tahun_pelajaran_id' => $sourceYear->id,
            'to_tahun_pelajaran_id' => $targetYear->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('asrama_komponen_nilai', [
            'tahun_pelajaran_id' => $targetYear->id,
            'kode' => 'HARIAN',
        ]);
        $this->assertDatabaseHas('asrama_komponen_nilai', [
            'tahun_pelajaran_id' => $targetYear->id,
            'kode' => 'AAS',
        ]);
    }

    public function test_dynamic_grade_input_calculates_weighted_score_and_stores_detail(): void
    {
        $student = Siswa::whereNotNull('user_id')->first();
        $teacher = Gtk::whereNotNull('user_id')->first();
        $year = TahunPelajaran::first();
        if (! $student || ! $teacher || ! $year) {
            $this->markTestSkipped('Master siswa, GTK, dan tahun pelajaran diperlukan.');
        }

        $user = $teacher->user;
        $user->givePermissionTo(['input-nilai-asrama', 'manage-asrama-pengampu']);

        $suffix = strtoupper(Str::random(6));
        $unit = Asrama::create(['kode' => 'T'.$suffix, 'nama' => 'Asrama Unit Test', 'jenis' => 'putra']);
        $santri = AsramaSantri::create([
            'asrama_id' => $unit->id,
            'siswa_id' => $student->id,
            'nomor_induk_asrama' => 'S'.$suffix,
            'status' => 'aktif',
        ]);
        $asatidz = AsramaAsatidz::create([
            'asrama_id' => $unit->id,
            'gtk_id' => $teacher->id,
            'jabatan' => 'Asatidz',
            'is_active' => true,
        ]);
        $class = AsramaKelas::create([
            'asrama_id' => $unit->id,
            'tahun_pelajaran_id' => $year->id,
            'nama_kelas' => 'Kelas Dinamis '.$suffix,
            'jenis' => 'putra',
            'wali_asatidz_id' => $asatidz->id,
            'kapasitas' => 30,
            'is_active' => true,
        ]);
        $membership = AsramaKelasSantri::create([
            'asrama_kelas_id' => $class->id,
            'asrama_santri_id' => $santri->id,
            'status' => 'aktif',
        ]);
        $subject = AsramaMapel::create([
            'kode' => 'N'.$suffix,
            'nama_latin' => 'Hadits',
            'nama_arab' => 'الحديث',
            'skala_maksimum' => 100,
            'urutan' => 1,
            'is_active' => true,
        ]);
        $assignment = AsramaPengampu::create([
            'asrama_kelas_id' => $class->id,
            'asrama_mapel_id' => $subject->id,
            'asrama_asatidz_id' => $asatidz->id,
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        // Define 2 active components for this academic year: Formatif (40%), Sumatif (60%)
        AsramaKomponenNilai::create([
            'tahun_pelajaran_id' => $year->id,
            'kode' => 'FORMATIF',
            'nama' => 'Formatif',
            'bobot' => 40,
            'urutan' => 1,
            'is_active' => true,
        ]);
        AsramaKomponenNilai::create([
            'tahun_pelajaran_id' => $year->id,
            'kode' => 'SUMATIF',
            'nama' => 'Sumatif',
            'bobot' => 60,
            'urutan' => 2,
            'is_active' => true,
        ]);

        // Formatif = 80, Sumatif = 90
        // Expected weighted final score = (80 * 40 + 90 * 60) / 100 = (3200 + 5400) / 100 = 86.00
        $response = $this->actingAs($user)->put(route('asrama.nilai.update', $assignment), [
            'detail_nilai' => [
                $membership->id => [
                    'FORMATIF' => 80,
                    'SUMATIF' => 90,
                ],
            ],
            'catatan' => [
                $membership->id => 'Sangat aktif dalam hafalan',
            ],
        ]);

        $response->assertRedirect();

        $savedNilai = AsramaNilai::where('asrama_pengampu_id', $assignment->id)
            ->where('asrama_kelas_santri_id', $membership->id)
            ->first();

        $this->assertNotNull($savedNilai);
        $this->assertEquals(86.00, (float) $savedNilai->nilai);
        $this->assertIsArray($savedNilai->detail_nilai);
        $this->assertEquals(80, $savedNilai->detail_nilai['FORMATIF']);
        $this->assertEquals(90, $savedNilai->detail_nilai['SUMATIF']);
    }

    public function test_ensure_defaults_for_year_automatically_generates_standard_components(): void
    {
        $kurikulumId = TahunPelajaran::value('kurikulum_id') ?? (string) Str::uuid();
        $newYear = TahunPelajaran::create([
            'nama' => '2030/2031',
            'tahun_mulai' => 2030,
            'tahun_selesai' => 2031,
            'tanggal_mulai' => '2030-07-15',
            'tanggal_selesai' => '2031-06-20',
            'semester_aktif' => 'Ganjil',
            'kurikulum_id' => $kurikulumId,
            'is_active' => false,
        ]);

        $components = AsramaKomponenNilai::ensureDefaultsForYear($newYear->id);

        $this->assertCount(3, $components);
        $this->assertEquals(['HARIAN', 'ATS', 'AAS'], $components->pluck('kode')->all());
        $this->assertEquals(100.00, (float) $components->sum('bobot'));

        // Calling again should return existing without duplicating
        $secondCall = AsramaKomponenNilai::ensureDefaultsForYear($newYear->id);
        $this->assertCount(3, $secondCall);
    }

    public function test_admin_can_apply_default_template(): void
    {
        $admin = User::first();
        $admin->givePermissionTo(['manage-asrama-komponen-nilai']);

        $year = TahunPelajaran::first();

        $response = $this->actingAs($admin)->post(route('asrama.komponen-nilai.apply-template'), [
            'tahun_pelajaran_id' => $year->id,
            'force' => true,
        ]);

        $response->assertRedirect(route('asrama.komponen-nilai.index', ['tahun_pelajaran_id' => $year->id]));
        $this->assertDatabaseHas('asrama_komponen_nilai', [
            'tahun_pelajaran_id' => $year->id,
            'kode' => 'HARIAN',
            'bobot' => 30.00,
        ]);
        $this->assertDatabaseHas('asrama_komponen_nilai', [
            'tahun_pelajaran_id' => $year->id,
            'kode' => 'ATS',
            'bobot' => 30.00,
        ]);
        $this->assertDatabaseHas('asrama_komponen_nilai', [
            'tahun_pelajaran_id' => $year->id,
            'kode' => 'AAS',
            'bobot' => 40.00,
        ]);
    }
}
