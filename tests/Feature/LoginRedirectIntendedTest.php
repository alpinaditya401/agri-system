<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRedirectIntendedTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_ignores_api_notification_intended_url(): void
    {
        $role = Role::create([
            'name' => 'buyer',
            'display_name' => 'Pembeli',
        ]);

        User::create([
            'role_id' => $role->id,
            'name' => 'Pembeli Redirect',
            'email' => 'buyer.redirect@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->withSession(['url.intended' => route('api.notifications.summary')])
            ->post(route('login'), [
                'email' => 'buyer.redirect@example.com',
                'password' => 'password',
            ])
            ->assertRedirect(route('dashboard'));
    }
}
