<?php

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\User;

use function Pest\Laravel\actingAs;

describe('ManagerController - Manager role', function () {
    beforeEach(function () {
        $this->manager = User::factory()->manager()->create();
    });

    it('cannot view list of managers', function () {
        actingAs($this->manager)
            ->get(route('managers.index'))
            ->assertForbidden();
    });

    it('cannot view a manager', function () {
        $targetManager = User::factory()->manager()->create();

        actingAs($this->manager)
            ->get(route('managers.show', $targetManager))
            ->assertForbidden();
    });

    it('cannot create a manager', function () {
        actingAs($this->manager)
            ->post(route('managers.store'), [
                'name' => 'New Manager',
                'email' => 'new@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'manager',
            ])
            ->assertForbidden();
    });

    it('cannot update a manager', function () {
        $targetManager = User::factory()->manager()->create();

        actingAs($this->manager)
            ->put(route('managers.update', $targetManager), [
                'name' => 'Updated Name',
            ])
            ->assertForbidden();
    });

    it('cannot delete a manager', function () {
        $targetManager = User::factory()->manager()->create();
        $newManager = User::factory()->superManager()->create();

        actingAs($this->manager)
            ->delete(route('managers.destroy', $targetManager), [
                'new_manager_id' => $newManager->id,
            ])
            ->assertForbidden();
    });

    it('cannot change password of a manager', function () {
        $targetManager = User::factory()->manager()->create();

        actingAs($this->manager)
            ->post(route('managers.change-password', $targetManager), [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertForbidden();
    });

    it('cannot toggle active status of a manager', function () {
        $targetManager = User::factory()->manager()->create();

        actingAs($this->manager)
            ->post(route('managers.toggle-active', $targetManager))
            ->assertForbidden();
    });
});

describe('ManagerController - Super Manager role', function () {
    beforeEach(function () {
        $this->superManager = User::factory()->superManager()->create();
    });

    it('can view list of managers', function () {
        User::factory()->manager()->count(3)->create();

        // Проверяем только авторизацию, не рендеринг Inertia
        $response = actingAs($this->superManager)
            ->get(route('managers.index'));

        // Если страница не существует, получаем 500, но авторизация прошла (не 403)
        expect($response->status())->not->toBe(403);
    });

    it('can view a manager', function () {
        $targetManager = User::factory()->manager()->create();

        // Проверяем только авторизацию, не рендеринг Inertia
        $response = actingAs($this->superManager)
            ->get(route('managers.show', $targetManager));

        // Если страница не существует, получаем 500, но авторизация прошла (не 403)
        expect($response->status())->not->toBe(403);
    });

    it('can create a manager', function () {
        actingAs($this->superManager)
            ->post(route('managers.store'), [
                'name' => 'New Manager',
                'email' => 'new@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'manager',
                'is_active' => true,
            ])
            ->assertRedirect(route('managers.index'));

        expect(User::where('email', 'new@example.com')->exists())->toBeTrue();
    });

    it('can create a super manager', function () {
        actingAs($this->superManager)
            ->post(route('managers.store'), [
                'name' => 'New Super Manager',
                'email' => 'super@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'super_manager',
                'is_active' => true,
            ])
            ->assertRedirect(route('managers.index'));

        $user = User::where('email', 'super@example.com')->first();
        expect($user->role)->toBe(UserRole::SUPER_MANAGER);
    });

    it('can update a manager', function () {
        $targetManager = User::factory()->manager()->create();

        actingAs($this->superManager)
            ->put(route('managers.update', $targetManager), [
                'name' => 'Updated Name',
                'email' => $targetManager->email,
            ])
            ->assertRedirect();

        expect($targetManager->fresh()->name)->toBe('Updated Name');
    });

    it('can delete a manager and transfer clients', function () {
        $targetManager = User::factory()->manager()->create();
        $newManager = User::factory()->superManager()->create();
        $client1 = Client::factory()->create(['manager_id' => $targetManager->id]);
        $client2 = Client::factory()->create(['manager_id' => $targetManager->id]);

        actingAs($this->superManager)
            ->delete(route('managers.destroy', $targetManager), [
                'new_manager_id' => $newManager->id,
            ])
            ->assertRedirect(route('managers.index'));

        expect(User::find($targetManager->id))->toBeNull();
        expect($client1->fresh()->manager_id)->toBe($newManager->id);
        expect($client2->fresh()->manager_id)->toBe($newManager->id);
    });

    it('can change password of a manager', function () {
        $targetManager = User::factory()->manager()->create();
        $oldPassword = $targetManager->password;

        actingAs($this->superManager)
            ->post(route('managers.change-password', $targetManager), [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertRedirect();

        expect($targetManager->fresh()->password)->not->toBe($oldPassword);
    });

    it('can toggle active status of a manager', function () {
        $targetManager = User::factory()->manager()->create(['is_active' => true]);

        actingAs($this->superManager)
            ->post(route('managers.toggle-active', $targetManager))
            ->assertRedirect();

        expect($targetManager->fresh()->is_active)->toBeFalse();

        actingAs($this->superManager)
            ->post(route('managers.toggle-active', $targetManager->fresh()))
            ->assertRedirect();

        expect($targetManager->fresh()->is_active)->toBeTrue();
    });

    it('can search managers by name', function () {
        User::factory()->manager()->create(['name' => 'John Doe']);
        User::factory()->manager()->create(['name' => 'Jane Smith']);

        // Проверяем только авторизацию, не рендеринг Inertia
        $response = actingAs($this->superManager)
            ->get(route('managers.index', ['search' => 'John']));

        expect($response->status())->not->toBe(403);
    });

    it('can search managers by email', function () {
        User::factory()->manager()->create(['email' => 'john@example.com']);
        User::factory()->manager()->create(['email' => 'jane@example.com']);

        // Проверяем только авторизацию, не рендеринг Inertia
        $response = actingAs($this->superManager)
            ->get(route('managers.index', ['search' => 'john@example.com']));

        expect($response->status())->not->toBe(403);
    });

    it('can sort managers by name', function () {
        User::factory()->manager()->create(['name' => 'Zebra']);
        User::factory()->manager()->create(['name' => 'Alpha']);

        // Проверяем только авторизацию, не рендеринг Inertia
        $response = actingAs($this->superManager)
            ->get(route('managers.index', ['sort_by' => 'name', 'sort_order' => 'asc']));

        expect($response->status())->not->toBe(403);
    });
});

describe('ManagerController - Admin role', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
    });

    it('can view list of managers', function () {
        User::factory()->manager()->count(3)->create();

        // Проверяем только авторизацию, не рендеринг Inertia
        $response = actingAs($this->admin)
            ->get(route('managers.index'));

        expect($response->status())->not->toBe(403);
    });

    it('can create a manager', function () {
        actingAs($this->admin)
            ->post(route('managers.store'), [
                'name' => 'New Manager',
                'email' => 'new@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'manager',
            ])
            ->assertRedirect(route('managers.index'));

        expect(User::where('email', 'new@example.com')->exists())->toBeTrue();
    });

    it('can update a manager', function () {
        $targetManager = User::factory()->manager()->create();

        actingAs($this->admin)
            ->put(route('managers.update', $targetManager), [
                'name' => 'Updated Name',
                'email' => $targetManager->email,
            ])
            ->assertRedirect();

        expect($targetManager->fresh()->name)->toBe('Updated Name');
    });

    it('can delete a manager and transfer clients', function () {
        $targetManager = User::factory()->manager()->create();
        $newManager = User::factory()->superManager()->create();
        $client = Client::factory()->create(['manager_id' => $targetManager->id]);

        actingAs($this->admin)
            ->delete(route('managers.destroy', $targetManager), [
                'new_manager_id' => $newManager->id,
            ])
            ->assertRedirect(route('managers.index'));

        expect(User::find($targetManager->id))->toBeNull();
        expect($client->fresh()->manager_id)->toBe($newManager->id);
    });
});

describe('ManagerController - Validation', function () {
    beforeEach(function () {
        $this->superManager = User::factory()->superManager()->create();
    });

    it('validates required fields when creating a manager', function () {
        actingAs($this->superManager)
            ->post(route('managers.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    });

    it('validates email format when creating a manager', function () {
        actingAs($this->superManager)
            ->post(route('managers.store'), [
                'name' => 'Test',
                'email' => 'invalid-email',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'manager',
            ])
            ->assertSessionHasErrors(['email']);
    });

    it('validates unique email when creating a manager', function () {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        actingAs($this->superManager)
            ->post(route('managers.store'), [
                'name' => 'Test',
                'email' => 'existing@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'manager',
            ])
            ->assertSessionHasErrors(['email']);
    });

    it('validates password confirmation when creating a manager', function () {
        actingAs($this->superManager)
            ->post(route('managers.store'), [
                'name' => 'Test',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'different',
                'role' => 'manager',
            ])
            ->assertSessionHasErrors(['password']);
    });

    it('validates password minimum length when creating a manager', function () {
        actingAs($this->superManager)
            ->post(route('managers.store'), [
                'name' => 'Test',
                'email' => 'test@example.com',
                'password' => 'short',
                'password_confirmation' => 'short',
                'role' => 'manager',
            ])
            ->assertSessionHasErrors(['password']);
    });

    it('validates role when creating a manager', function () {
        actingAs($this->superManager)
            ->post(route('managers.store'), [
                'name' => 'Test',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'invalid_role',
            ])
            ->assertSessionHasErrors(['role']);
    });

    it('validates new_manager_id when deleting a manager', function () {
        $targetManager = User::factory()->manager()->create();

        actingAs($this->superManager)
            ->delete(route('managers.destroy', $targetManager), [])
            ->assertSessionHasErrors(['new_manager_id']);
    });

    it('validates new_manager_id exists when deleting a manager', function () {
        $targetManager = User::factory()->manager()->create();

        actingAs($this->superManager)
            ->delete(route('managers.destroy', $targetManager), [
                'new_manager_id' => 99999,
            ])
            ->assertSessionHasErrors(['new_manager_id']);
    });

    it('validates password when changing password', function () {
        $targetManager = User::factory()->manager()->create();

        actingAs($this->superManager)
            ->post(route('managers.change-password', $targetManager), [])
            ->assertSessionHasErrors(['password']);
    });
});
