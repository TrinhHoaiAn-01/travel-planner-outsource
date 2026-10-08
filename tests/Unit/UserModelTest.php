<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    /**
     * Kiểm thử phương thức isAdmin trả về true khi vai trò là admin và false khi là user.
     */
    public function test_is_admin_method_identifies_admin_role_correctly(): void
    {
        $admin = new User([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        $regularUser = new User([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'role' => 'user',
        ]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($regularUser->isAdmin());
    }

    /**
     * Kiểm thử các trường fillable của User model.
     */
    public function test_user_fillable_attributes(): void
    {
        $user = new User([
            'name' => 'Trần Văn Trọng',
            'email' => 'trong@travelplanner.test',
            'password' => 'secret123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->assertSame('Trần Văn Trọng', $user->name);
        $this->assertSame('trong@travelplanner.test', $user->email);
        $this->assertSame('admin', $user->role);
        $this->assertTrue($user->is_active);
    }
}
