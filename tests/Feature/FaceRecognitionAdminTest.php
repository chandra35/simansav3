<?php

namespace Tests\Feature;

use App\Models\FaceEncoding;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FaceRecognitionAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_manage_and_verify_student_face_data(): void
    {
        $admin = User::role('Super Admin')->first() ?: User::role('Admin')->first();
        $student = Siswa::whereNotNull('user_id')->whereHas('user')->first();
        if (! $admin || ! $student) {
            $this->markTestSkipped('Akun admin dan siswa diperlukan.');
        }

        $this->actingAs($admin)
            ->get(route('admin.absensi.face-register', ['type' => 'siswa']))
            ->assertOk()
            ->assertSee('Registrasi Wajah Siswa');

        $face = FaceEncoding::updateOrCreate(
            ['user_id' => $student->user_id, 'user_type' => 'siswa'],
            [
                'descriptors' => [array_fill(0, 128, 0.1)],
                'capture_angles' => ['frontal', 'kanan', 'kiri', 'senyum', 'kedip'],
                'total_captures' => 5,
                'quality_score' => 85,
                'is_active' => true,
                'is_verified' => false,
                'verified_by' => null,
                'verified_at' => null,
            ]
        );

        $this->actingAs($admin)
            ->get(route('admin.absensi.face-verification', ['type' => 'siswa']))
            ->assertOk()
            ->assertSee('Verifikasi Wajah Siswa');

        $this->actingAs($admin)
            ->withSession(['_token' => 'verify-student-face'])
            ->post(route('admin.absensi.face-verify', $face), [
                '_token' => 'verify-student-face',
                'action' => 'approve',
            ])
            ->assertRedirect(route('admin.absensi.face-verification', ['type' => 'siswa']));

        $this->assertTrue($face->fresh()->is_verified);
    }

    public function test_face_descriptors_are_not_available_to_student_accounts(): void
    {
        $studentUser = User::whereHas('siswa')->first();
        if (! $studentUser) {
            $this->markTestSkipped('Akun siswa diperlukan.');
        }

        $this->assertFalse(app('router')->has('siswa.face-descriptors'));
        $this->actingAs($studentUser)
            ->get(route('siswa.face-register'))
            ->assertOk()
            ->assertSee('Registrasi Wajah Saya')
            ->assertDontSee('face-descriptors');

        $this->actingAs($studentUser)
            ->getJson(route('admin.absensi.face-descriptors', ['type' => 'siswa']))
            ->assertForbidden();
    }
}
