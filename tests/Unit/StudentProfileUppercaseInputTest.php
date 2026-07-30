<?php

namespace Tests\Unit;

use App\Support\UppercaseInputNormalizer;
use PHPUnit\Framework\TestCase;

class StudentProfileUppercaseInputTest extends TestCase
{
    public function test_selected_human_readable_fields_are_trimmed_and_uppercased(): void
    {
        $result = UppercaseInputNormalizer::normalize([
            'nama_lengkap' => '  Muhammad Al-Fatih  ',
            'tempat_lahir' => 'Kota Metro',
            'email' => 'Siswa.Example@gmail.com',
            'status_ayah' => 'masih_hidup',
            'jumlah_saudara' => 2,
            'alamat_siswa' => null,
        ], [
            'nama_lengkap',
            'tempat_lahir',
            'alamat_siswa',
        ]);

        $this->assertSame('MUHAMMAD AL-FATIH', $result['nama_lengkap']);
        $this->assertSame('KOTA METRO', $result['tempat_lahir']);
        $this->assertSame('Siswa.Example@gmail.com', $result['email']);
        $this->assertSame('masih_hidup', $result['status_ayah']);
        $this->assertSame(2, $result['jumlah_saudara']);
        $this->assertNull($result['alamat_siswa']);
    }

    public function test_student_profile_can_edit_name_and_normalizes_free_text_on_both_forms(): void
    {
        $profileController = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Siswa/ProfileController.php');
        $parentController = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Siswa/OrtuController.php');
        $studentView = file_get_contents(dirname(__DIR__, 2).'/resources/views/siswa/profile/diri.blade.php');
        $parentView = file_get_contents(dirname(__DIR__, 2).'/resources/views/siswa/profile/ortu.blade.php');

        $this->assertStringContainsString("'nama_lengkap' => 'required|string|max:255'", $profileController);
        $this->assertStringContainsString("'name' => \$validated['nama_lengkap']", $profileController);
        $this->assertStringNotContainsString("\$request->except(", $profileController);
        $this->assertStringNotContainsString("\$request->all()", $profileController);
        $this->assertStringContainsString('UppercaseInputNormalizer::normalize', $profileController);
        $this->assertStringContainsString('UppercaseInputNormalizer::normalize', $parentController);

        $this->assertStringContainsString('name="nama_lengkap"', $studentView);
        $this->assertStringContainsString('class="uppercase-input-form"', $studentView);
        $this->assertStringContainsString('class="uppercase-input-form"', $parentView);
        $this->assertStringContainsString("toLocaleUpperCase('id-ID')", $studentView);
        $this->assertStringContainsString("toLocaleUpperCase('id-ID')", $parentView);
    }
}
