<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class GtkAccountUiArchitectureTest extends TestCase
{
    public function test_core_gtk_account_pages_use_the_shared_simansa_visual_language(): void
    {
        $root = dirname(__DIR__, 2).'/resources/views/admin/gtk/';
        $dashboard = file_get_contents($root.'dashboard.blade.php');
        $profile = file_get_contents($root.'profile/index.blade.php');
        $password = file_get_contents($root.'profile/password.blade.php');

        foreach ([$dashboard, $profile, $password] as $view) {
            $this->assertStringContainsString('class="row mb-2"', $view);
            $this->assertStringContainsString('class="breadcrumb float-sm-right"', $view);
            $this->assertStringContainsString('card bg-gradient-primary text-white mb-4', $view);
            $this->assertStringContainsString('card card-outline card-primary', $view);
            $this->assertStringContainsString('@media (max-width:', $view);
            $this->assertStringNotContainsString('class="simansa-hero', $view);
        }

        $this->assertStringContainsString('class="gtk-account-dashboard"', $dashboard);
        $this->assertStringContainsString('class="gtk-account-profile"', $profile);
        $this->assertStringContainsString('class="gtk-account-password"', $password);
    }

    public function test_profile_actions_do_not_depend_on_a_viewport_wide_fixed_bar(): void
    {
        $profile = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/gtk/profile/index.blade.php');

        $this->assertStringContainsString('position: sticky;', $profile);
        $this->assertStringNotContainsString('left: 250px', $profile);
        $this->assertStringNotContainsString('.content-wrapper { padding-bottom:', $profile);
    }

    public function test_profile_photo_preview_keeps_the_head_in_frame(): void
    {
        $profile = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/gtk/profile/index.blade.php');

        $this->assertStringContainsString('.gtk-foto-frame img', $profile);
        $this->assertStringContainsString('object-position: center top;', $profile);
    }

    public function test_password_inputs_keep_browser_autofill_and_accessible_feedback(): void
    {
        $password = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/gtk/profile/password.blade.php');

        $this->assertStringContainsString('autocomplete="current-password"', $password);
        $this->assertSame(2, substr_count($password, 'autocomplete="new-password"'));
        $this->assertStringContainsString('aria-live="polite"', $password);
        $this->assertStringContainsString('gtk-account-password__identity .card-body { grid-template-columns:1fr;', $password);
    }
}
