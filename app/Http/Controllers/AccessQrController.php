<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccessQrController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input("q", ""));
        $group = trim((string) $request->input("group", ""));

        $guests = Guest::query()
            ->where("status", "Confirmado")
            ->when($search !== "", function ($query) use ($search) {
                $needle = "%".strtolower($search)."%";
                $query->where(function ($builder) use ($needle) {
                    $builder
                        ->whereRaw("LOWER(name) LIKE ?", [$needle])
                        ->orWhereRaw("LOWER(prefix) LIKE ?", [$needle])
                        ->orWhereRaw("LOWER(phone) LIKE ?", [$needle])
                        ->orWhereRaw("LOWER(group_name) LIKE ?", [$needle]);
                });
            })
            ->when($group !== "", fn ($query) => $query->where("group_name", $group))
            ->orderBy("group_name")
            ->orderBy("name")
            ->paginate(40)
            ->withQueryString();

        $groups = Guest::query()
            ->where("status", "Confirmado")
            ->pluck("group_name")
            ->filter()
            ->unique()
            ->sort()
            ->values();

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
        ]);
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
