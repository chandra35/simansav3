<?php

namespace Tests\Feature\Admin;

use App\Models\AbsensiOperationalSchedule;
use App\Models\User;
use App\Services\AttendanceWindowService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AttendanceOperationalWindowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_tuesday_check_in_and_check_out_windows_follow_server_schedule(): void
    {
        $this->schedule('gtk', 2, '16:30');
        $service = app(AttendanceWindowService::class);

        $this->assertState($service, '2026-08-11 05:59:00', false, 'closed');
        $this->assertState($service, '2026-08-11 06:00:00', true, 'masuk', 'hadir');
        $this->assertState($service, '2026-08-11 07:00:59', true, 'masuk', 'hadir');
        $this->assertState($service, '2026-08-11 07:01:00', true, 'masuk', 'terlambat');
        $this->assertState($service, '2026-08-11 08:00:59', true, 'masuk', 'terlambat');
        $this->assertState($service, '2026-08-11 08:01:00', false, 'closed');
        $this->assertState($service, '2026-08-11 16:29:59', false, 'closed');
        $this->assertState($service, '2026-08-11 16:30:00', true, 'pulang');
        $this->assertState($service, '2026-08-11 23:59:59', true, 'pulang');
    }

    public function test_daily_checkout_defaults_are_available_for_both_kiosk_types(): void
    {
        $service = app(AttendanceWindowService::class);
        foreach (['gtk', 'siswa'] as $type) {
            $this->schedule($type, 1, '15:00');
            $this->schedule($type, 5, '14:30');
            $this->assertState($service, '2026-08-10 15:00:00', true, 'pulang', null, $type);
            $this->assertState($service, '2026-08-14 14:30:00', true, 'pulang', null, $type);
        }
    }

    public function test_inactive_day_is_closed_even_when_a_time_matches(): void
    {
        AbsensiOperationalSchedule::query()->updateOrCreate(
            ['user_type' => 'gtk', 'day_of_week' => 6],
            $this->payload('15:00', false)
        );

        $state = app(AttendanceWindowService::class)->state('gtk', Carbon::parse('2026-08-15 06:30:00', 'Asia/Jakarta'));

        $this->assertFalse($state['is_open']);
        $this->assertSame('closed', $state['mode']);
        $this->assertNotNull($state['next_at']);
    }

    public function test_record_face_endpoint_rejects_a_browser_request_outside_the_server_window(): void
    {
        $this->schedule('gtk', 2, '16:30');
        $user = User::role('Super Admin')->where('is_active', true)->firstOrFail();
        Carbon::setTestNow(Carbon::parse('2026-08-11 12:00:00', 'Asia/Jakarta'));

        try {
            $response = $this->actingAs($user)->postJson(route('admin.absensi.record-face'), [
                'user_id' => $user->id,
                'user_type' => 'gtk',
                'confidence' => 0.91,
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('success', false)
                ->assertJsonPath('window.mode', 'closed')
                ->assertJsonPath('window.is_open', false);
        } finally {
            Carbon::setTestNow();
        }
    }

    private function schedule(string $type, int $day, string $checkout): void
    {
        AbsensiOperationalSchedule::query()->updateOrCreate(
            ['user_type' => $type, 'day_of_week' => $day],
            $this->payload($checkout)
        );
    }

    private function payload(string $checkout, bool $active = true): array
    {
        return [
            'is_active' => $active,
            'check_in_open' => '06:00',
            'on_time_until' => '07:00',
            'check_in_close' => '08:00',
            'check_out_open' => $checkout,
            'check_out_close' => '23:59',
        ];
    }

    private function assertState(AttendanceWindowService $service, string $time, bool $open, string $mode, ?string $status = null, string $type = 'gtk'): void
    {
        $state = $service->state($type, Carbon::parse($time, 'Asia/Jakarta'));
        $this->assertSame($open, $state['is_open'], $time);
        $this->assertSame($mode, $state['mode'], $time);
        if ($status !== null) {
            $this->assertSame($status, $state['status'], $time);
        }
    }
}
