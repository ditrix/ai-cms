<?php

namespace App\Http\Controllers;

use App\Http\Requests\Manager\ChangePasswordRequest;
use App\Http\Requests\Manager\StoreManagerRequest;
use App\Http\Requests\Manager\TransferClientsRequest;
use App\Http\Requests\Manager\UpdateManagerRequest;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class ManagerController extends Controller
{
    /**
     * Display a listing of the managers.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        $query = User::query();

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
        $allowedSortColumns = ['id', 'name', 'email', 'is_active'];

        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $managers = $query->paginate(15)->withQueryString();

        return Inertia::render('Managers/Index', [
            'managers' => $managers,
            'filters' => $request->only(['search', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Display the specified manager.
     */
    public function show(User $manager): Response
    {
        Gate::authorize('view', $manager);

        return Inertia::render('Managers/Show', [
            'manager' => $manager,
        ]);
    }

    /**
     * Store a newly created manager in storage.
     */
    public function store(StoreManagerRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('managers.index');
    }

    /**
     * Update the specified manager in storage.
     */
    public function update(UpdateManagerRequest $request, User $manager): RedirectResponse
    {
        Gate::authorize('update', $manager);

        $manager->fill($request->validated());
        $manager->save();

        return redirect()->back();
    }

    /**
     * Remove the specified manager from storage.
     */
    public function destroy(TransferClientsRequest $request, User $manager): RedirectResponse
    {
        Gate::authorize('delete', $manager);

        // Передача всех клиентов новому менеджеру
        Client::where('manager_id', $manager->id)
            ->update(['manager_id' => $request->new_manager_id]);

        $manager->delete();

        return redirect()->route('managers.index');
    }

    /**
     * Change the password for the specified manager.
     */
    public function changePassword(ChangePasswordRequest $request, User $manager): RedirectResponse
    {
        Gate::authorize('changePassword', $manager);

        $manager->password = Hash::make($request->password);
        $manager->save();

        return redirect()->back();
    }

    /**
     * Toggle the active status of the specified manager.
     */
    public function toggleActive(User $manager): RedirectResponse
    {
        Gate::authorize('update', $manager);

        $manager->is_active = ! $manager->is_active;
        $manager->save();

        return redirect()->back();
    }
}
