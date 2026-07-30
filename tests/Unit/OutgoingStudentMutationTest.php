<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class OutgoingStudentMutationTest extends TestCase
{
    public function test_destination_school_is_optional_when_creating_or_editing_outgoing_mutation(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/MutasiSiswaController.php');
        $createView = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/mutasi-siswa/create.blade.php');
        $editView = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/mutasi-siswa/edit.blade.php');

        $this->assertSame(
            2,
            substr_count($controller, "\$rules['sekolah_tujuan'] = 'nullable|string|max:200';")
        );
        $this->assertStringNotContainsString(
            "\$rules['sekolah_tujuan'] = 'required|string|max:200';",
            $controller
        );
        $this->assertStringContainsString('Sekolah tujuan boleh dikosongkan.', $createView);
        $this->assertStringNotContainsString('Nama sekolah tujuan wajib diisi', $createView);
        $this->assertStringNotContainsString(
            'value="{{ old(\'sekolah_tujuan\', $mutasiSiswa->sekolah_tujuan) }}" required',
            $editView
        );
    }

    public function test_approval_closes_active_class_history_and_disables_student_access(): void
    {
        $model = file_get_contents(dirname(__DIR__, 2).'/app/Models/MutasiSiswa.php');

        $this->assertStringContainsString("->where('status', 'aktif')", $model);
        $this->assertStringContainsString("'status' => 'keluar'", $model);
        $this->assertStringContainsString("'tanggal_keluar' => \$this->tanggal_mutasi ?? today()", $model);
        $this->assertStringContainsString("'status_siswa' => 'mutasi_keluar'", $model);
        $this->assertStringContainsString("'kelas_saat_ini_id' => null", $model);
        $this->assertStringContainsString("updateOrFail(['is_active' => false])", $model);
        $this->assertStringContainsString("->update(['is_online' => false])", $model);
    }

    public function test_mutation_list_displays_class_origin_and_new_outgoing_mutations_snapshot_it(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/MutasiSiswaController.php');
        $model = file_get_contents(dirname(__DIR__, 2).'/app/Models/MutasiSiswa.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/mutasi-siswa/index.blade.php');

        $this->assertStringContainsString("'siswa.siswaKelasRecords.kelas'", $controller);
        $this->assertStringContainsString("\$validated['kelas_asal'] = \$siswa->kelasSaatIni?->nama_lengkap", $controller);
        $this->assertStringContainsString('function getAsalKelasAttribute()', $model);
        $this->assertStringContainsString('Asal kelas: {{ $mutasi->asalKelas }}', $view);
    }

    public function test_approval_is_locked_audited_and_has_a_native_post_fallback(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/MutasiSiswaController.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/mutasi-siswa/show.blade.php');

        $this->assertStringContainsString('->lockForUpdate()', $controller);
        $this->assertStringContainsString('DB::transaction(function ()', $controller);
        $this->assertStringContainsString("'before' => \$before", $controller);
        $this->assertStringContainsString("'after' => [", $controller);
        $this->assertSame(1, substr_count($view, "@section('content')"));
        $this->assertSame(1, substr_count($view, "@section('js')"));
        $this->assertSame(1, substr_count($view, 'id="btnApprove"'));
        $this->assertStringContainsString('id="formApproveMutation"', $view);
        $this->assertStringContainsString("@section('plugins.Sweetalert2', true)", $view);
        $this->assertStringContainsString("typeof window.Swal === 'undefined'", $view);
        $this->assertStringContainsString('window.confirm(', $view);
        $this->assertStringContainsString('fetch(form.action, {', $view);
        $this->assertStringContainsString("'Accept': 'application/json'", $view);
    }

    public function test_outgoing_mutation_uses_clear_student_results_reason_options_and_editable_date(): void
    {
        $config = file_get_contents(dirname(__DIR__, 2).'/config/simansa.php');
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/MutasiSiswaController.php');
        $createView = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/mutasi-siswa/create.blade.php');
        $editView = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/mutasi-siswa/edit.blade.php');

        foreach ([
            'Ikut pindah orang tua',
            'Pelanggaran disiplin',
            'Kurang perhatian orang tua',
            'Pengaruh teman/lingkungan',
            'Hilang/Tidak ada kabar',
            'Lainnya',
        ] as $reason) {
            $this->assertStringContainsString("'{$reason}'", $config);
        }
        $this->assertStringContainsString("Rule::in(config('simansa.alasan_mutasi_keluar', []))", $controller);
        $this->assertStringContainsString('name="alasan_mutasi_keluar"', $createView);
        $this->assertStringNotContainsString('<textarea name="alasan_mutasi_keluar"', $createView);
        $this->assertStringNotContainsString('<textarea name="alasan_mutasi_keluar"', $editView);
        $this->assertStringContainsString('id="tanggal_mutasi_keluar"', $createView);
        $this->assertStringContainsString('id="tanggal_mutasi_masuk"', $createView);
        $this->assertStringContainsString('name="tanggal_mutasi" id="final_tanggal_mutasi"', $createView);
        $this->assertStringContainsString('class="s2-student-name"', $createView);
        $this->assertStringContainsString('.select2-results__option--highlighted .s2-student-meta', $createView);
    }
}
