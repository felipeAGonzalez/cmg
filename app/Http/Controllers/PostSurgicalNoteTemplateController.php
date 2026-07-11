<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostSurgicalNoteTemplateRequest;
use App\Http\Requests\UpdatePostSurgicalNoteTemplateRequest;
use App\Models\PostSurgicalNoteTemplate;

class PostSurgicalNoteTemplateController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = PostSurgicalNoteTemplate::query()->with('owner');
        if (!$user->isAdmin()) {
            $query->where('owner_id', $user->id);
        }

        $templates = $query->orderBy('name')->get();

        return view('post-surgical-note-templates.index', [
            'templates' => $templates,
            'isAdmin'   => $user->isAdmin(),
        ]);
    }

    public function create()
    {
        return view('post-surgical-note-templates.create', [
            'sections' => config('post_surgical_note_template_sections'),
        ]);
    }

    public function store(StorePostSurgicalNoteTemplateRequest $request)
    {
        $data = $request->validated();
        $data['owner_id'] = auth()->id();

        $template = PostSurgicalNoteTemplate::create($data);

        return redirect()->route('postSurgicalNoteTemplates.show', $template)
            ->with('success', 'Plantilla creada correctamente.');
    }

    public function show(PostSurgicalNoteTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: true);

        return view('post-surgical-note-templates.show', [
            'template' => $template->load('owner'),
            'sections' => config('post_surgical_note_template_sections'),
        ]);
    }

    public function edit(PostSurgicalNoteTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        return view('post-surgical-note-templates.edit', [
            'template' => $template,
            'sections' => config('post_surgical_note_template_sections'),
        ]);
    }

    public function update(UpdatePostSurgicalNoteTemplateRequest $request, PostSurgicalNoteTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        $template->update($request->validated());

        return redirect()->route('postSurgicalNoteTemplates.show', $template)
            ->with('success', 'Plantilla actualizada correctamente.');
    }

    public function duplicate(PostSurgicalNoteTemplate $template)
    {
        $this->authorizeAccess($template, allowAdminRead: false);

        $copy = $template->replicate();
        $copy->owner_id = auth()->id();
        $copy->name = $template->name . ' (copia)';
        $copy->save();

        return redirect()->route('postSurgicalNoteTemplates.edit', $copy)
            ->with('success', 'Plantilla duplicada. Ajusta los datos según necesites.');
    }

    public function destroy(PostSurgicalNoteTemplate $template)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $template->owner_id !== $user->id) {
            abort(403);
        }

        $template->delete();

        return redirect()->route('postSurgicalNoteTemplates.index')
            ->with('success', 'Plantilla eliminada.');
    }

    public function content(PostSurgicalNoteTemplate $template)
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

    protected function authorizeAccess(PostSurgicalNoteTemplate $template, bool $allowAdminRead): void
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
