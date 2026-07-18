<?php

namespace Tests\Unit;

use App\Services\SmartStudentComparator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SmartStudentComparatorTest extends TestCase
{
    private SmartStudentComparator $comparator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->comparator = new SmartStudentComparator;
    }

    public function test_name_formatting_is_equivalent(): void
    {
        $result = $this->comparator->compare(
            $this->student(['nama_lengkap' => 'Muhammad Al-Fatih']),
            $this->student(['nama_lengkap' => 'MUHAMMAD  AL FATIH']),
        );

        $this->assertSame('equivalent', $result['details']['nama_lengkap']['status']);
        $this->assertSame(100.0, $result['details']['nama_lengkap']['score']);
    }

    public function test_small_name_typo_is_marked_similar(): void
    {
        $result = $this->comparator->compare(
            $this->student(['nama_lengkap' => 'Muhammad Rizky Pratama']),
            $this->student(['nama_lengkap' => 'Mohamad Rizki Pratama']),
        );

        $this->assertSame('similar', $result['details']['nama_lengkap']['status']);
        $this->assertGreaterThanOrEqual(85, $result['name_similarity']);
    }

    public function test_identity_is_not_hidden_by_similar_name(): void
    {
        $result = $this->comparator->compare(
            $this->student(['nisn' => '1234567890']),
            $this->student(['nisn' => '1234567891']),
        );

        $this->assertSame('different', $result['status']);
        $this->assertSame('different', $result['details']['nisn']['status']);
    }

    public function test_missing_simansa_value_is_actionable_but_missing_emis_value_is_not_a_false_mismatch(): void
    {
        $missingSimansa = $this->comparator->compare(
            $this->student(['tempat_lahir' => null]),
            $this->student(['tempat_lahir' => 'Metro']),
        );
        $missingEmis = $this->comparator->compare(
            $this->student(['jenis_kelamin' => 'L']),
            $this->student(['jenis_kelamin' => null]),
        );

        $this->assertSame('different', $missingSimansa['status']);
        $this->assertSame('simansa_empty', $missingSimansa['details']['tempat_lahir']['status']);
        $this->assertNotSame('different', $missingEmis['status']);
        $this->assertSame('emis_empty', $missingEmis['details']['jenis_kelamin']['status']);
    }

    #[DataProvider('equivalentFieldProvider')]
    public function test_common_field_variants_are_equivalent(string $field, mixed $left, mixed $right): void
    {
        $result = $this->comparator->compare(
            $this->student([$field => $left]),
            $this->student([$field => $right]),
        );

        $this->assertContains($result['details'][$field]['status'], ['exact', 'equivalent']);
    }

    public static function equivalentFieldProvider(): array
    {
        return [
            'birth place case' => ['tempat_lahir', 'Metro', 'METRO'],
            'birth date format' => ['tanggal_lahir', '2009-01-15', '15 January 2009'],
            'gender label' => ['jenis_kelamin', 'L', 'Laki-laki'],
            'class punctuation' => ['kelas', 'XII IPA 1', '12-IPA-1'],
        ];
    }

    private function student(array $override = []): array
    {
        return array_merge([
            'nama_lengkap' => 'Nama Siswa',
            'nisn' => '1234567890',
            'tempat_lahir' => 'Metro',
            'tanggal_lahir' => '2009-01-15',
            'jenis_kelamin' => 'L',
            'kelas' => 'XII IPA 1',
        ], $override);
    }
}
