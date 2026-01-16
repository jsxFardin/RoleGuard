<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::query()
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function edit(User $user): Response
    {
        $user->load('roles');

        return Inertia::render('admin/Users/Edit', [
            'user' => $user,
            'roles' => Role::query()->orderBy('name')->get(),
            'selectedRoles' => $user->roles->pluck('id')->values(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        $user->roles()->sync($data['roles'] ?? []);

        return redirect()->route('admin.users.index')->with('success', 'User roles updated successfully.');
    }
}

