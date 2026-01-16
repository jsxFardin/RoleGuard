<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index(Request $request): Response
    {
        $roles = Role::query()
            ->withCount('users')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/Roles/Create', [
            'permissionGroups' => config('permissions.groups'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:roles,slug'],
            'description' => ['nullable', 'string'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        $permIds = Permission::query()
            ->whereIn('slug', $data['permissions'] ?? [])
            ->pluck('id')
            ->all();

        $role->permissions()->sync($permIds);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): Response
    {
        $role->load('permissions');

        return Inertia::render('admin/Roles/Edit', [
            'role' => $role,
            'selectedPermissions' => $role->permissions->pluck('slug')->values(),
            'permissionGroups' => config('permissions.groups'),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:roles,slug,'.$role->id],
            'description' => ['nullable', 'string'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $role->update([
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        $permIds = Permission::query()
            ->whereIn('slug', $data['permissions'] ?? [])
            ->pluck('id')
            ->all();

        $role->permissions()->sync($permIds);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }
}

