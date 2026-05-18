<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $editId = $request->integer('edit');

        return view('users.index', [
            'users' => User::query()->orderByDesc('active')->orderBy('name')->get(),
            'editingUser' => $editId > 0 ? User::find($editId) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:users,name'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'active' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'password' => $validated['password'],
            'active' => (bool) ($validated['active'] ?? true),
        ]);

        return redirect()
            ->to($request->input('return_to', route('users.index')))
            ->with('status', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('users', 'name')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'active' => ['nullable', 'boolean'],
        ]);

        if ((int) $request->user()->id === (int) $user->id && ! $request->boolean('active', true)) {
            return redirect()
                ->to($request->input('return_to', route('users.index')))
                ->with('status', 'No puedes desactivar tu propio acceso desde aquí.');
        }

        $payload = [
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'active' => $request->boolean('active', false),
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);

        return redirect()
            ->to($request->input('return_to', route('users.index')))
            ->with('status', 'Usuario actualizado correctamente.');
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ((int) $request->user()->id === (int) $user->id && $user->active) {
            return redirect()
                ->to($request->input('return_to', route('users.index')))
                ->with('status', 'No puedes desactivar tu propio acceso desde aquí.');
        }

        $user->update(['active' => ! $user->active]);

        return redirect()
            ->to($request->input('return_to', route('users.index')))
            ->with('status', $user->active ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.');
    }
}
