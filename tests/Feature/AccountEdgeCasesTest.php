<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_from_a_stale_tab_reports_an_error_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        $url = route('admin.users.destroy', $member);

        $this->actingAs($admin)->delete($url)->assertSessionHas('success');
        $this->delete($url)->assertSessionHas('error', 'Xóa không hợp lệ. Tài khoản không tồn tại hoặc đã được xóa.');
        $this->assertSoftDeleted($member);
        $this->assertDatabaseCount('activity_logs', 1);
    }

    public function test_stale_admin_edit_cannot_overwrite_the_first_save(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        $url = route('admin.users.update', $member);
        $form = ['email' => $member->email, 'role' => 'user', 'edit_version' => $member->editVersion()];

        $this->actingAs($admin)->put($url, $form + ['name' => $member->name])->assertSessionHas('success');
        $this->putJson($url, $form + ['name' => 'Stale edit'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.edit_version.0', 'Dữ liệu đã thay đổi. Vui lòng tải lại trang trước khi cập nhật.');
        $this->assertSame($member->name, $member->fresh()->name);
    }

    public function test_invalid_ids_and_page_numbers_return_a_not_found_response(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        foreach (['abc', '99999999999'] as $id) {
            $this->get("/admin/users/{$id}")->assertNotFound()->assertSee('Không tìm thấy trang');
            $this->get("/admin/users/{$id}/edit")->assertNotFound();
            $this->put("/admin/users/{$id}")->assertNotFound();
            $this->getJson("/admin/activity-logs/{$id}")->assertNotFound();
        }

        foreach (['/admin/users', '/admin/activity-logs'] as $path) {
            foreach (['abc', '99999', '0', '%EF%BC%91'] as $page) {
                $this->get("{$path}?page={$page}")->assertNotFound();
            }
            $this->get("{$path}?page=1")->assertOk();
        }
    }

    public function test_create_checks_types_lengths_whitespace_and_select_values(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $form = [
            'name' => 'New user',
            'email' => 'new@example.test',
            'role' => 'user',
            'password' => 'Test-password1',
            'password_confirmation' => 'Test-password1',
        ];
        $url = route('admin.users.store');

        foreach ([' ', '　', ['wrong type']] as $invalidName) {
            $this->postJson($url, array_replace($form, ['name' => $invalidName]))
                ->assertUnprocessable()->assertJsonValidationErrors('name');
        }
        $this->postJson($url, array_replace($form, ['name' => str_repeat('<div>Text</div>', 100)]))
            ->assertUnprocessable()->assertJsonPath('errors.name.0', 'Họ và tên không được vượt quá 255 ký tự.');
        $this->postJson($url, array_replace($form, ['role' => 'owner', 'is_active' => '９', 'phone' => 'abc0912345678']))
            ->assertUnprocessable()->assertJsonValidationErrors(['role', 'is_active', 'phone']);
        $this->from(route('admin.users.create'))->post($url, array_replace($form, ['name' => ['wrong type']]))
            ->assertRedirect(route('admin.users.create'))->assertSessionHasErrors('name');
        $this->get(route('admin.users.create'))->assertOk()->assertSee('Họ và tên phải là chuỗi ký tự hợp lệ.');

        $this->post($url, $form + ['phone' => '０９１２３４５６７８'])->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => $form['email'], 'phone' => '0912345678']);
        $this->postJson($url, $form)->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertSame(1, User::where('email', $form['email'])->count());
    }

    public function test_pdf_is_rejected_and_profile_updates_preserve_the_uploaded_avatar(): void
    {
        Storage::fake('public');
        $member = User::factory()->create();
        $this->actingAs($member);
        $avatarRoute = route('profile.avatar');

        $this->postJson($avatarRoute, ['avatar' => UploadedFile::fake()->create('example.pdf', 10, 'application/pdf')])
            ->assertUnprocessable()->assertJsonValidationErrors('avatar');

        $upload = $this->postJson($avatarRoute, [
            'avatar' => UploadedFile::fake()->image('portrait.png'),
            'edit_version' => $member->editVersion(),
        ])->assertOk();
        $url = $upload->json('avatar_url');
        $form = ['name' => 'Updated member', 'email' => $member->email];

        $this->putJson(route('profile.update'), $form + ['edit_version' => $upload->json('edit_version'), 'city' => 'Out of list'])
            ->assertUnprocessable()->assertJsonValidationErrors('city');
        $this->putJson(route('profile.update'), $form + ['edit_version' => '0'])
            ->assertUnprocessable()->assertJsonValidationErrors('edit_version');
        $this->put(route('profile.update'), $form + ['edit_version' => $upload->json('edit_version')])
            ->assertSessionHas('success');
        $this->assertSame($url, $member->fresh()->avatar);
        Storage::disk('public')->assertExists(str($url)->after('/storage/')->toString());
        $this->get(route('profile.show'))->assertOk()->assertSee('userAvatarFallback');
    }

    public function test_deletion_requires_an_authenticated_admin_and_a_delete_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        $url = route('admin.users.destroy', $member);

        $this->delete($url)->assertRedirect(route('login'));
        $this->actingAs($member)->delete($url)->assertForbidden();
        $this->actingAs($admin)->get($url)->assertOk();
        $this->assertNotSoftDeleted($member);
    }
}
