<?php

namespace Tests\Feature;

use App\Models\Asrama;
use App\Models\AsramaAsatidz;
use App\Models\AsramaKelas;
use App\Models\AsramaKelasSantri;
use App\Models\AsramaMapel;
use App\Models\AsramaNilai;
use App\Models\AsramaPengampu;
use App\Models\AsramaRapor;
use App\Models\AsramaSantri;
use App\Models\Gtk;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use App\Services\AsramaAccessService;
use App\Services\AsramaRaporService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class AsramaWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_complete_asrama_score_and_report_snapshot_flow(): void
    {
        $student = Siswa::whereNotNull('user_id')->first();
        $teacher = Gtk::whereNotNull('user_id')->first();
        $year = TahunPelajaran::first();
        if (! $student || ! $teacher || ! $year) {
            $this->markTestSkipped('Master siswa, GTK, dan tahun pelajaran diperlukan.');
        }

        $suffix = strtoupper(Str::random(7));
        $unit = Asrama::create(['kode' => 'T'.$suffix, 'nama' => 'Asrama Test', 'jenis' => 'campuran']);
        $santri = AsramaSantri::create([
            'asrama_id' => $unit->id,
            'siswa_id' => $student->id,
            'nomor_induk_asrama' => 'N'.$suffix,
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
            'nama_kelas' => 'Kelas '.$suffix,
            'jenis' => 'campuran',
            'wali_asatidz_id' => $asatidz->id,
            'kapasitas' => 20,
            'is_active' => true,
        ]);
        $membership = AsramaKelasSantri::create([
            'asrama_kelas_id' => $class->id,
            'asrama_santri_id' => $santri->id,
            'status' => 'aktif',
        ]);
        $subject = AsramaMapel::create([
            'kode' => 'M'.$suffix,
            'nama_latin' => 'Nahwu',
            'nama_arab' => 'النحو',
            'skala_maksimum' => 10,
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
        AsramaNilai::create([
            'asrama_pengampu_id' => $assignment->id,
            'asrama_kelas_santri_id' => $membership->id,
            'nilai' => 8.5,
        ]);
        $rapor = AsramaRapor::create([
            'asrama_kelas_santri_id' => $membership->id,
            'semester' => 'Ganjil',
            'nilai_kebersihan' => 8,
            'status' => 'draft',
        ]);

        $snapshot = app(AsramaRaporService::class)->publishSnapshot($rapor);

        $this->assertSame('Nahwu', $snapshot['scores'][0]['nama_latin']);
        $this->assertSame('النحو', $snapshot['scores'][0]['nama_arab']);
        $this->assertSame(8.5, $snapshot['scores'][0]['nilai']);
        $this->assertSame('ثماني ونصف', $snapshot['scores'][0]['nilai_arab']);
        $this->assertSame(8.5, $snapshot['summary']['rata_rata']);

        $html = view('asrama.rapor.print', [
            'rapor' => $rapor,
            'report' => $snapshot,
            'service' => app(AsramaRaporService::class),
        ])->render();
        $this->assertStringContainsString('كشف الدرجات', $html);
        $this->assertStringContainsString('Noto Naskh Arabic', $html);

        $access = app(AsramaAccessService::class);
        $access->syncStudent($student->user);
        $access->syncGtk($teacher->user);
        $this->assertTrue($student->user->fresh()->can('view-asrama-portal'));
        $this->assertTrue($teacher->user->fresh()->can('input-nilai-asrama'));
        $this->actingAs($student->user)->get(route('asrama.dashboard'))->assertOk();
        $this->actingAs($teacher->user)->get(route('asrama.nilai.index'))->assertOk();
    }

    public function test_asrama_management_pages_render_for_authorized_admin(): void
    {
        $admin = User::role('Super Admin')->first() ?: User::role('Admin')->first();
        if (! $admin) {
            $this->markTestSkipped('Akun Admin diperlukan untuk pengujian halaman.');
        }

        $this->actingAs($admin)->get(route('asrama.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('asrama.operator.index'))->assertOk();
        $this->actingAs($admin)->get(route('asrama.kelas.index'))->assertOk();
        $this->actingAs($admin)->get(route('asrama.kamar.index'))->assertOk();
        $this->actingAs($admin)->get(route('asrama.nilai.index'))->assertOk();
        $this->actingAs($admin)->get(route('asrama.rapor.index'))->assertOk();
    }

    public function test_gtk_with_asrama_assignment_cannot_be_deleted(): void
    {
        $admin = User::permission('delete-gtk')->first();
        $teacher = Gtk::whereNotNull('user_id')
            ->when($admin, fn ($query) => $query->where('user_id', '!=', $admin->id))
            ->first();
        if (! $admin || ! $teacher) {
            $this->markTestSkipped('Akun penghapus dan GTK diperlukan.');
        }

        $suffix = strtoupper(Str::random(7));
        $unit = Asrama::create(['kode' => 'D'.$suffix, 'nama' => 'Asrama Delete Test', 'jenis' => 'campuran']);
        AsramaAsatidz::create([
            'asrama_id' => $unit->id,
            'gtk_id' => $teacher->id,
            'jabatan' => 'Asatidz',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->withSession(['_token' => 'asrama-delete-test'])
            ->deleteJson(route('admin.gtk.destroy', $teacher), ['_token' => 'asrama-delete-test'])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'GTK masih terdaftar pada tim Asrama. Lepas seluruh tugas dan hapus GTK dari menu Pengasuh & Pengajar Asrama terlebih dahulu.');

        $this->assertNull($teacher->fresh()->deleted_at);
    }

    public function test_asatidz_can_read_soft_deleted_gtk_history(): void
    {
        $teacher = Gtk::whereNotNull('user_id')->first();
        if (! $teacher) {
            $this->markTestSkipped('GTK diperlukan.');
        }

        $suffix = strtoupper(Str::random(7));
        $unit = Asrama::create(['kode' => 'H'.$suffix, 'nama' => 'Asrama History Test', 'jenis' => 'campuran']);
        $asatidz = AsramaAsatidz::create([
            'asrama_id' => $unit->id,
            'gtk_id' => $teacher->id,
            'jabatan' => 'Asatidz',
            'is_active' => false,
        ]);

        $teacher->delete();

        $this->assertTrue($asatidz->fresh()->gtk->trashed());
        $this->assertSame($teacher->nama_lengkap, $asatidz->fresh()->gtk->nama_lengkap);
    }
}
