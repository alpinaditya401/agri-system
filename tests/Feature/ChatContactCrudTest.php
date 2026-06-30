<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatContactCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_manage_chat_contacts(): void
    {
        $buyerRole = Role::create([
            'name' => 'buyer',
            'display_name' => 'Pembeli',
        ]);

        $farmerRole = Role::create([
            'name' => 'farmer',
            'display_name' => 'Petani',
        ]);

        $buyer = User::create([
            'role_id' => $buyerRole->id,
            'name' => 'Pembeli Test',
            'email' => 'buyer.chat@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $farmer = User::create([
            'role_id' => $farmerRole->id,
            'name' => 'Petani Cabai',
            'email' => 'farmer.chat@example.com',
            'password' => 'password',
            'district' => 'Sleman',
            'is_active' => true,
        ]);

        $this->actingAs($buyer)
            ->getJson(route('api.chat.contacts'))
            ->assertOk()
            ->assertJsonPath('data', []);

        $this->actingAs($buyer)
            ->getJson(route('api.chat.contacts.search', ['q' => 'Cabai']))
            ->assertOk()
            ->assertJsonPath('data.0.id', $farmer->id);

        $contactId = $this->actingAs($buyer)
            ->postJson(route('api.chat.contacts.store'), [
                'contact_user_id' => $farmer->id,
            ])
            ->assertCreated()
            ->json('data.id');

        $this->actingAs($buyer)
            ->patchJson(route('api.chat.contacts.update', $contactId), [
                'label' => 'Supplier Cabai',
                'is_pinned' => true,
            ])
            ->assertOk();

        $this->actingAs($buyer)
            ->getJson(route('api.chat.contacts'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $farmer->id)
            ->assertJsonPath('data.0.nama', 'Supplier Cabai')
            ->assertJsonPath('data.0.is_pinned', true);

        $this->actingAs($buyer)
            ->deleteJson(route('api.chat.contacts.destroy', $contactId))
            ->assertOk();

        $this->actingAs($buyer)
            ->getJson(route('api.chat.contacts'))
            ->assertOk()
            ->assertJsonPath('data', []);
    }
}
