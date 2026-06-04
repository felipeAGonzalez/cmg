<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordChangeController extends Controller
{
    public function create(): View
    {
        return view('passwords.change');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password'         => 'required|string|min:8|confirmed',
        ], [
            'current_password.required'      => 'Debes ingresar tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.required'              => 'La nueva contraseña es obligatoria.',
            'password.min'                   => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'             => 'Las contraseñas no coinciden.',
        ]);

        $request->user()->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return redirect()->route('home')
            ->with('success', 'Contraseña actualizada correctamente.');
    }
}
