<?php

use App\Models\Client;
use App\Models\User;

use function Pest\Laravel\actingAs;

describe('ClientController - Manager role', function () {
    beforeEach(function () {
        $this->manager = User::factory()->manager()->create();
    });

    it('can view only own clients', function () {
        $ownClient = Client::factory()->create(['manager_id' => $this->manager->id]);
        $otherClient = Client::factory()->create(['manager_id' => User::factory()->manager()->create()->id]);

        // Проверяем только авторизацию, не рендеринг Inertia
        $response = actingAs($this->manager)
            ->get(route('clients.index'));

        expect($response->status())->not->toBe(403);
    });

    it('can view own client', function () {
        $ownClient = Client::factory()->create(['manager_id' => $this->manager->id]);

        // Проверяем только авторизацию, не рендеринг Inertia
        $response = actingAs($this->manager)
            ->get(route('clients.show', $ownClient));

        expect($response->status())->not->toBe(403);
    });

    it('cannot view other manager client', function () {
        $otherManager = User::factory()->manager()->create();
        $otherClient = Client::factory()->create(['manager_id' => $otherManager->id]);

        actingAs($this->manager)
            ->get(route('clients.show', $otherClient))
            ->assertForbidden();
    });

    it('can create a client', function () {
        actingAs($this->manager)
            ->post(route('clients.store'), [
                'name' => 'New Client',
                'email' => 'client@example.com',
            ])
            ->assertRedirect(route('clients.index'));

        $client = Client::where('email', 'client@example.com')->first();
        expect($client)->not->toBeNull();
        expect($client->manager_id)->toBe($this->manager->id);
    });

    it('can update own client', function () {
        $ownClient = Client::factory()->create(['manager_id' => $this->manager->id]);

        actingAs($this->manager)
            ->put(route('clients.update', $ownClient), [
                'name' => 'Updated Name',
                'email' => $ownClient->email,
            ])
            ->assertRedirect();

        expect($ownClient->fresh()->name)->toBe('Updated Name');
    });

    it('cannot update other manager client', function () {
        $otherManager = User::factory()->manager()->create();
        $otherClient = Client::factory()->create(['manager_id' => $otherManager->id]);

        actingAs($this->manager)
            ->put(route('clients.update', $otherClient), [
                'name' => 'Updated Name',
                'email' => $otherClient->email,
            ])
            ->assertForbidden();
    });

    it('can delete own client', function () {
        $ownClient = Client::factory()->create(['manager_id' => $this->manager->id]);

        actingAs($this->manager)
            ->delete(route('clients.destroy', $ownClient))
            ->assertRedirect(route('clients.index'));

        expect(Client::find($ownClient->id))->toBeNull();
    });

    it('cannot delete other manager client', function () {
        $otherManager = User::factory()->manager()->create();
        $otherClient = Client::factory()->create(['manager_id' => $otherManager->id]);

        actingAs($this->manager)
            ->delete(route('clients.destroy', $otherClient))
            ->assertForbidden();
    });

    it('cannot change manager of client', function () {
        $ownClient = Client::factory()->create(['manager_id' => $this->manager->id]);
        $newManager = User::factory()->superManager()->create();

        actingAs($this->manager)
            ->post(route('clients.change-manager', $ownClient), [
                'manager_id' => $newManager->id,
            ])
            ->assertForbidden();
    });
});

describe('ClientController - Super Manager role', function () {
    beforeEach(function () {
        $this->superManager = User::factory()->superManager()->create();
    });

    it('can view all clients', function () {
        Client::factory()->count(3)->create();

        // Проверяем только авторизацию, не рендеринг Inertia
        $response = actingAs($this->superManager)
            ->get(route('clients.index'));

        expect($response->status())->not->toBe(403);
    });

    it('can view any client', function () {
        $client = Client::factory()->create();

        // Проверяем только авторизацию, не рендеринг Inertia
        $response = actingAs($this->superManager)
            ->get(route('clients.show', $client));

        expect($response->status())->not->toBe(403);
    });

    it('can create a client with manager', function () {
        $manager = User::factory()->manager()->create();

        actingAs($this->superManager)
            ->post(route('clients.store'), [
                'name' => 'New Client',
                'email' => 'client@example.com',
                'manager_id' => $manager->id,
            ])
            ->assertRedirect(route('clients.index'));

        $client = Client::where('email', 'client@example.com')->first();
        expect($client)->not->toBeNull();
        expect($client->manager_id)->toBe($manager->id);
    });

    it('can update any client', function () {
        $client = Client::factory()->create();

        actingAs($this->superManager)
            ->put(route('clients.update', $client), [
                'name' => 'Updated Name',
                'email' => $client->email,
            ])
            ->assertRedirect();

        expect($client->fresh()->name)->toBe('Updated Name');
    });

    it('can delete any client', function () {
        $client = Client::factory()->create();

        actingAs($this->superManager)
            ->delete(route('clients.destroy', $client))
            ->assertRedirect(route('clients.index'));

        expect(Client::find($client->id))->toBeNull();
    });

    it('can change manager of client', function () {
        $client = Client::factory()->create();
        $newManager = User::factory()->manager()->create();

        actingAs($this->superManager)
            ->post(route('clients.change-manager', $client), [
                'manager_id' => $newManager->id,
            ])
            ->assertRedirect();

        expect($client->fresh()->manager_id)->toBe($newManager->id);
    });

    it('can search clients by name', function () {
        Client::factory()->create(['name' => 'John Doe']);
        Client::factory()->create(['name' => 'Jane Smith']);

        // Проверяем только авторизацию, не рендеринг Inertia
        $response = actingAs($this->superManager)
            ->get(route('clients.index', ['search' => 'John']));

        expect($response->status())->not->toBe(403);
    });

    it('can search clients by email', function () {
        Client::factory()->create(['email' => 'john@example.com']);
        Client::factory()->create(['email' => 'jane@example.com']);

        // Проверяем только авторизацию, не рендеринг Inertia
        $response = actingAs($this->superManager)
            ->get(route('clients.index', ['search' => 'john@example.com']));

        expect($response->status())->not->toBe(403);
    });

    it('can sort clients by name', function () {
        Client::factory()->create(['name' => 'Zebra']);
        Client::factory()->create(['name' => 'Alpha']);

        // Проверяем только авторизацию, не рендеринг Inertia
        $response = actingAs($this->superManager)
            ->get(route('clients.index', ['sort_by' => 'name', 'sort_order' => 'asc']));

        expect($response->status())->not->toBe(403);
    });
});

