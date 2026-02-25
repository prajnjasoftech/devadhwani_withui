<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index(Request $request): Response
    {
        $temple = auth()->user();

        $query = Role::withCount('members')
            ->where('temple_id', $temple->id);

        if ($request->filled('search')) {
            $query->where('role_name', 'like', "%{$request->search}%");
        }

        $roles = $query->orderByDesc('id')->paginate(10)->withQueryString();

        return Inertia::render('Role/Index', [
            'roles' => $roles,
            'filters' => [
                'search' => $request->search ?? '',
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Role/Create', [
            'availablePermissions' => $this->roleService->getAvailablePermissions(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $temple = auth()->user();

        $data = $request->validated();
        $data['temple_id'] = $temple->id;

        $this->roleService->create($data);

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit($id): Response
    {
        $temple = auth()->user();
        $role = $this->roleService->findForTemple($id, $temple->id);

        if (! $role) {
            abort(404);
        }

        return Inertia::render('Role/Edit', [
            'role' => $role,
            'availablePermissions' => $this->roleService->getAvailablePermissions(),
        ]);
    }

    public function update(UpdateRoleRequest $request, $id): RedirectResponse
    {
        $temple = auth()->user();
        $role = $this->roleService->findForTemple($id, $temple->id);

        if (! $role) {
            abort(404);
        }

        $this->roleService->update($role, $request->validated());

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $temple = auth()->user();
        $role = $this->roleService->findForTemple($id, $temple->id);

        if (! $role) {
            abort(404);
        }

        if (! $this->roleService->canDelete($role)) {
            $count = $this->roleService->getMembersCount($role);

            return redirect()->route('roles.index')
                ->with('error', "Cannot delete role. It is assigned to {$count} member(s).");
        }

        $this->roleService->delete($role);

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
