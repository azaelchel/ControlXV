<?php

namespace App\Http\Controllers;

use App\Models\Companion;
use App\Models\Guest;
use App\Models\MessageSend;
use App\Models\MessageTemplate;
use App\Models\PublicGuestLink;
use App\Services\PublicGuestLinkService;
use App\Support\CatalogOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MessageSendController extends Controller
{
    public function __construct(private readonly PublicGuestLinkService $linkService) {}

    public function index(Request $request): View
    {
        $statusFilter    = $request->string('status')->toString();
        $linkStateFilter = $request->string('link_state')->toString();
        $groupFilter     = $request->string('group')->toString();
        $nameQuery       = $request->string('q')->toString();
        $perPage         = (int) ($request->integer('per_page') ?: 25);
        $page            = (int) ($request->integer('page') ?: 1);

        $guests = Guest::query()
            ->with(['currentPublicLink', 'messageSends' => fn ($q) => $q->latest()->with('template')])
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->when($groupFilter, fn ($q) => $q->where('group_name', $groupFilter))
            ->when($nameQuery, fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($nameQuery) . '%']))
            ->orderBy('name')
            ->get();

        // Conteo de invitados registrados por familia (para mostrar boton si hay)
        $companionCounts = Companion::query()
            ->select('invited_group')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('invited_group')
            ->pluck('total', 'invited_group')
            ->all();

        $rows = $guests->map(fn (Guest $g) => $this->buildRow($g, $companionCounts));

        if ($linkStateFilter) {
            $rows = $rows->filter(fn ($r) => $r['state']['key'] === $linkStateFilter)->values();
        }

        $paginator = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('message-sends.index', [
            'rows'            => $paginator,
            'stats'           => $this->buildStats(),
            'statuses'        => CatalogOptions::values('statuses'),
            'groups'          => CatalogOptions::values('guest_groups'),
            'templates'       => MessageTemplate::orderBy('position')->get(),
            'statusFilter'    => $statusFilter,
            'linkStateFilter' => $linkStateFilter,
            'groupFilter'     => $groupFilter,
            'nameQuery'       => $nameQuery,
            'linkStates'      => $this->linkStateLabels(),
        ]);
    }

    public function create(Request $request): View
    {
        $defaultTemplateId = (int) $request->integer('template_id');

        $guests = Guest::query()
            ->with('currentPublicLink', 'messageSends.template')
            ->orderBy('name')
            ->get();

        return view('message-sends.create', [
            'guests'            => $guests,
            'statuses'          => CatalogOptions::values('statuses'),
            'categories'        => CatalogOptions::values('categories'),
            'groups'            => CatalogOptions::values('guest_groups'),
            'templates'         => MessageTemplate::orderBy('position')->get(),
            'defaultTemplateId' => $defaultTemplateId,
        ]);
    }

    public function history(Request $request): View
    {
        $templateFilter = (int) $request->integer('template_id');
        $statusFilter   = $request->string('status')->toString();
        $nameQuery      = $request->string('q')->toString();
        $dateFrom       = $request->string('from')->toString();
        $dateTo         = $request->string('to')->toString();

        $sends = MessageSend::query()
            ->with(['guest', 'template', 'publicLink', 'user'])
            ->when($templateFilter, fn ($q) => $q->where('message_template_id', $templateFilter))
            ->when($statusFilter, fn ($q) => $q->whereHas('guest', fn ($g) => $g->where('status', $statusFilter)))
            ->when($nameQuery, fn ($q) => $q->whereHas('guest', fn ($g) => $g->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($nameQuery) . '%'])))
            ->when($dateFrom, fn ($q) => $q->whereDate('sent_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('sent_at', '<=', $dateTo))
            ->latest('sent_at')
            ->paginate(30)
            ->withQueryString();

        $totalsByTemplate = MessageSend::query()
            ->selectRaw('message_template_id, COUNT(*) as total')
            ->groupBy('message_template_id')
            ->pluck('total', 'message_template_id');

        return view('message-sends.history', [
            'sends'            => $sends,
            'templates'        => MessageTemplate::orderBy('position')->get(),
            'statuses'         => CatalogOptions::values('statuses'),
            'templateFilter'   => $templateFilter,
            'statusFilter'     => $statusFilter,
            'nameQuery'        => $nameQuery,
            'dateFrom'         => $dateFrom,
            'dateTo'           => $dateTo,
            'totalsByTemplate' => $totalsByTemplate,
        ]);
    }

    public function prepare(Request $request): View
    {
        $data = $request->validate([
            'guest_ids'           => ['required', 'array', 'min:1'],
            'guest_ids.*'         => ['integer', 'exists:guests,id'],
            'message_template_id' => ['required', 'integer', 'exists:message_templates,id'],
        ]);

        $template = MessageTemplate::findOrFail($data['message_template_id']);
        $guests   = Guest::whereIn('id', $data['guest_ids'])->orderBy('name')->get();

        $rows = $guests->map(fn ($g) => $this->renderForSend($g, $template));

        return view('message-sends.prepare', ['template' => $template, 'rows' => $rows]);
    }

    public function companions(Guest $guest): JsonResponse
    {
        $companions = Companion::query()
            ->where('invited_group', $guest->name)
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->map(fn (Companion $c) => [
                'id'    => $c->id,
                'name'  => $c->name,
                'type'  => $c->type ?? '—',
                'sex'   => $c->sex ?? '',
                'notes' => $c->notes ?? '',
                'source' => $c->source,
                'created_at' => $c->created_at?->format('d/m/Y H:i'),
            ]);

        $byType = $companions->groupBy('type')->map->count();

        return response()->json([
            'guest_name' => $guest->name,
            'status'     => $guest->status,
            'companions' => $companions,
            'counts'     => [
                'total'        => $companions->count(),
                'adults'       => $byType['Adulto'] ?? 0,
                'adolescents'  => $byType['Adolescente'] ?? 0,
                'children'     => $byType['Niño'] ?? 0,
            ],
            'expected' => [
                'adults'      => (int) $guest->adults,
                'adolescents' => (int) $guest->adolescents,
                'children'    => (int) $guest->children,
                'total'       => (int) ($guest->adults + $guest->adolescents + $guest->children),
            ],
        ]);
    }

    public function renderOne(Request $request, Guest $guest): JsonResponse
    {
        $data = $request->validate([
            'message_template_id' => ['required', 'integer', 'exists:message_templates,id'],
        ]);

        $template = MessageTemplate::findOrFail($data['message_template_id']);
        $row = $this->renderForSend($guest, $template);

        return response()->json([
            'message'              => $row['message'],
            'public_guest_link_id' => $row['link']['id'] ?? null,
            'link_url'             => $row['link_url'],
            'eligible'             => $row['eligible'],
            'phone_intl'           => $this->phoneIntl($guest),
            'guest_name'           => $guest->name,
            'template_name'        => $template->name,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'guest_id'             => ['required', 'integer', 'exists:guests,id'],
            'message_template_id'  => ['required', 'integer', 'exists:message_templates,id'],
            'rendered_message'     => ['required', 'string'],
            'public_guest_link_id' => ['nullable', 'integer', 'exists:public_guest_links,id'],
        ]);

        $guest = Guest::findOrFail($data['guest_id']);

        $send = MessageSend::create([
            'guest_id'             => $guest->id,
            'message_template_id'  => $data['message_template_id'],
            'public_guest_link_id' => $data['public_guest_link_id'] ?? null,
            'user_id'              => $request->user()?->id,
            'rendered_message'     => $data['rendered_message'],
            'phone'                => $guest->phone,
            'sent_at'              => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'send_id' => $send->id]);
        }

        return back()->with('status', "Envío registrado para {$guest->name}.");
    }

    public function destroy(MessageSend $messageSend): RedirectResponse
    {
        $name = $messageSend->guest?->name ?? '—';
        $messageSend->delete();

        return redirect()
            ->route('message-sends.history')
            ->with('status', "Envío de {$name} eliminado del histórico.");
    }

    private function renderForSend(Guest $guest, MessageTemplate $template): array
    {
        $linkUrl = null;
        $linkInfo = null;

        if ($template->hasLinkPlaceholder()) {
            $link = $this->linkService->ensureLinkFor($guest);
            if ($link) {
                $linkUrl  = $this->linkService->linkUrl($guest, $link);
                $linkInfo = [
                    'id'         => $link->id,
                    'expires_at' => $link->expires_at,
                    'reused'     => $link->wasRecentlyCreated === false,
                ];
            }
        }

        return [
            'guest'    => $guest,
            'link'     => $linkInfo,
            'link_url' => $linkUrl,
            'message'  => $template->render($guest, $linkUrl),
            'eligible' => ! $template->hasLinkPlaceholder() || $linkUrl !== null,
        ];
    }

    private function buildRow(Guest $guest, array $companionCounts = []): array
    {
        $link = $guest->currentPublicLink;
        $lastSend = $guest->messageSends->first();

        return [
            'guest'           => $guest,
            'link'            => $link,
            'state'           => $this->describeLinkState($link),
            'last_send'       => $lastSend,
            'sends_count'     => $guest->messageSends->count(),
            'phone_intl'      => $this->phoneIntl($guest),
            'companion_count' => $companionCounts[$guest->name] ?? 0,
        ];
    }

    private function phoneIntl(Guest $guest): ?string
    {
        $clean = preg_replace('/[^0-9]/', '', $guest->phone ?? '');
        if (! $clean) return null;
        return strlen($clean) === 10 ? '52' . $clean : $clean;
    }

    private function describeLinkState(?PublicGuestLink $link): array
    {
        if (! $link) {
            return ['key' => 'none', 'label' => 'Sin link', 'class' => 'status-default'];
        }
        if ($link->responded_at) {
            return ['key' => 'responded', 'label' => 'Respondió', 'class' => 'status-confirmado'];
        }
        if ($link->closed_reason === 'cancelled') {
            return ['key' => 'cancelled', 'label' => 'Cancelado', 'class' => 'status-no-asistira'];
        }
        if ($link->isExpired()) {
            return ['key' => 'expired', 'label' => 'Venció', 'class' => 'status-no-asistira'];
        }
        if ($link->opened_at) {
            return ['key' => 'opened', 'label' => 'Abrió, no respondió', 'class' => 'status-pendiente'];
        }
        return ['key' => 'active', 'label' => 'Enviado, sin abrir', 'class' => 'status-considerado'];
    }

    private function linkStateLabels(): array
    {
        return [
            'none'      => 'Sin link',
            'active'    => 'Enviado, sin abrir',
            'opened'    => 'Abrió, no respondió',
            'responded' => 'Respondió',
            'expired'   => 'Venció',
            'cancelled' => 'Cancelado',
        ];
    }

    private function buildStats(): array
    {
        $today = MessageSend::whereDate('sent_at', today())->count();
        $week  = MessageSend::where('sent_at', '>=', now()->startOfWeek())->count();

        $responded = PublicGuestLink::whereNotNull('responded_at')
            ->where('is_current', true)
            ->count();

        $waiting = PublicGuestLink::whereNull('responded_at')
            ->where('is_current', true)
            ->where('expires_at', '>', now())
            ->where(function ($q) {
                $q->whereNull('closed_reason')->orWhere('closed_reason', '!=', 'cancelled');
            })
            ->count();

        $expired = PublicGuestLink::whereNull('responded_at')
            ->where('is_current', true)
            ->where('expires_at', '<=', now())
            ->count();

        $needsResend = Guest::whereHas('currentPublicLink', function ($q) {
                $q->whereNull('responded_at')->where('expires_at', '<=', now());
            })
            ->whereIn('status', ['Invitacion Enviada', 'Confirmado'])
            ->count();

        return [
            'today'        => $today,
            'week'         => $week,
            'responded'    => $responded,
            'waiting'      => $waiting,
            'expired'      => $expired,
            'needs_resend' => $needsResend,
        ];
    }
}
