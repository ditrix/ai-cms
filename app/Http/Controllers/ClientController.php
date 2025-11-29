<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    /**
     * Display a listing of the clients.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Client::class);

        $query = Client::query();

        // Фильтрация по роли
        if ($request->user()->isManager()) {
            // Менеджер видит только своих клиентов
            $query->where('manager_id', $request->user()->id);
        }
        // Супер-менеджер и админ видят всех клиентов (без фильтрации)

        // Поиск по name и email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'asc');
        $allowedSortColumns = ['id', 'name', 'email'];

        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Загрузка отношения manager для отображения
        $query->with('manager');

        $clients = $query->paginate(15)->withQueryString();

        // Получение списка менеджеров для супер-менеджера/админа
        $managers = null;
        if (in_array($request->user()->role->value, [UserRole::SUPER_MANAGER->value, UserRole::ADMIN->value])) {
            $managers = \App\Models\User::where('role', UserRole::MANAGER->value)
                ->where('is_active', true)
                ->get(['id', 'name', 'email']);
        }

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'managers' => $managers,
            'filters' => $request->only(['search', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client): Response
    {
        Gate::authorize('view', $client);

        $client->load('manager');

        return Inertia::render('Clients/Show', [
            'client' => $client,
        ]);
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        Gate::authorize('create', Client::class);

        $data = $request->validated();

        // Для менеджера автоматически устанавливаем manager_id
        if ($request->user()->isManager()) {
            $data['manager_id'] = $request->user()->id;
        }

        Client::create($data);

        return redirect()->route('clients.index');
    }

    /**
     * Update the specified client in storage.
     */
    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        Gate::authorize('update', $client);

        $client->fill($request->validated());
        $client->save();

        return redirect()->back();
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(Client $client): RedirectResponse
    {
        Gate::authorize('delete', $client);

        $client->delete();

        return redirect()->route('clients.index');
    }

    /**
     * Change manager for the specified client.
     */
    public function changeManager(Request $request, Client $client): RedirectResponse
    {
        Gate::authorize('update', $client);

        // Проверка роли (только супер-менеджер/админ)
        if (! in_array($request->user()->role->value, [UserRole::SUPER_MANAGER->value, UserRole::ADMIN->value])) {
            abort(403);
        }

        $request->validate([
            'manager_id' => [
                'required',
                'exists:users,id',
            ],
        ]);

        $client->manager_id = $request->manager_id;
        $client->save();

        return redirect()->back();
    }
}
