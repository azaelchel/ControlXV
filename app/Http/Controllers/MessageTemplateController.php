<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    public function index(): View
    {
        $templates = MessageTemplate::withoutGlobalScope('active')
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return view('message-templates.index', [
            'templates' => $templates,
            'placeholders' => MessageTemplate::placeholders(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['position'] = (int) MessageTemplate::withoutGlobalScope('active')->max('position') + 1;

        MessageTemplate::create($data);

        return redirect()
            ->route('message-templates.index')
            ->with('status', 'Plantilla creada correctamente.');
    }

    public function update(Request $request, MessageTemplate $template): RedirectResponse
    {
        $template->update($this->validated($request));

        return redirect()
            ->route('message-templates.index')
            ->with('status', 'Plantilla actualizada.');
    }

    public function toggle(MessageTemplate $template): RedirectResponse
    {
        $template->update(['active' => ! $template->active]);

        return redirect()
            ->route('message-templates.index')
            ->with('status', $template->active ? 'Plantilla activada.' : 'Plantilla desactivada.');
    }

    public function destroy(MessageTemplate $template): RedirectResponse
    {
        $template->update(['active' => false]);

        return redirect()
            ->route('message-templates.index')
            ->with('status', 'Plantilla eliminada.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'kicker'      => ['nullable', 'string', 'max:80'],
            'content'     => ['required', 'string'],
        ]);

        $data['includes_link'] = str_contains($data['content'], '{link}');

        return $data;
    }
}
