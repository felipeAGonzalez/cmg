<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnesthesiaNoteTemplateRequest;
use App\Http\Requests\UpdateAnesthesiaNoteTemplateRequest;
use App\Models\AnesthesiaNoteTemplate;

class AnesthesiaNoteTemplateController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = AnesthesiaNoteTemplate::query()->with('owner');
        if (!$user->isAdmin()) {
            $query->where('owner_id', $user->id);
        }

        $templates = $query->orderBy('name')->get();

        return view('anesthesia-note-templates.index', [
            'templates' => $templates,
            'isAdmin'   => $user->isAdmin(),
        ]);
    }

    public function create()
    {
        return view('anesthesia-note-templates.create', [
            'sections' => config('anesthesia_note_template_sections'),
        ]);
    }

    public function store(StoreAnesthesiaNoteTemplateRequest $request)
    {
        $data = $request->validated();
        $data['owner_id'] = auth()->id();

        $template = AnesthesiaNoteTemplate::create($data);

        return redirect()->route('anesthesiaNoteTemplates.show', $template)
            ->with('success', 'Plantilla creada correctamente.');
    }

    public function show(AnesthesiaNoteTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: true);

        return view('anesthesia-note-templates.show', [
            'template' => $template->load('owner'),
            'sections' => config('anesthesia_note_template_sections'),
        ]);
    }

    public function edit(AnesthesiaNoteTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        return view('anesthesia-note-templates.edit', [
            'template' => $template,
            'sections' => config('anesthesia_note_template_sections'),
        ]);
    }

    public function update(UpdateAnesthesiaNoteTemplateRequest $request, AnesthesiaNoteTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        $template->update($request->validated());

        return redirect()->route('anesthesiaNoteTemplates.show', $template)
            ->with('success', 'Plantilla actualizada correctamente.');
    }

    public function duplicate(AnesthesiaNoteTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        $copy = $template->replicate();
        $copy->owner_id = auth()->id();
        $copy->name = $template->name . ' (copia)';
        $copy->save();

        return redirect()->route('anesthesiaNoteTemplates.edit', $copy)
            ->with('success', 'Plantilla duplicada. Ajusta los datos según necesites.');
    }

    public function destroy(AnesthesiaNoteTemplate $template)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $template->owner_id !== $user->id) {
            abort(403);
        }

        $template->delete();

        return redirect()->route('anesthesiaNoteTemplates.index')
            ->with('success', 'Plantilla eliminada.');
    }

    public function content(AnesthesiaNoteTemplate $template)
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

    protected function authorizeAccess(AnesthesiaNoteTemplate $template, bool $allowAdminRead): void
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
