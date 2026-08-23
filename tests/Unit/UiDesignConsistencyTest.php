<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class UiDesignConsistencyTest extends TestCase
{
    private function file(string $relative): string
    {
        return file_get_contents(dirname(__DIR__, 2).'/'.$relative);
    }

    public function test_documentation_requires_the_data_siswa_page_structure(): void
    {
        foreach (['MAN1METRO.md', 'docs/UI_DESIGN_PRINCIPLES_UPDATED.md'] as $file) {
            $contents = $this->file($file);

            $this->assertStringContainsString('card bg-gradient-primary text-white', $contents);
            $this->assertStringContainsString('card-outline card-primary', $contents);
            $this->assertStringContainsString('content_header', $contents);
            $this->assertStringContainsString('breadcrumb', $contents);
        }
    }

    public function test_requested_operational_pages_use_one_continuous_hero(): void
    {
        $views = [
            'resources/views/admin/gtk/wali/siswa/index.blade.php',
            'resources/views/admin/gtk/wali/absensi/index.blade.php',
            'resources/views/admin/gtk/wali/absensi/rekap.blade.php',
            'resources/views/admin/cetak/id-card-siswa-index.blade.php',
        ];

        foreach ($views as $view) {
            $contents = $this->file($view);

            $this->assertStringContainsString('class="row mb-2"', $contents);
            $this->assertStringContainsString('class="breadcrumb float-sm-right"', $contents);
            $this->assertStringContainsString('card bg-gradient-primary text-white mb-4', $contents);
            $this->assertStringContainsString('card card-outline card-primary', $contents);
            $this->assertStringNotContainsString('class="simansa-hero', $contents);
        }
    }

    public function test_student_and_parent_phone_numbers_are_clickable(): void
    {
        $studentShow = $this->file('resources/views/admin/siswa/show.blade.php');
        $studentIndex = $this->file('resources/views/admin/siswa/index.blade.php');
        $waliShow = $this->file('resources/views/admin/gtk/wali/siswa/show.blade.php');

        $this->assertGreaterThanOrEqual(3, substr_count($studentShow, 'href="tel:'));
        $this->assertStringContainsString('function renderPhoneLink(value, label)', $studentIndex);
        $this->assertSame(4, substr_count($studentIndex, 'renderPhoneLink('));
        $this->assertGreaterThanOrEqual(3, substr_count($waliShow, 'href="tel:'));
        $this->assertStringContainsString('$siswa->ortu->hp_ayah', $waliShow);
        $this->assertStringContainsString('$siswa->ortu->hp_ibu', $waliShow);
        $this->assertStringNotContainsString('$siswa->ortu->no_hp_ayah', $waliShow);
        $this->assertStringNotContainsString('$siswa->ortu->no_hp_ibu', $waliShow);
    }

    public function test_phone_links_suppress_the_global_navigation_overlay(): void
    {
        $master = $this->file('resources/views/vendor/adminlte/master.blade.php');

        $this->assertMatchesRegularExpression(
            "/href\.startsWith\('mailto:'\) \|\| href\.startsWith\('tel:'\)\) \{\s*suppressOverlayForNonNavigation\(\);/s",
            $master
        );
    }

    public function test_wali_student_detail_is_loaded_in_a_responsive_modal(): void
    {
        $index = $this->file('resources/views/admin/gtk/wali/siswa/index.blade.php');
        $controller = $this->file('app/Http/Controllers/Admin/WaliKelas/SiswaController.php');
        $partial = $this->file('resources/views/admin/gtk/wali/siswa/partials/detail.blade.php');

        $this->assertStringContainsString('btn-detail-siswa', $index);
        $this->assertStringContainsString('targets:[0, 9]', $index);
        $this->assertStringContainsString('event.stopPropagation();', $index);
        $this->assertStringContainsString('modal-dialog-scrollable', $index);
        $this->assertStringContainsString("dataType: 'json'", $index);
        $this->assertStringContainsString('request()->ajax()', $controller);
        $this->assertStringContainsString("wali.siswa.partials.detail", $controller);
        $this->assertGreaterThanOrEqual(2, substr_count($partial, 'href="tel:'));
        $this->assertStringContainsString("data_get(\$siswa->ortu, 'hp_'.\$key)", $partial);
        $this->assertGreaterThanOrEqual(3, substr_count($partial, 'data-no-overlay'));
    }

    public function test_wali_student_page_matches_admin_student_management_structure(): void
    {
        $index = $this->file('resources/views/admin/gtk/wali/siswa/index.blade.php');
        $controller = $this->file('app/Http/Controllers/Admin/WaliKelas/SiswaController.php');

        $this->assertStringContainsString("'data_lengkap'", $controller);
        $this->assertSame(4, substr_count($index, "'description' =>"));
        $this->assertStringContainsString('simansa-filter-panel', $index);
        $this->assertStringContainsString('filterJenisKelamin', $index);
        $this->assertStringContainsString('filterStatusData', $index);
        foreach (['EMIS', 'Keberadaan', 'Tgl Masuk'] as $column) {
            $this->assertStringContainsString($column, $index);
        }
        $this->assertStringNotContainsString('Verval', $index);
        $this->assertStringNotContainsString('Verval Ijazah', $this->file('resources/views/admin/gtk/wali/siswa/partials/detail.blade.php'));
    }

    public function test_wali_student_modal_contains_complete_read_only_tabs(): void
    {
        $partial = $this->file('resources/views/admin/gtk/wali/siswa/partials/detail.blade.php');
        $controller = $this->file('app/Http/Controllers/Admin/WaliKelas/SiswaController.php');

        foreach (['Data Siswa', 'Data Diri', 'Orang Tua', 'Sekolah Asal', 'Dokumen', 'Catatan'] as $tab) {
            $this->assertStringContainsString($tab, $partial);
        }
        foreach (['ortu.provinsi', 'provinsiSiswa', 'kelasTahunAktif', "'dokumen' =>"] as $relation) {
            $this->assertStringContainsString($relation, $controller);
        }
        $this->assertStringNotContainsString('readable_password', $partial);
    }

    public function test_gtk_operational_feedback_uses_sweetalert2(): void
    {
        $views = [
            'resources/views/admin/gtk/wali/siswa/index.blade.php',
            'resources/views/admin/gtk/wali/absensi/index.blade.php',
            'resources/views/admin/gtk/wali/catatan/index.blade.php',
            'resources/views/admin/gtk/profile/index.blade.php',
            'resources/views/admin/gtk/import.blade.php',
        ];

        foreach ($views as $view) {
            $contents = $this->file($view);
            $this->assertStringContainsString('Swal.fire', $contents);
            $this->assertDoesNotMatchRegularExpression('/\b(?:alert|confirm)\s*\(/', $contents);
        }
    }

    public function test_textareas_remain_vertically_resizable(): void
    {
        $css = $this->file('public/css/custom-compact.css');

        preg_match('/\.form-control,\s*\.form-select,\s*select\.form-control\s*\{([^}]*)\}/s', $css, $sharedFormRule);
        $this->assertNotEmpty($sharedFormRule);
        $this->assertDoesNotMatchRegularExpression('/(?:^|;)\s*height\s*:/', $sharedFormRule[1]);
        $this->assertMatchesRegularExpression('/textarea\.form-control\s*\{\s*resize:\s*vertical;\s*\}/', $css);
        $this->assertMatchesRegularExpression('/input\.form-control,\s*\.form-select,\s*select\.form-control\s*\{[^}]*height:\s*auto\s*!important;/s', $css);
    }
}
