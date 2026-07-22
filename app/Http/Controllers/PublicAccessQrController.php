<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\PublicGuestLink;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PublicAccessQrController extends Controller
{
    public function show(Request $request, Guest $guest, string $token): View
    {
        $link = PublicGuestLink::query()
            ->where("guest_id", $guest->id)
            ->where("token", $token)
            ->where("mode", "access_qr")
            ->firstOrFail();

        $isPreviewBot = $this->isPreviewBot($request->userAgent() ?? "");

        if (! $isPreviewBot && ! $link->isExpired() && ! $link->opened_at) {
            $link->forceFill(["opened_at" => now()])->save();
        }

        return view("public.access-qr", [
            "guest" => $guest,
            "link" => $link,
            "eventName" => Setting::get("event_name", "XV años de Zugeily"),
            "eventDate" => Setting::get("event_date", ""),
            "qrDataUrl" => $guest->access_qr_data ? "data:{$guest->access_qr_mime};base64,{$guest->access_qr_data}" : null,
            "links" => [
                "misa" => "https://www.google.com/maps/place/Catedral+de+Toluca+(San+José)/@19.2918265,-99.6572516,795m/data=!3m2!1e3!4b1!4m6!3m5!1s0x85cd89c1cd25d0df:0xd53533c114c4842b!8m2!3d19.2918265!4d-99.6572516!16s%2Fm%2F011l70n1!18m1!1e1?entry=ttu&g_ep=EgoyMDI2MDcxOS4wIKXMDSoASAFQAw%3D%3D",
                "recepcion" => "https://www.google.com/maps/place/Hacienda+La+Cúpula/@19.3735156,-99.762905,1138m/data=!3m2!1e3!4b1!4m6!3m5!1s0x85d279a176a993df:0x52b76288d325d50!8m2!3d19.3735156!4d-99.762905!16s%2Fg%2F11clt9pqc0!18m1!1e1?entry=ttu&g_ep=EgoyMDI2MDIyNC4wIKXMDSoASAFQAw%3D%3D",
                "liverpool" => "https://mesaderegalos.liverpool.com.mx/milistaderegalos/51958494",
                "amazon" => "https://www.amazon.com.mx/registries/gl/guest-view/14PJNGKVM9S8M?ref_=cm_sw_r_cp_ud_ggr-subnav-share_NKYRMZ3HBHRGKNSJ5J5B",
            ],
        ]);
    }

    private function isPreviewBot(string $agent): bool
    {
        $agent = strtolower($agent);

        return str_contains($agent, "whatsapp")
            || str_contains($agent, "facebookexternalhit")
            || str_contains($agent, "twitterbot")
            || str_contains($agent, "telegrambot");
    }
}
