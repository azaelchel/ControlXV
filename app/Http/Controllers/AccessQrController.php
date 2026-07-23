<?php

namespace App\Http\Controllers;

use App\Models\Companion;
use App\Models\Guest;
use App\Models\TableAssignment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessQrController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input("q", ""));
        $group = trim((string) $request->input("group", ""));
        $perPage = in_array((int) $request->integer("per_page"), [10, 25, 50, 100], true)
            ? (int) $request->integer("per_page")
            : 25;

        $guests = Guest::query()
            ->where("status", "Confirmado")
            ->when($search !== "", function ($query) use ($search) {
                $needle = "%".strtolower($search)."%";
                $query->where(function ($builder) use ($needle) {
                    $builder
                        ->whereRaw("LOWER(name) LIKE ?", [$needle])
                        ->orWhereRaw("LOWER(prefix) LIKE ?", [$needle])
                        ->orWhereRaw("LOWER(phone) LIKE ?", [$needle])
                        ->orWhereRaw("LOWER(group_name) LIKE ?", [$needle])
                        ->orWhereRaw("LOWER(sponsor) LIKE ?", [$needle])
                        ->orWhereExists(function ($subquery) use ($needle) {
                            $subquery
                                ->selectRaw("1")
                                ->from("companions")
                                ->whereColumn("companions.invited_group", "guests.name")
                                ->where("companions.active", true)
                                ->where(function ($personQuery) use ($needle) {
                                    $personQuery
                                        ->whereRaw("LOWER(companions.name) LIKE ?", [$needle])
                                        ->orWhereExists(function ($tableQuery) use ($needle) {
                                            $tableQuery
                                                ->selectRaw("1")
                                                ->from("table_assignments")
                                                ->join("event_tables", "event_tables.id", "=", "table_assignments.event_table_id")
                                                ->whereColumn("table_assignments.companion_id", "companions.id")
                                                ->where("table_assignments.active", true)
                                                ->where("event_tables.active", true)
                                                ->whereRaw("LOWER(event_tables.name) LIKE ?", [$needle]);
                                        });
                                });
                        });
                });
            })
            ->when($group !== "", fn ($query) => $query->where("group_name", $group))
            ->orderBy("group_name")
            ->orderBy("name")
            ->paginate($perPage)
            ->withQueryString();

        $groups = Guest::query()
            ->where("status", "Confirmado")
            ->pluck("group_name")
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $guestNames = $guests->getCollection()->pluck("name");

        $companions = Companion::query()
            ->whereIn("invited_group", $guestNames)
            ->orderBy("invited_group")
            ->orderBy("name")
            ->get();

        $assignmentsByCompanion = TableAssignment::query()
            ->with("table")
            ->whereIn("companion_id", $companions->pluck("id"))
            ->get()
            ->keyBy("companion_id");

        $detailsByGuest = $companions
            ->groupBy("invited_group")
            ->map(function ($people) use ($assignmentsByCompanion) {
                $rows = $people->map(function (Companion $companion) use ($assignmentsByCompanion) {
                    $assignment = $assignmentsByCompanion->get($companion->id);

                    return [
                        "name" => $companion->name,
                        "type" => $companion->type ?: "—",
                        "sex" => $companion->sex ?: "",
                        "table" => $assignment?->table?->name,
                    ];
                })->values();

                $tables = $rows
                    ->pluck("table")
                    ->filter()
                    ->unique()
                    ->values();

                return [
                    "people" => $rows,
                    "tables" => $tables,
                    "people_by_table" => $rows->groupBy(fn ($row) => $row["table"] ?: "Sin mesa")->map->values(),
                    "is_divided" => $tables->count() > 1,
                ];
            });

        $summary = [
            "confirmed" => Guest::where("status", "Confirmado")->count(),
            "with_qr" => Guest::where("status", "Confirmado")->whereNotNull("access_qr_data")->count(),
        ];

        return view("access-qrs.index", [
            "guests" => $guests,
            "groups" => $groups,
            "summary" => $summary,
            "search" => $search,
            "group" => $group,
            "perPage" => $perPage,
            "detailsByGuest" => $detailsByGuest,
        ]);
    }

    public function preview(Guest $guest): Response
    {
        abort_unless($guest->status === "Confirmado" && $guest->access_qr_data, 404);

        return response(base64_decode($guest->access_qr_data), 200)
            ->header("Content-Type", $guest->access_qr_mime ?: "image/png")
            ->header("Cache-Control", "private, max-age=300");
    }

    public function store(Request $request, Guest $guest): RedirectResponse
    {
        abort_unless($guest->status === "Confirmado", 404);

        $validated = $request->validate([
            "qr" => ["required", "image", "mimes:jpg,jpeg,png,webp", "max:5120"],
        ]);

        $guest->update([
            "access_qr_mime" => $validated["qr"]->getMimeType(),
            "access_qr_data" => base64_encode(file_get_contents($validated["qr"]->getRealPath())),
        ]);

        return back()->with("status", "QR actualizado para {$guest->name}.");
    }

    public function destroy(Guest $guest): RedirectResponse
    {
        abort_unless($guest->status === "Confirmado", 404);

        $guest->update([
            "access_qr_mime" => null,
            "access_qr_data" => null,
        ]);

        return back()->with("status", "QR eliminado para {$guest->name}.");
    }
}
