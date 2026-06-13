<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpecialtyRequest;
use App\Http\Requests\UpdateSpecialtyRequest;
use App\Models\Specialty;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SpecialtyController extends Controller
{
    public function index(): View
    {
        $specialties = Specialty::withCount('users')
            ->orderBy('name')
            ->get();

        return view('admin.specialties.index', compact('specialties'));
    }

    public function create(): View
    {
        return view('admin.specialties.create');
    }

    public function store(StoreSpecialtyRequest $request): RedirectResponse
    {
        Specialty::create($request->validated());

        return redirect()->route('specialties.index')
            ->with('success', 'Especialidad creada correctamente.');
    }

    public function edit(Specialty $specialty): View
    {
        return view('admin.specialties.edit', compact('specialty'));
    }

    public function update(UpdateSpecialtyRequest $request, Specialty $specialty): RedirectResponse
    {
        $specialty->update($request->validated());

        return redirect()->route('specialties.index')
            ->with('success', 'Especialidad actualizada correctamente.');
    }

    /**
     * Activa o desactiva una especialidad. Una especialidad nunca se elimina
     * (para preservar registros históricos): solo se desactiva, lo que la
     * oculta del formulario de asignar médico.
     */
    public function toggle(Specialty $specialty): RedirectResponse
    {
        $specialty->update(['is_active' => ! $specialty->is_active]);

        $message = $specialty->is_active
            ? 'Especialidad activada.'
            : 'Especialidad desactivada.';

        return redirect()->route('specialties.index')->with('success', $message);
    }
}
