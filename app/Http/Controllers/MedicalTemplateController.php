<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalTemplateRequest;
use App\Http\Requests\UpdateMedicalTemplateRequest;
use App\Models\MedicalTemplate;

class MedicalTemplateController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = MedicalTemplate::query()->with('owner');
        if (!$user->isAdmin()) {
            $query->where('owner_id', $user->id);
        }

        $templates = $query->orderBy('name')->get();

        return view('medical-templates.index', [
            'templates' => $templates,
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    public function create()
    {
        return view('medical-templates.create', [
            'sections' => config('medical_template_sections'),
        ]);
    }

    public function store(StoreMedicalTemplateRequest $request)
    {
        $data = $request->validated();
        $data['owner_id'] = auth()->id();

        $template = MedicalTemplate::create($data);

        return redirect()->route('medicalTemplates.show', $template)
            ->with('success', 'Plantilla creada correctamente.');
    }

    public function show(MedicalTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: true);

        return view('medical-templates.show', [
            'template' => $template->load('owner'),
            'sections' => config('medical_template_sections'),
        ]);
    }

    public function edit(MedicalTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        return view('medical-templates.edit', [
            'template' => $template,
            'sections' => config('medical_template_sections'),
        ]);
    }

    public function update(UpdateMedicalTemplateRequest $request, MedicalTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        $template->update($request->validated());

        return redirect()->route('medicalTemplates.show', $template)
            ->with('success', 'Plantilla actualizada correctamente.');
    }

    public function duplicate(MedicalTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        $copy = $template->replicate();
        $copy->owner_id = auth()->id();
        $copy->name = $template->name . ' (copia)';
        $copy->save();

        return redirect()->route('medicalTemplates.edit', $copy)
            ->with('success', 'Plantilla duplicada. Ajusta los datos según necesites.');
    }

    public function destroy(MedicalTemplate $template)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $template->owner_id !== $user->id) {
            abort(403);
        }

        $template->delete();

        return redirect()->route('medicalTemplates.index')
            ->with('success', 'Plantilla eliminada.');
    }

    protected function authorizeAccess(MedicalTemplate $template, bool $allowAdminRead): void
    {
        $user = auth()->user();

        if ($template->owner_id === $user->id) {
            return;
        }

        if ($allowAdminRead && $user->isAdmin()) {
            return;
        }

        abort(403, 'No tienes permiso para acceder a esta plantilla.');
    }
}
