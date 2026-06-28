<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalHistoryTemplateRequest;
use App\Http\Requests\UpdateMedicalHistoryTemplateRequest;
use App\Models\MedicalHistoryTemplate;

class MedicalHistoryTemplateController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = MedicalHistoryTemplate::query()->with('owner');
        if (!$user->isAdmin()) {
            $query->where('owner_id', $user->id);
        }

        $templates = $query->orderBy('name')->get();

        return view('medical-history-templates.index', [
            'templates' => $templates,
            'isAdmin'   => $user->isAdmin(),
        ]);
    }

    public function create()
    {
        return view('medical-history-templates.create', [
            'sections' => config('medical_template_sections'),
        ]);
    }

    public function store(StoreMedicalHistoryTemplateRequest $request)
    {
        $data = $request->validated();
        $data['owner_id'] = auth()->id();

        $template = MedicalHistoryTemplate::create($data);

        return redirect()->route('medicalHistoryTemplates.show', $template)
            ->with('success', 'Plantilla creada correctamente.');
    }

    public function show(MedicalHistoryTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: true);

        return view('medical-history-templates.show', [
            'template' => $template->load('owner'),
            'sections' => config('medical_template_sections'),
        ]);
    }

    public function edit(MedicalHistoryTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        return view('medical-history-templates.edit', [
            'template' => $template,
            'sections' => config('medical_template_sections'),
        ]);
    }

    public function update(UpdateMedicalHistoryTemplateRequest $request, MedicalHistoryTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        $template->update($request->validated());

        return redirect()->route('medicalHistoryTemplates.show', $template)
            ->with('success', 'Plantilla actualizada correctamente.');
    }

    public function duplicate(MedicalHistoryTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        $copy = $template->replicate();
        $copy->owner_id = auth()->id();
        $copy->name = $template->name . ' (copia)';
        $copy->save();

        return redirect()->route('medicalHistoryTemplates.edit', $copy)
            ->with('success', 'Plantilla duplicada. Ajusta los datos según necesites.');
    }

    public function destroy(MedicalHistoryTemplate $template)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $template->owner_id !== $user->id) {
            abort(403);
        }

        $template->delete();

        return redirect()->route('medicalHistoryTemplates.index')
            ->with('success', 'Plantilla eliminada.');
    }

    public function content(MedicalHistoryTemplate $template)
    {
        $user = auth()->user();

        $canAccess = $template->owner_id === $user->id
            || $user->isAdmin()
            || $user->isNurse();

        if (!$canAccess) {
            abort(403);
        }

        return response()->json([
            'sections' => $template->sections(),
        ]);
    }

    protected function authorizeAccess(MedicalHistoryTemplate $template, bool $allowAdminRead): void
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
