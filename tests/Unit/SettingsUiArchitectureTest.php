<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SettingsUiArchitectureTest extends TestCase
{
    private function settingsView(): string
    {
        return file_get_contents(__DIR__.'/../../resources/views/admin/settings/edit.blade.php');
    }

    public function test_school_information_is_shown_before_branding_and_letterhead(): void
    {
        $view = $this->settingsView();

        $expectedOrder = [
            '#identitySchoolCard { order: 10; }',
            '#addressSchoolCard { order: 20; }',
            '#contactSchoolCard { order: 30; }',
            '#principalSchoolCard { order: 40; }',
            '#socialSchoolCard { order: 50; }',
            '#schoolLogoCard { order: 60; }',
            '#letterheadCard { order: 70; }',
        ];

        $previousPosition = -1;

        foreach ($expectedOrder as $rule) {
            $position = strpos($view, $rule);

            $this->assertNotFalse($position, "Missing settings order rule: {$rule}");
            $this->assertGreaterThan($previousPosition, $position);
            $previousPosition = $position;
        }

        $this->assertStringContainsString('Wilayah Administratif', $view);
        $this->assertStringContainsString('Pengaturan Ukuran Logo untuk Cetak PDF', $view);
    }

    public function test_settings_controls_adapt_to_mobile_widths(): void
    {
        $view = $this->settingsView();

        $this->assertStringContainsString('@media (max-width: 767.98px)', $view);
        $this->assertStringContainsString('class="input-group school-fetch-group"', $view);
        $this->assertStringContainsString('class="input-group kop-custom-upload"', $view);
        $this->assertStringContainsString('grid-template-columns: 1fr;', $view);
        $this->assertStringContainsString('kop-preview-scroll', $view);
    }

    public function test_school_data_uses_compact_professional_layout(): void
    {
        $view = $this->settingsView();
        $controller = file_get_contents(__DIR__.'/../../app/Http/Controllers/Admin/AppSettingController.php');

        $this->assertStringContainsString('school-data-card', $view);
        $this->assertStringContainsString('margin-bottom: 0.85rem;', $view);
        $this->assertStringContainsString('min-height: 36px;', $view);
        $this->assertStringContainsString('school-source-meta', $view);
        $this->assertStringContainsString('school-region-note', $view);
        $this->assertStringContainsString('max-width: 620px;', $view);
        $this->assertStringContainsString('id="nsm"', $view);
        $this->assertStringContainsString('maxlength="12" readonly', $view);
        $this->assertStringContainsString('Otomatis dari referensi', $view);
        $this->assertStringNotContainsString("'nsm' => 'required|digits:12'", $controller);
        $this->assertStringContainsString('principal-photo', $view);
    }

    public function test_address_fields_follow_a_single_vertical_sequence(): void
    {
        $view = $this->settingsView();

        $fields = ['id="alamat"', 'id="rt"', 'id="rw"', 'id="kode_pos"', 'id="provinsi_code"', 'id="kota_code"', 'id="kecamatan_code"', 'id="kelurahan_code"'];
        $previousPosition = -1;

        foreach ($fields as $field) {
            $position = strpos($view, $field);
            $this->assertNotFalse($position, "Missing address field: {$field}");
            $this->assertGreaterThan($previousPosition, $position);
            $previousPosition = $position;
        }

        $addressSection = substr($view, strpos($view, 'id="addressSchoolCard"'), strpos($view, 'id="contactSchoolCard"') - strpos($view, 'id="addressSchoolCard"'));
        $this->assertSame(8, substr_count($addressSection, '<div class="col-12">'));
    }

    public function test_school_settings_are_presented_as_one_continuous_panel(): void
    {
        $view = $this->settingsView();

        $this->assertStringContainsString('class="settings-main-panel"', $view);
        $this->assertStringContainsString('class="settings-panel-hero"', $view);
        $this->assertStringContainsString('Data Sekolah', $view);
        $this->assertStringContainsString('Identitas &amp; Informasi Sekolah', $view);
        $this->assertStringContainsString('school-data-card:not(#identitySchoolCard)', $view);
        $this->assertStringContainsString('principal-inline-label', $view);
        $this->assertStringNotContainsString('class="settings-section-nav"', $view);
        $this->assertStringContainsString('box-shadow: 0 22px 48px', $view);
        $this->assertStringContainsString('#settingsForm .settings-main-panel > .settings-card', $view);
    }
}
