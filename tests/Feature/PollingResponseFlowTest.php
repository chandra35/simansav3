<?php

namespace Tests\Feature;

use App\Models\Gtk;
use App\Models\Polling;
use App\Models\Siswa;
use App\Models\User;
use App\Http\Controllers\Admin\PollingController;
use App\Services\PollingAudienceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PollingResponseFlowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_response_is_locked_until_admin_approves_unlock_request(): void
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
        ])->assertSessionHasErrors('polling');

        $this->actingAs($user)->post(route('siswa.polling.unlock-request', $polling))
            ->assertRedirect();
        $response = $polling->responses()->firstOrFail();
        $this->assertNotNull($response->unlock_requested_at);

        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::withoutEvents(fn () => User::factory()->create([
            'id' => (string) Str::uuid(),
            'username' => 'polling-unlock-admin-'.uniqid(),
            'role' => 'super_admin',
            'is_active' => true,
            'is_first_login' => false,
        ]));
        $admin->assignRole($role);
        $this->actingAs($admin)->postJson(route('admin.polling.responses.unlock', [$polling, $response]))
            ->assertOk()->assertJson(['success' => true]);

        $this->actingAs($user)->post(route('siswa.polling.store', $polling), [
            'answers' => [$question->id => $optionB->id],
        ])->assertRedirect(route('siswa.polling.show', $polling));

        $this->assertDatabaseCount('polling_responses', 1);
        $this->assertDatabaseMissing('polling_answer_options', ['polling_option_id' => $optionA->id]);
        $this->assertDatabaseHas('polling_answer_options', ['polling_option_id' => $optionB->id]);
        $this->assertTrue($response->fresh()->isLocked());

        $voterTable = [
            'draw' => 1, 'start' => 0, 'length' => 10,
            'search' => ['value' => '', 'regex' => 'false'],
            'columns' => [
                ['data' => 'DT_RowIndex', 'name' => '', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'respondent', 'name' => 'respondent_name', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'scope', 'name' => 'class_name', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'submitted', 'name' => 'submitted_at', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ],
        ];
        $voters = $this->actingAs($admin)->getJson(route('admin.polling.voters', [$polling, $question, $optionB]).'?'.http_build_query($voterTable));
        $voters->assertOk()->assertJsonPath('recordsFiltered', 1);

        $respondents = $this->actingAs($admin)->getJson(
            route('admin.polling.respondents', $polling).'?status=answered&'.http_build_query($voterTable)
        );
        $respondents->assertOk()->assertJsonPath('recordsFiltered', 1);

        $this->actingAs($user)->get(route('siswa.polling.show', $polling))
            ->assertOk()->assertSee('Jawaban sudah terkunci');
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
            ->assertOk()->assertSee('Preset TKA Kelas XII')->assertSee('Survei Kepuasan');

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
        $this->actingAs($user)->get(route('admin.polling.duplicate', $storedPolling))
            ->assertOk()
            ->assertSee('Salinan Polling Target GTK Custom')
            ->assertSee('name="source_polling_id"', false)
            ->assertSee('Masukan GTK');

        $this->actingAs($user)->post(route('admin.polling.store'), [
            'title' => 'Salinan Polling Target GTK Custom',
            'audience' => 'gtk',
            'starts_at' => now()->addHour()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'reminder_interval_hours' => 6,
            'action' => 'draft',
            'gtk_all' => '1',
            'source_polling_id' => $storedPolling->id,
            'questions' => [[
                'prompt' => 'Masukan GTK',
                'type' => 'long_text',
                'is_required' => '1',
            ]],
        ])->assertRedirect();
        $this->assertDatabaseHas('pollings', [
            'title' => 'Salinan Polling Target GTK Custom',
            'source_polling_id' => $storedPolling->id,
        ]);

        $this->actingAs($user)->delete(route('admin.polling.destroy', $storedPolling))->assertRedirect(route('admin.polling.index'));
        $this->assertDatabaseHas('pollings', ['id' => $storedPolling->id, 'status' => 'closed']);

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

        $respondentTable = [
            'draw' => 1, 'start' => 0, 'length' => 10,
            'search' => ['value' => '', 'regex' => 'false'],
            'columns' => [
                ['data' => 'DT_RowIndex', 'name' => '', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'respondent', 'name' => 'name', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ],
        ];
        $this->actingAs($user)->getJson(route('admin.polling.respondents', $polling).'?'.http_build_query($respondentTable))
            ->assertOk()->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }

    public function test_manager_can_reopen_closed_polling_without_changing_existing_responses(): void
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::withoutEvents(fn () => User::factory()->create([
            'id' => (string) Str::uuid(),
            'username' => 'polling-reopen-admin-'.uniqid(),
            'role' => 'super_admin',
            'is_active' => true,
            'is_first_login' => false,
        ]));
        $admin->assignRole($role);
        $polling = Polling::create([
            'slug' => 'polling-reopen-'.uniqid(),
            'title' => 'Polling Perpanjangan Test',
            'audience' => 'siswa',
            'status' => 'closed',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->subHour(),
            'reminder_interval_hours' => 6,
        ]);
        $response = $polling->responses()->create([
            'user_id' => $admin->id,
            'respondent_type' => 'siswa',
            'respondent_id' => (string) Str::uuid(),
            'respondent_name' => 'Respons Lama',
            'submitted_at' => now()->subHours(2),
            'locked_at' => now()->subHours(2),
        ]);
        $newEndsAt = now()->addDay()->startOfMinute();

        auth()->login($admin);
        $redirect = app(PollingController::class)->reopen(Request::create('/', 'POST', [
            'ends_at' => $newEndsAt->format('Y-m-d H:i:s'),
        ]), $polling);

        $this->assertTrue($redirect->isRedirection());
        $this->assertDatabaseHas('pollings', ['id' => $polling->id, 'status' => 'published']);
        $this->assertTrue($polling->fresh()->ends_at->equalTo($newEndsAt));
        $this->assertDatabaseHas('polling_responses', ['id' => $response->id, 'locked_at' => $response->locked_at]);
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
