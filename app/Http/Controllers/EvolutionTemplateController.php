<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEvolutionTemplateRequest;
use App\Http\Requests\UpdateEvolutionTemplateRequest;
use App\Models\EvolutionTemplate;

class EvolutionTemplateController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = EvolutionTemplate::query()->with('owner');
        if (!$user->isAdmin()) {
            $query->where('owner_id', $user->id);
        }

        $templates = $query->orderBy('name')->get();

        return view('evolution-templates.index', [
            'templates' => $templates,
            'isAdmin'   => $user->isAdmin(),
        ]);
    }

    public function create()
    {
        return view('evolution-templates.create', [
            'sections' => config('evolution_template_sections'),
        ]);
    }

    public function store(StoreEvolutionTemplateRequest $request)
    {
        $data = $request->validated();
        $data['owner_id'] = auth()->id();

        $template = EvolutionTemplate::create($data);

        return redirect()->route('evolutionTemplates.show', $template)
            ->with('success', 'Plantilla creada correctamente.');
    }

    public function show(EvolutionTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: true);

        return view('evolution-templates.show', [
            'template' => $template->load('owner'),
            'sections' => config('evolution_template_sections'),
        ]);
    }

    public function edit(EvolutionTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        return view('evolution-templates.edit', [
            'template' => $template,
            'sections' => config('evolution_template_sections'),
        ]);
    }

    public function update(UpdateEvolutionTemplateRequest $request, EvolutionTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        $template->update($request->validated());

        return redirect()->route('evolutionTemplates.show', $template)
            ->with('success', 'Plantilla actualizada correctamente.');
    }

    public function duplicate(EvolutionTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        $copy = $template->replicate();
        $copy->owner_id = auth()->id();
        $copy->name = $template->name . ' (copia)';
        $copy->save();

        return redirect()->route('evolutionTemplates.edit', $copy)
            ->with('success', 'Plantilla duplicada. Ajusta los datos según necesites.');
    }

    public function destroy(EvolutionTemplate $template)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $template->owner_id !== $user->id) {
            abort(403);
        }

        $template->delete();

        return redirect()->route('evolutionTemplates.index')
            ->with('success', 'Plantilla eliminada.');
    }

    public function content(EvolutionTemplate $template)
    {
        $user = auth()->user();

        $canAccess = $template->owner_id === $user->id || $user->isAdmin();

        if (!$canAccess) {
            abort(403);
        }

        return response()->json([
            'sections' => $template->sections(),
        ]);
    }

    protected function authorizeAccess(EvolutionTemplate $template, bool $allowAdminRead): void
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
