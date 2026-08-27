<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword as ResetPasswordComponent;
use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_requires_post_and_ends_the_session(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->get('/logout')->assertMethodNotAllowed();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_api_login_is_rate_limited_and_returns_a_generic_error(): void
    {
        $payload = ['email' => 'unknown@example.test', 'password' => 'incorrect'];

        $this->postJson('/api/v1/login/new', $payload)
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Invalid email or password.']);

        for ($attempt = 2; $attempt <= 5; $attempt++) {
            $this->postJson('/api/v1/login/new', $payload)->assertUnauthorized();
        }

        $this->postJson('/api/v1/login/new', $payload)->assertTooManyRequests();
    }

    public function test_scanner_login_never_returns_shared_storage_credentials(): void
    {
        $user = $this->createUser(Role::SUPERADMIN);

        $this->postJson('/api/v1/login/new', [
            'email' => $user->email,
            'password' => 'Secret-password-123',
        ])->assertOk()
            ->assertJsonStructure(['status', 'token', 'start'])
            ->assertJsonMissingPath('sas_token')
            ->assertJsonMissingPath('endpoint');
    }

    public function test_password_reset_is_non_enumerating_hashed_and_single_use(): void
    {
        Notification::fake();
        $user = $this->createUser();

        Livewire::test(ForgotPassword::class)
            ->set('email', 'missing@example.test')
            ->call('submit')
            ->assertSet('status', true)
            ->assertHasNoErrors();

        Livewire::test(ForgotPassword::class)
            ->set('email', $user->email)
            ->call('submit')
            ->assertSet('status', true)
            ->assertHasNoErrors();

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->assertNotNull($token);
        $this->assertDatabaseMissing('password_reset_tokens', ['token' => $token]);

        Livewire::withQueryParams(['email' => $user->email])
            ->test(ResetPasswordComponent::class, ['token' => $token])
            ->set('password', 'New-password-456')
            ->set('password_confirmation', 'New-password-456')
            ->call('submit')
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('New-password-456', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    private function createUser(Role $role = Role::EMPLOYEE): User
    {
        $department = Department::create([
            'group' => 'GROUP',
            'center' => 'CENTER',
            'office' => 'GSD',
        ]);

        $user = User::create([
            'hris_number' => fake()->unique()->numerify('######'),
            'email' => fake()->unique()->safeEmail(),
            'role' => $role,
            'password' => 'Secret-password-123',
        ]);

        $user->employee()->create([
            'hris_number' => $user->hris_number,
            'last_name' => 'Tester',
            'first_name' => 'Security',
            'department_id' => $department->id,
            'appointment_status' => 'pbp',
        ]);

        return $user->fresh('employee.department');
    }
}
