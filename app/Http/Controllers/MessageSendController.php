<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\MessageSend;
use App\Models\MessageTemplate;
use App\Services\PublicGuestLinkService;
use App\Support\CatalogOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageSendController extends Controller
{
    public function __construct(private readonly PublicGuestLinkService $linkService) {}

    public function index(Request $request): View
    {
        $statusFilter   = $request->string('status')->toString();
        $templateFilter = (int) $request->integer('template_id');

        $sends = MessageSend::query()
            ->with(['guest', 'template', 'publicLink', 'user'])
            ->when($templateFilter, fn ($q) => $q->where('message_template_id', $templateFilter))
            ->when($statusFilter, function ($q) use ($statusFilter) {
                $q->whereHas('guest', fn ($g) => $g->where('status', $statusFilter));
            })
            ->latest()
            ->paginate(40)
            ->withQueryString();

        $stats = $this->buildStats();

        return view('message-sends.index', [
            'sends'          => $sends,
            'stats'          => $stats,
            'templates'      => MessageTemplate::orderBy('position')->get(),
            'statuses'       => CatalogOptions::values('statuses'),
            'statusFilter'   => $statusFilter,
            'templateFilter' => $templateFilter,
        ]);
    }

    public function create(Request $request): View
    {
        $defaultTemplateId = (int) $request->integer('template_id');
        $defaultStatuses   = array_filter(explode(',', (string) $request->query('statuses', '')));

        $statuses = CatalogOptions::values('statuses');

        $guests = Guest::query()
            ->with('currentPublicLink', 'messageSends.template')
            ->orderBy('name')
            ->get();

        return view('message-sends.create', [
            'guests'            => $guests,
            'statuses'          => $statuses,
            'templates'         => MessageTemplate::orderBy('position')->get(),
            'defaultTemplateId' => $defaultTemplateId,
            'defaultStatuses'   => $defaultStatuses,
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
        $guests   = Guest::whereIn('id', $data['guest_ids'])
            ->orderBy('name')
            ->get();

        $rows = $guests->map(function (Guest $guest) use ($template) {
            $linkUrl = null;
            $linkInfo = null;

            if ($template->includes_link) {
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
                'eligible' => ! $template->includes_link || $linkUrl !== null,
            ];
        });

        return view('message-sends.prepare', [
            'template' => $template,
            'rows'     => $rows,
        ]);
    }

    public function store(Request $request): RedirectResponse
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
            return response()->json([
                'ok'      => true,
                'send_id' => $send->id,
            ]);
        }

        return back()->with('status', "Envío registrado para {$guest->name}.");
    }

    public function destroy(MessageSend $messageSend): RedirectResponse
    {
        $name = $messageSend->guest?->name ?? '—';
        $messageSend->delete();

        return redirect()
            ->route('message-sends.index')
            ->with('status', "Envío de {$name} eliminado del histórico.");
    }

    private function buildStats(): array
    {
        $today = MessageSend::whereDate('sent_at', today())->count();
        $week  = MessageSend::where('sent_at', '>=', now()->startOfWeek())->count();
        $total = MessageSend::count();

        $respondedToday = MessageSend::join('public_guest_links as l', 'l.id', '=', 'message_sends.public_guest_link_id')
            ->whereNotNull('l.responded_at')
            ->whereDate('l.responded_at', today())
            ->count();

        $pending = Guest::whereHas('messageSends')
            ->where(function ($q) {
                $q->whereDoesntHave('currentPublicLink', fn ($l) => $l->whereNotNull('responded_at'));
            })
            ->where('status', '!=', 'No asistirá')
            ->count();

        return [
            'today'           => $today,
            'week'            => $week,
            'total'           => $total,
            'responded_today' => $respondedToday,
            'pending'         => $pending,
        ];
    }
}
