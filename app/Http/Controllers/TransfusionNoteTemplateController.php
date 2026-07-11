<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransfusionNoteTemplateRequest;
use App\Http\Requests\UpdateTransfusionNoteTemplateRequest;
use App\Models\TransfusionNoteTemplate;

class TransfusionNoteTemplateController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = TransfusionNoteTemplate::query()->with('owner');
        if (!$user->isAdmin()) {
            $query->where('owner_id', $user->id);
        }

        $templates = $query->orderBy('name')->get();

        return view('transfusion-note-templates.index', [
            'templates' => $templates,
            'isAdmin'   => $user->isAdmin(),
        ]);
    }

    public function create()
    {
        return view('transfusion-note-templates.create', [
            'sections' => config('transfusion_note_template_sections'),
        ]);
    }

    public function store(StoreTransfusionNoteTemplateRequest $request)
    {
        $data = $request->validated();
        $data['owner_id'] = auth()->id();

        $template = TransfusionNoteTemplate::create($data);

        return redirect()->route('transfusionNoteTemplates.show', $template)
            ->with('success', 'Plantilla creada correctamente.');
    }

    public function show(TransfusionNoteTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: true);

        return view('transfusion-note-templates.show', [
            'template' => $template->load('owner'),
            'sections' => config('transfusion_note_template_sections'),
        ]);
    }

    public function edit(TransfusionNoteTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        return view('transfusion-note-templates.edit', [
            'template' => $template,
            'sections' => config('transfusion_note_template_sections'),
        ]);
    }

    public function update(UpdateTransfusionNoteTemplateRequest $request, TransfusionNoteTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        $template->update($request->validated());

        return redirect()->route('transfusionNoteTemplates.show', $template)
            ->with('success', 'Plantilla actualizada correctamente.');
    }

    public function duplicate(TransfusionNoteTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        $copy = $template->replicate();
        $copy->owner_id = auth()->id();
        $copy->name = $template->name . ' (copia)';
        $copy->save();

        return redirect()->route('transfusionNoteTemplates.edit', $copy)
            ->with('success', 'Plantilla duplicada. Ajusta los datos según necesites.');
    }

    public function destroy(TransfusionNoteTemplate $template)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $template->owner_id !== $user->id) {
            abort(403);
        }

        $template->delete();

        return redirect()->route('transfusionNoteTemplates.index')
            ->with('success', 'Plantilla eliminada.');
    }

    public function content(TransfusionNoteTemplate $template)
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

    protected function authorizeAccess(TransfusionNoteTemplate $template, bool $allowAdminRead): void
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
