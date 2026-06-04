<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    public function index(): RedirectResponse
    {
        $user = auth()->user();

        return match ($user->role) {
            'doctor'                 => redirect()->route('doctor.myPatients'),
            'admin', 'nurse', 'root' => redirect()->route('rooms.index'),
            default                  => $this->handleUnknownRole(),
        };
    }

    private function handleUnknownRole(): RedirectResponse
    {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('error', 'Tu cuenta no tiene un rol válido para acceder a este sistema. Contacta al administrador.');
    }
}
