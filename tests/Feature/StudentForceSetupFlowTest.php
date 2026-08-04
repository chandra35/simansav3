<?php

namespace Tests\Feature;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentForceSetupFlowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_student_can_secure_account_after_admin_password_reset_without_changing_personal_email(): void
    {
        [$user] = $this->createResetStudent();

        $response = $this->actingAs($user)->post(route('siswa.force-setup.update'), [
            'password' => 'PasswordBaru#2026',
            'password_confirmation' => 'PasswordBaru#2026',
        ]);

        $response->assertRedirect(route('siswa.dashboard'));

        $user->refresh();
        $this->assertFalse($user->is_first_login);
        $this->assertNull($user->password_reset_at);
        $this->assertNull($user->password_reset_by);
        $this->assertSame('siswa-force-setup@example.test', $user->email);
        $this->assertTrue(Hash::check('PasswordBaru#2026', $user->password));
    }

    public function test_force_setup_submit_has_mobile_safe_progressive_enhancement(): void
    {
        [$user] = $this->createResetStudent();

        $response = $this->actingAs($user)->get(route('siswa.force-setup'));

        $response->assertOk();
        $response->assertSee('Simpan &amp; Amankan Akun Saya', false);
        $response->assertSee('id="setupForm" data-no-overlay', false);
        preg_match('/<button[^>]+id="submitBtn"[^>]*>/i', $response->getContent(), $submitButton);
        $this->assertNotEmpty($submitButton);
        $this->assertStringNotContainsString('disabled', strtolower($submitButton[0]));
        preg_match('/<button[^>]+id="btnNext1"[^>]*>/i', $response->getContent(), $nextButton);
        $this->assertNotEmpty($nextButton);
        $this->assertStringNotContainsString('disabled', strtolower($nextButton[0]));
        $this->assertStringNotContainsString(
            '?.',
            file_get_contents(resource_path('views/siswa/profile/force-setup.blade.php'))
        );
    }

    /**
     * @return array{0: User, 1: Siswa}
     */
    private function createResetStudent(): array
    {
        $suffix = Str::lower(Str::random(10));
        $user = User::create([
            'name' => 'Siswa Force Setup',
            'username' => "force-{$suffix}",
            'email' => 'siswa-force-setup@example.test',
            'password' => Hash::make('1234567890'),
            'role' => 'siswa',
            'is_active' => true,
            'is_first_login' => true,
            'password_reset_at' => now(),
            'password_reset_by' => 'Administrator',
        ]);

        $siswa = Siswa::create([
            'user_id' => $user->id,
            'nisn' => '8'.random_int(100000000, 999999999),
            'nama_lengkap' => $user->name,
            'jenis_kelamin' => 'L',
            'data_ortu_completed' => true,
            'data_diri_completed' => true,
        ]);

        return [$user, $siswa];
    }
}
