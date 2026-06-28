<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDischargeTemplateRequest;
use App\Http\Requests\UpdateDischargeTemplateRequest;
use App\Models\DischargeTemplate;

class DischargeTemplateController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = DischargeTemplate::query()->with('owner');
        if (!$user->isAdmin()) {
            $query->where('owner_id', $user->id);
        }

        $templates = $query->orderBy('name')->get();

        return view('discharge-templates.index', [
            'templates' => $templates,
            'isAdmin'   => $user->isAdmin(),
        ]);
    }

    public function create()
    {
        return view('discharge-templates.create', [
            'sections' => config('discharge_template_sections'),
        ]);
    }

    public function store(StoreDischargeTemplateRequest $request)
    {
        $data = $request->validated();
        $data['owner_id'] = auth()->id();

        $template = DischargeTemplate::create($data);

        return redirect()->route('dischargeTemplates.show', $template)
            ->with('success', 'Plantilla creada correctamente.');
    }

    public function show(DischargeTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: true);

        return view('discharge-templates.show', [
            'template' => $template->load('owner'),
            'sections' => config('discharge_template_sections'),
        ]);
    }

    public function edit(DischargeTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        return view('discharge-templates.edit', [
            'template' => $template,
            'sections' => config('discharge_template_sections'),
        ]);
    }

    public function update(UpdateDischargeTemplateRequest $request, DischargeTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        $template->update($request->validated());

        return redirect()->route('dischargeTemplates.show', $template)
            ->with('success', 'Plantilla actualizada correctamente.');
    }

    public function duplicate(DischargeTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        $copy = $template->replicate();
        $copy->owner_id = auth()->id();
        $copy->name = $template->name . ' (copia)';
        $copy->save();

        return redirect()->route('dischargeTemplates.edit', $copy)
            ->with('success', 'Plantilla duplicada. Ajusta los datos según necesites.');
    }

    public function destroy(DischargeTemplate $template)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $template->owner_id !== $user->id) {
            abort(403);
        }

        $template->delete();

        return redirect()->route('dischargeTemplates.index')
            ->with('success', 'Plantilla eliminada.');
    }

    public function content(DischargeTemplate $template)
    {
        $user = auth()->user();

        // Nurses may read any template (they're shown only doctor-owned templates in their UI)
        $canAccess = $template->owner_id === $user->id || $user->isAdmin() || $user->isNurse();

        if (!$canAccess) {
            abort(403);
        }

        return response()->json([
            'sections' => $template->sections(),
        ]);
    }

    protected function authorizeAccess(DischargeTemplate $template, bool $allowAdminRead): void
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
