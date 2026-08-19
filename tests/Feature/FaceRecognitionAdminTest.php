<?php

namespace Tests\Feature;

use App\Models\FaceEncoding;
use App\Models\AbsensiSetting;
use App\Models\Siswa;
use App\Models\User;
use App\Services\FaceDescriptorService;
use App\Http\Controllers\Admin\FaceRegistrationController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class FaceRecognitionAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_python_edge_agent_uses_a_separate_bearer_token_and_reports_simulation_status(): void
    {
        $token = Str::random(64);
        AbsensiSetting::updateOrCreate(
            ['key' => 'face_python_device_token'],
            ['value' => $token, 'type' => 'string', 'group' => 'kiosk', 'label' => 'Token Perangkat Face Python']
        );
        $this->mock(FaceDescriptorService::class, function ($mock) {
            $mock->shouldReceive('forPython')->once()->andReturn(collect());
        });

        $this->getJson(route('public.face-python.bootstrap'))->assertNotFound();
        $this->withToken($token)->getJson(route('public.face-python.bootstrap'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private')
            ->assertJsonPath('success', true)
            ->assertJsonPath('mode', 'simulation')
            ->assertJsonPath('people', [])
            ->assertJsonMissingPath('identifier');

        $payload = [
            'device_name' => 'PC Gerbang Test',
            'agent_version' => '0.1.0',
            'state' => 'running',
            'fps' => 18.5,
            'faces_in_frame' => 2,
            'profiles' => 10,
            'recognized_name' => 'Pengguna Uji',
            'confidence' => 0.87,
        ];
        $this->withToken($token)->postJson(route('public.face-python.heartbeat'), $payload)
            ->assertOk()
            ->assertJsonPath('success', true);

        $cached = Cache::get('face-python:device:'.hash('sha256', $token));
        $this->assertSame('PC Gerbang Test', $cached['device_name']);
        $this->assertSame('running', $cached['state']);

        $admin = User::role('Super Admin')->first() ?: User::role('Admin')->first();
        if ($admin) {
            $this->actingAs($admin)->get(route('admin.absensi.face-python'))
                ->assertOk()
                ->assertSee('Face Python')
                ->assertSee('Mode simulasi');
            $download = $this->actingAs($admin)->get(route('admin.absensi.face-python.download'))
                ->assertOk()
                ->assertDownload('simansa-face-python-agent.zip')
                ->assertHeader('Content-Type', 'application/zip');

            $archivePath = $download->baseResponse->getFile()->getPathname();
            $this->assertSame('504b0304', bin2hex((string) file_get_contents($archivePath, false, null, 0, 4)));
            $archive = new \ZipArchive();
            $this->assertTrue($archive->open($archivePath, \ZipArchive::CHECKCONS) === true);
            $this->assertSame(4, $archive->numFiles);
            $this->assertNotFalse($archive->locateName('simansa-face-python/agent.py'));
            $this->assertNotFalse($archive->locateName('simansa-face-python/requirements.txt'));
            $archive->close();
        }
    }

    public function test_duplicate_face_protection_requires_consistent_matches_across_three_captures(): void
    {
        $users = User::query()->whereHas('siswa')->limit(2)->get();
        if ($users->count() < 2) {
            $this->markTestSkipped('Dua akun siswa diperlukan.');
        }

        [$target, $registered] = $users->values();
        $descriptor = array_fill(0, 128, 0.1);
        $different = array_fill(0, 128, 2.0);
        FaceEncoding::updateOrCreate(
            ['user_id' => $registered->id, 'user_type' => 'siswa'],
            [
                'descriptors' => array_fill(0, 5, $descriptor),
                'capture_angles' => ['frontal', 'kanan', 'kiri', 'senyum', 'kedip'],
                'total_captures' => 5,
                'quality_score' => 90,
                'is_active' => true,
                'is_verified' => true,
            ]
        );

        $method = new \ReflectionMethod(FaceRegistrationController::class, 'findDuplicateFaceMatch');
        $controller = app(FaceRegistrationController::class);

        $oneCoincidentalCapture = $method->invoke($controller, [$descriptor, $different, $different, $different, $different], $target->id);
        $this->assertNull($oneCoincidentalCapture);

        $threeConsistentCaptures = $method->invoke($controller, [$descriptor, $descriptor, $descriptor, $different, $different], $target->id);
        $this->assertSame(3, $threeConsistentCaptures['matched_captures']);
        $this->assertSame($registered->id, $threeConsistentCaptures['face']->user_id);
    }

    public function test_public_face_detect_requires_valid_rotatable_device_token_without_login(): void
    {
        $token = Str::random(64);
        AbsensiSetting::updateOrCreate(
            ['key' => 'face_detect_public_token'],
            ['value' => $token, 'type' => 'string', 'group' => 'kiosk', 'label' => 'Token Publik Face Detect']
        );

        $this->get(route('public.face-detect.show', ['token' => 'token-tidak-valid']))
            ->assertNotFound();

        $this->get(route('public.face-detect.show', ['token' => $token]))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee('Perangkat publik')
            ->assertSee('Aktifkan Kamera &amp; Suara', false);

        $this->getJson(route('public.face-detect.descriptors', ['token' => $token, 'type' => 'gtk']))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private')
            ->assertJsonPath('success', true);
    }

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
            ->assertSee('Verifikasi Wajah Siswa')
            ->assertSee('Foto Wajah')
            ->assertSee('Data Wajah Terdaftar');

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

    public function test_student_cannot_overwrite_locked_face_registration(): void
    {
        $student = Siswa::whereNotNull('user_id')->whereHas('user')->first();
        if (! $student) {
            $this->markTestSkipped('Akun siswa diperlukan.');
        }

        FaceEncoding::updateOrCreate(
            ['user_id' => $student->user_id, 'user_type' => 'siswa'],
            [
                'descriptors' => [array_fill(0, 128, 0.1)],
                'capture_angles' => ['frontal', 'kanan', 'kiri', 'senyum', 'kedip'],
                'total_captures' => 5,
                'quality_score' => 85,
                'self_registration_unlocked_at' => null,
                'is_active' => true,
                'is_verified' => true,
            ]
        );

        $this->actingAs($student->user)
            ->withSession(['_token' => 'student-face-lock'])
            ->withHeader('X-CSRF-TOKEN', 'student-face-lock')
            ->postJson(route('siswa.face-register.store'), $this->validRegistrationPayload($student->user_id))
            ->assertStatus(423)
            ->assertJsonPath('success', false);

        $this->actingAs($student->user)
            ->get(route('siswa.face-register'))
            ->assertOk()
            ->assertSee('Registrasi terkunci')
            ->assertSee('Minta Unlock')
            ->assertDontSee('Edit / Registrasi Ulang');

        $this->actingAs($student->user)
            ->get(route('siswa.face-attendance-history'))
            ->assertOk()
            ->assertSee('Riwayat Presensi Saya');
    }

    public function test_admin_can_unlock_one_self_reregistration_and_it_locks_after_save(): void
    {
        Storage::fake('public');
        $admin = User::role('Super Admin')->first() ?: User::role('Admin')->first();
        $student = Siswa::whereNotNull('user_id')->whereHas('user')->first();
        if (! $admin || ! $student) {
            $this->markTestSkipped('Akun admin dan siswa diperlukan.');
        }

        $face = FaceEncoding::updateOrCreate(
            ['user_id' => $student->user_id, 'user_type' => 'siswa'],
            [
                'descriptors' => [array_fill(0, 128, 0.1)],
                'capture_angles' => ['frontal', 'kanan', 'kiri', 'senyum', 'kedip'],
                'total_captures' => 5,
                'quality_score' => 80,
                'self_registration_unlocked_at' => null,
                'self_registration_requested_at' => null,
                'self_registration_request_note' => null,
                'is_active' => true,
                'is_verified' => false,
            ]
        );

        $this->actingAs($student->user)
            ->withSession(['_token' => 'student-face-request'])
            ->post(route('siswa.face-register.request-unlock'), [
                '_token' => 'student-face-request',
                'note' => 'Wajah tidak lagi terdeteksi.',
            ])
            ->assertSessionHas('success');
        $this->assertNotNull($face->fresh()->self_registration_requested_at);
        $this->assertSame('Wajah tidak lagi terdeteksi.', $face->fresh()->self_registration_request_note);

        $this->actingAs($admin)
            ->withSession(['_token' => 'admin-face-unlock'])
            ->post(route('admin.absensi.face-encoding.self-access', $face), [
                '_token' => 'admin-face-unlock',
                'action' => 'unlock',
            ])
            ->assertRedirect(route('admin.absensi.face-verification', ['type' => 'siswa']));

        $this->assertNotNull($face->fresh()->self_registration_unlocked_at);
        $this->assertNull($face->fresh()->self_registration_requested_at);

        $this->actingAs($admin)
            ->withSession(['_token' => 'admin-face-lock'])
            ->post(route('admin.absensi.face-encoding.self-access', $face), [
                '_token' => 'admin-face-lock',
                'action' => 'lock',
            ])
            ->assertRedirect(route('admin.absensi.face-verification', ['type' => 'siswa']));
        $this->assertNull($face->fresh()->self_registration_unlocked_at);

        $this->actingAs($admin)
            ->withSession(['_token' => 'admin-face-reunlock'])
            ->post(route('admin.absensi.face-encoding.self-access', $face), [
                '_token' => 'admin-face-reunlock',
                'action' => 'unlock',
            ]);

        $this->actingAs($student->user)
            ->withSession(['_token' => 'student-face-reregister'])
            ->withHeader('X-CSRF-TOKEN', 'student-face-reregister')
            ->postJson(route('siswa.face-register.store'), $this->validRegistrationPayload($student->user_id))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNull($face->fresh()->self_registration_unlocked_at);
        $this->assertSame(5, $face->fresh()->total_captures);
        $this->assertCount(5, $face->fresh()->registration_photos);
        $this->assertTrue(Activity::query()
            ->where('log_name', 'face-recognition')
            ->where('subject_id', $face->id)
            ->where('properties->event', 'reregistered')
            ->exists());
    }

    private function validRegistrationPayload(string $userId): array
    {
        $descriptors = [];
        for ($capture = 0; $capture < 5; $capture++) {
            $descriptors[] = array_map(
                fn (int $index) => 10 + $capture + ($index / 1000),
                range(0, 127)
            );
        }

        return [
            'user_id' => $userId,
            'user_type' => 'siswa',
            'descriptors' => $descriptors,
            'angles' => ['frontal', 'kanan', 'kiri', 'senyum', 'kedip'],
            'quality_score' => 90,
            'liveness_score' => 90,
            'liveness_summary' => [
                'challenge_count' => 5,
                'completed_steps' => ['frontal', 'kanan', 'kiri', 'senyum', 'kedip'],
                'total_duration_ms' => 6000,
                'blink_count' => 1,
                'max_blink_close_frames' => 2,
                'yaw_span' => 0.5,
                'turn_signs' => [1, -1],
                'turn_deltas' => [0.08, -0.08],
                'turn_span' => 0.16,
                'smile_delta' => 0.05,
                'passive_motion_score' => 0.02,
                'gesture_motion_score' => 0.1,
                'liveness_score' => 90,
            ],
            'photos' => array_fill(0, 5, 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9WlmsAAAAASUVORK5CYII='),
        ];
    }
}
