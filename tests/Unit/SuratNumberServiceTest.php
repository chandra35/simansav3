<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\SuratNomorConfig;
use App\Models\SuratNomorCounter;
use App\Models\BukuNomorSurat;
use App\Services\SuratNumberService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SuratNumberServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected SuratNumberService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SuratNumberService::class);
    }

    public function test_can_preview_letter_number()
    {
        $preview = $this->service->preview('PP.00.6');
        $this->assertNotEmpty($preview);
        $this->assertStringContainsString('PP.00.6', $preview);
        $this->assertStringContainsString((string) date('Y'), $preview);
    }

    public function test_can_issue_letter_number_and_recorded_in_buku_agenda()
    {
        $issued = $this->service->issue('PP.00.6', [
            'perihal' => 'Surat Uji Coba Unit Test',
            'tujuan' => 'Instansi Penguji',
            'keterangan' => 'Diterbitkan melalui automated unit test',
        ]);

        $this->assertNotNull($issued);
        $this->assertNotEmpty($issued->nomor_surat);
        $this->assertStringContainsString('PP.00.6', $issued->nomor_surat);
        $this->assertEquals('aktif', $issued->status);

        // Verify recorded in BukuNomorSurat
        $found = BukuNomorSurat::where('nomor_surat', $issued->nomor_surat)->first();
        $this->assertNotNull($found);
        $this->assertEquals('Surat Uji Coba Unit Test', $found->perihal);
        $this->assertEquals('Instansi Penguji', $found->tujuan);

        // Cancel the issued letter
        $cancelled = $this->service->cancel($issued->nomor_surat, 'Pembatalan dalam testing');
        $this->assertTrue($cancelled);

        $found->refresh();
        $this->assertEquals('dibatalkan', $found->status);
        $this->assertEquals('Pembatalan dalam testing', $found->alasan_dibatalkan);
    }

    public function test_template_surat_classification_mapping_and_preview()
    {
        $template = new \App\Models\TemplateSurat([
            'nama' => 'Surat Keterangan Aktif Test',
            'kode' => 'SK-AKTIF-TEST',
            'jenis' => 'sk_aktif',
        ]);

        // Default mapping fallback
        $this->assertEquals('PP.00.6', $template->kode_klasifikasi);

        // Preview should contain the classification
        $preview = $template->preview_nomor_surat;
        $this->assertStringContainsString('PP.00.6', $preview);

        // Custom override
        $template->kode_klasifikasi = 'KU.01.1';
        $this->assertEquals('KU.01.1', $template->kode_klasifikasi);
        $this->assertStringContainsString('KU.01.1', $template->preview_nomor_surat);
    }
}