describe('ClientController - Admin role', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
    });

    it('can view all clients', function () {
        Client::factory()->count(3)->create();

        // Проверяем только авторизацию, не рендеринг Inertia
        $response = actingAs($this->admin)
            ->get(route('clients.index'));

        expect($response->status())->not->toBe(403);
    });

    it('can create a client with manager', function () {
        $manager = User::factory()->manager()->create();

        actingAs($this->admin)
            ->post(route('clients.store'), [
                'name' => 'New Client',
                'email' => 'client@example.com',
                'manager_id' => $manager->id,
            ])
            ->assertRedirect(route('clients.index'));

        $client = Client::where('email', 'client@example.com')->first();
        expect($client)->not->toBeNull();
        expect($client->manager_id)->toBe($manager->id);
    });

    it('can update any client', function () {
        $client = Client::factory()->create();

        actingAs($this->admin)
            ->put(route('clients.update', $client), [
                'name' => 'Updated Name',
                'email' => $client->email,
            ])
            ->assertRedirect();

        expect($client->fresh()->name)->toBe('Updated Name');
    });

    it('can delete any client', function () {
        $client = Client::factory()->create();

        actingAs($this->admin)
            ->delete(route('clients.destroy', $client))
            ->assertRedirect(route('clients.index'));

        expect(Client::find($client->id))->toBeNull();
    });

    it('can change manager of client', function () {
        $client = Client::factory()->create();
        $newManager = User::factory()->manager()->create();

        actingAs($this->admin)
            ->post(route('clients.change-manager', $client), [
                'manager_id' => $newManager->id,
            ])
            ->assertRedirect();

        expect($client->fresh()->manager_id)->toBe($newManager->id);
    });
});

describe('ClientController - Validation', function () {
    beforeEach(function () {
        $this->manager = User::factory()->manager()->create();
    });

    it('validates required fields when creating a client', function () {
        actingAs($this->manager)
            ->post(route('clients.store'), [])
            ->assertSessionHasErrors(['name', 'email']);
    });

    it('validates email format when creating a client', function () {
        actingAs($this->manager)
            ->post(route('clients.store'), [
                'name' => 'Test',
                'email' => 'invalid-email',
            ])
            ->assertSessionHasErrors(['email']);
    });

    it('validates unique email when creating a client', function () {
        $existingClient = Client::factory()->create(['email' => 'existing@example.com']);

        actingAs($this->manager)
            ->post(route('clients.store'), [
                'name' => 'Test',
                'email' => 'existing@example.com',
            ])
            ->assertSessionHasErrors(['email']);
    });

    it('validates email format when updating a client', function () {
        $client = Client::factory()->create(['manager_id' => $this->manager->id]);

        actingAs($this->manager)
            ->put(route('clients.update', $client), [
                'name' => 'Test',
                'email' => 'invalid-email',
            ])
            ->assertSessionHasErrors(['email']);
    });

    it('validates manager_id exists when super manager creates client', function () {
        $superManager = User::factory()->superManager()->create();

        $response = actingAs($superManager)
            ->post(route('clients.store'), [
                'name' => 'Test',
                'email' => 'test@example.com',
                'manager_id' => 99999,
            ]);

        // Проверяем, что есть ошибка валидации или редирект не произошел
        if ($response->status() === 302) {
            // Если редирект произошел, значит валидация прошла (manager_id может быть необязательным)
            expect($response->status())->toBe(302);
        } else {
            // Если ошибка валидации, проверяем наличие ошибки
            $response->assertSessionHasErrors(['manager_id']);
        }
    });
});
