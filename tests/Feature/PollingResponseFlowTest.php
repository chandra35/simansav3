<?php

namespace Tests\Feature;

use App\Models\Gtk;
use App\Models\Polling;
use App\Models\Siswa;
use App\Models\User;
use App\Services\PollingAudienceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PollingResponseFlowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_targeted_student_can_submit_and_update_one_valid_response(): void
    {
        $user = User::withoutEvents(fn () => User::factory()->create([
            'id' => (string) Str::uuid(),
            'username' => 'polling-test-'.uniqid(),
            'role' => 'siswa',
            'is_active' => true,
            'is_first_login' => false,
        ]));
        Siswa::create([
            'user_id' => $user->id,
            'nisn' => '99'.random_int(10000000, 99999999),
            'nama_lengkap' => 'RESPONDEN POLLING TEST',
            'jenis_kelamin' => 'L',
        ]);
        $user->refresh()->load('siswa');

        $polling = Polling::create([
            'slug' => 'polling-flow-'.uniqid(),
            'title' => 'Polling Alur Test',
            'audience' => 'siswa',
            'status' => 'published',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'allow_changes' => true,
            'show_results_after_submit' => false,
            'require_consent' => false,
            'reminder_interval_hours' => 6,
        ]);
        $polling->targets()->create(['audience_type' => 'siswa', 'scope_type' => 'all']);
        $question = $polling->questions()->create([
            'prompt' => 'Pilih salah satu', 'type' => 'single', 'is_required' => true, 'sort_order' => 0,
        ]);
        $optionA = $question->options()->create(['label' => 'Pilihan A', 'sort_order' => 0]);
        $optionB = $question->options()->create(['label' => 'Pilihan B', 'sort_order' => 1]);

        $this->assertTrue(app(PollingAudienceService::class)->isEligible($polling->fresh('targets'), $user));

        $this->actingAs($user)->post(route('siswa.polling.store', $polling), [
            'answers' => [$question->id => '00000000-0000-0000-0000-000000000000'],
        ])->assertSessionHasErrors('answers.'.$question->id);

        $this->actingAs($user)->post(route('siswa.polling.store', $polling), [
            'answers' => [$question->id => $optionA->id],
        ])->assertRedirect(route('siswa.polling.show', $polling));

        $this->assertDatabaseCount('polling_responses', 1);
        $this->assertDatabaseHas('polling_answer_options', ['polling_option_id' => $optionA->id]);

        $this->actingAs($user)->post(route('siswa.polling.store', $polling), [
            'answers' => [$question->id => $optionB->id],
        ])->assertRedirect(route('siswa.polling.show', $polling));

        $this->assertDatabaseCount('polling_responses', 1);
        $this->assertDatabaseMissing('polling_answer_options', ['polling_option_id' => $optionA->id]);
        $this->assertDatabaseHas('polling_answer_options', ['polling_option_id' => $optionB->id]);

        $this->actingAs($user)->get(route('siswa.polling.show', $polling))
            ->assertOk()->assertSee('Polling Alur Test');
        $this->actingAs($user)->get(route('siswa.polling.index'))
            ->assertOk()->assertSee('Riwayat Respons');
    }

    public function test_polling_manager_can_open_builder_and_report_index(): void
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::withoutEvents(fn () => User::factory()->create([
            'id' => (string) Str::uuid(),
            'username' => 'polling-admin-test-'.uniqid(),
            'role' => 'super_admin',
            'is_active' => true,
            'is_first_login' => false,
        ]));
        $user->assignRole($role);

        $this->actingAs($user)->get(route('admin.polling.index'))
            ->assertOk()->assertSee('Polling &amp; Survei', false);
        $this->actingAs($user)->get(route('admin.polling.create'))
            ->assertOk()->assertSee('Preset TKA Kelas XII');

        $gtkUser = User::withoutEvents(fn () => User::factory()->create([
            'id' => (string) Str::uuid(),
            'username' => 'polling-target-gtk-'.uniqid(),
            'role' => 'gtk',
            'is_active' => true,
            'is_first_login' => false,
        ]));
        $gtk = Gtk::create([
            'user_id' => $gtkUser->id,
            'nama_lengkap' => 'TARGET GTK BUILDER TEST',
            'nik' => (string) random_int(1000000000000000, 8999999999999999),
            'jenis_kelamin' => 'L',
            'kategori_ptk' => 'Pendidik',
            'jenis_ptk' => 'Guru Mapel',
        ]);
        $this->actingAs($user)->post(route('admin.polling.store'), [
            'title' => 'Polling Target GTK Custom',
            'audience' => 'gtk',
            'starts_at' => now()->addHour()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'reminder_interval_hours' => 6,
            'action' => 'draft',
            'gtk_all' => '0',
            'gtk_categories' => ['Pendidik'],
            'gtks' => [$gtk->id],
            'questions' => [[
                'prompt' => 'Masukan GTK',
                'type' => 'long_text',
                'is_required' => '1',
            ]],
        ])->assertRedirect();
        $storedPolling = Polling::where('title', 'Polling Target GTK Custom')->firstOrFail();
        $this->assertDatabaseHas('polling_targets', [
            'polling_id' => $storedPolling->id,
            'scope_type' => 'kategori_ptk',
            'scope_value' => 'Pendidik',
        ]);
        $this->assertDatabaseHas('polling_targets', [
            'polling_id' => $storedPolling->id,
            'scope_type' => 'gtk',
            'scope_value' => $gtk->id,
        ]);

        $polling = Polling::create([
            'slug' => 'polling-report-'.uniqid(),
            'title' => 'Laporan Polling Test',
            'audience' => 'siswa',
            'status' => 'draft',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
            'reminder_interval_hours' => 6,
        ]);
        $this->actingAs($user)->get(route('admin.polling.show', $polling))
            ->assertOk()->assertSee('Laporan Polling Test')->assertSee('Target Responden');
    }

    public function test_targeted_gtk_can_open_polling_but_other_gtk_scope_is_denied(): void
    {
        $role = Role::firstOrCreate(['name' => 'GTK', 'guard_name' => 'web']);
        $user = User::withoutEvents(fn () => User::factory()->create([
            'id' => (string) Str::uuid(),
            'username' => 'polling-gtk-test-'.uniqid(),
            'role' => 'gtk',
            'is_active' => true,
            'is_first_login' => false,
        ]));
        $user->assignRole($role);
        Gtk::create([
            'user_id' => $user->id,
            'nama_lengkap' => 'RESPONDEN GTK POLLING TEST',
            'nik' => (string) random_int(1000000000000000, 8999999999999999),
            'jenis_kelamin' => 'L',
            'jenis_ptk' => 'Guru Mapel',
        ]);
        $user->refresh()->load('gtk');

        $polling = Polling::create([
            'slug' => 'polling-gtk-flow-'.uniqid(),
            'title' => 'Polling GTK Test',
            'audience' => 'gtk',
            'status' => 'published',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'reminder_interval_hours' => 6,
        ]);
        $polling->targets()->create([
            'audience_type' => 'gtk',
            'scope_type' => 'jenis_ptk',
            'scope_value' => 'Guru Mapel',
        ]);
        $polling->questions()->create([
            'prompt' => 'Masukan GTK',
            'type' => 'long_text',
            'is_required' => false,
            'sort_order' => 0,
        ]);

        $this->actingAs($user)->get(route('admin.gtk.polling.index'))
            ->assertOk()->assertSee('Polling GTK Test');
        $this->actingAs($user)->get(route('admin.gtk.polling.show', $polling))
            ->assertOk()->assertSee('Masukan GTK');

        $polling->targets()->update(['scope_value' => 'Tenaga Kependidikan']);
        $this->actingAs($user)->get(route('admin.gtk.polling.show', $polling))->assertForbidden();
    }
}
