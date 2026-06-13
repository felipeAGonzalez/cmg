<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::orderBy('name');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name_one', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        return view('users.index', compact('users', 'search'));
    }

    public function create(): View
    {
        $availableSpecialties = Specialty::active()->orderBy('name')->get();

        return view('users.create', compact('availableSpecialties'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $specialtyIds = $data['specialty_ids'] ?? [];
        unset($data['specialty_ids']);

        $user = User::create($data);

        $user->specialties()->sync($user->isDoctor() ? $specialtyIds : []);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user): View
    {
        $availableSpecialties = Specialty::active()->orderBy('name')->get();
        $userSpecialtyIds = $user->specialties->pluck('id')->toArray();

        return view('users.edit', compact('user', 'availableSpecialties', 'userSpecialtyIds'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $specialtyIds = $data['specialty_ids'] ?? [];
        unset($data['specialty_ids']);

        $user->update($data);

        // Solo los médicos conservan especialidades; al cambiar de rol se limpian.
        $user->specialties()->sync($user->isDoctor() ? $specialtyIds : []);

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        if ($user->isRoot()) {
            return back()->with('error', 'La cuenta de soporte del sistema no puede eliminarse.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario dado de baja correctamente.');
    }
}
