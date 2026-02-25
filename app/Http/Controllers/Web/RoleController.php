<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(Request $request): Response
    {
        $temple = auth()->user();

        $query = Role::withCount('members')
            ->where('temple_id', $temple->id);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('role_name', 'like', "%{$search}%");
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
            'availablePermissions' => $this->getAvailablePermissions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $temple = auth()->user();

        $validated = $request->validate([
            'role_name' => 'required|string|max:100|unique:roles,role_name,NULL,id,temple_id,'.$temple->id,
            'role' => 'nullable|array',
        ]);

        $validated['temple_id'] = $temple->id;

        Role::create($validated);

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit($id): Response
    {
        $temple = auth()->user();
        $role = Role::where('temple_id', $temple->id)->findOrFail($id);

        return Inertia::render('Role/Edit', [
            'role' => $role,
            'availablePermissions' => $this->getAvailablePermissions(),
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $temple = auth()->user();
        $role = Role::where('temple_id', $temple->id)->findOrFail($id);

        $validated = $request->validate([
            'role_name' => 'required|string|max:100|unique:roles,role_name,'.$id.',id,temple_id,'.$temple->id,
            'role' => 'nullable|array',
        ]);

        $role->update($validated);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $temple = auth()->user();
        $role = Role::where('temple_id', $temple->id)->findOrFail($id);

        // Check if role is assigned to any members
        $membersCount = Member::where('role_id', $id)->count();
        if ($membersCount > 0) {
            return redirect()->route('roles.index')->with('error', "Cannot delete role. It is assigned to {$membersCount} member(s).");
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    private function getAvailablePermissions(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'devotees' => 'Devotees Management',
            'members' => 'Members Management',
            'roles' => 'Roles Management',
            'poojas' => 'Poojas Management',
            'bookings' => 'Bookings Management',
            'categories' => 'Categories Management',
            'items' => 'Items Management',
            'suppliers' => 'Suppliers Management',
            'purchases' => 'Purchases Management',
            'reports' => 'Reports',
            'settings' => 'Settings',
        ];
    }
}
