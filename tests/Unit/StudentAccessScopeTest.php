<?php

namespace Tests\Unit;

use App\Models\Gtk;
use App\Models\User;
use App\Services\StudentAccessScope;
use Tests\TestCase;

class StudentAccessScopeTest extends TestCase
{
    public function test_it_limits_a_gtk_account_without_a_global_staff_role(): void
    {
        $user = new class extends User {
            public function hasAnyRole(...$roles): bool
            {
                return false;
            }
        };
        $user->role = 'gtk';
        $user->setRelation('gtk', new Gtk());

        $this->assertTrue(app(StudentAccessScope::class)->isLimited($user));
    }

    public function test_it_keeps_global_staff_outside_assignment_scope(): void
    {
        $user = new class extends User {
            public function hasAnyRole(...$roles): bool
            {
                return in_array('Admin', $roles[0] ?? [], true);
            }
        };
        $user->role = 'admin';

        $this->assertFalse(app(StudentAccessScope::class)->isLimited($user));
    }

    public function test_it_allows_a_custom_role_to_receive_global_student_and_class_scope(): void
    {
        $user = new class extends User {
            public function hasAnyRole(...$roles): bool
            {
                return false;
            }

            public function can($ability, $arguments = []): bool
            {
                return $ability === StudentAccessScope::GLOBAL_SCOPE_PERMISSION;
            }
        };
        $user->role = 'gtk';
        $user->setRelation('gtk', new Gtk());

        $this->assertFalse(app(StudentAccessScope::class)->isLimited($user));
    }
}
