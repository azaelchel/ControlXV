<?php

namespace App\Http\Controllers;

use App\Models\Companion;
use App\Models\Guest;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $summary = [
            'records' => (int) Guest::count(),
            'real_records' => (int) Guest::where('category', 'Real')->count(),
            'adults' => (int) Guest::sum('adults'),
            'adolescents' => (int) Guest::sum('adolescents'),
            'children' => (int) Guest::sum('children'),
            'total_people' => (int) Guest::query()->selectRaw('COALESCE(SUM(adults + adolescents + children), 0) as total')->value('total'),
            'sponsors' => (int) Guest::whereNotNull('sponsor')->where('sponsor', '!=', '')->count(),
            'companions' => (int) Companion::count(),
            'confirmed_records' => (int) Guest::where('status', 'Confirmado')->count(),
            'confirmed_adults' => (int) Guest::where('status', 'Confirmado')->sum('adults'),
            'confirmed_adolescents' => (int) Guest::where('status', 'Confirmado')->sum('adolescents'),
            'confirmed_children' => (int) Guest::where('status', 'Confirmado')->sum('children'),
            'confirmed_total_people' => (int) Guest::where('status', 'Confirmado')
                ->selectRaw('COALESCE(SUM(adults + adolescents + children), 0) as total')
                ->value('total'),
            // Métricas específicas de la categoría REAL (lo que realmente esperamos)
            'real_total_people' => (int) Guest::where('category', 'Real')
                ->selectRaw('COALESCE(SUM(adults + adolescents + children), 0) as total')
                ->value('total'),
            'real_confirmed_records' => (int) Guest::where('category', 'Real')->where('status', 'Confirmado')->count(),
            'real_confirmed_total_people' => (int) Guest::where('category', 'Real')->where('status', 'Confirmado')
                ->selectRaw('COALESCE(SUM(adults + adolescents + children), 0) as total')
                ->value('total'),
            'real_rejected_total_people' => (int) Guest::where('category', 'Real')->where('status', 'No asistirá')
                ->selectRaw('COALESCE(SUM(adults + adolescents + children), 0) as total')
                ->value('total'),
        ];

        $byGroup = Guest::query()
            ->select('group_name')
            ->selectRaw('COUNT(*) as records')
            ->selectRaw('SUM(adults) as adults')
            ->selectRaw('SUM(adolescents) as adolescents')
            ->selectRaw('SUM(children) as children')
            ->selectRaw('SUM(adults + adolescents + children) as total_people')
            ->selectRaw("SUM(CASE WHEN sponsor IS NOT NULL AND sponsor != '' THEN 1 ELSE 0 END) as sponsors")
            ->groupBy('group_name')
            ->orderBy('group_name')
            ->get();

        $byCategory = Guest::query()
            ->select('category')
            ->selectRaw('COUNT(*) as records')
            ->selectRaw('SUM(adults) as adults')
            ->selectRaw('SUM(adolescents) as adolescents')
            ->selectRaw('SUM(children) as children')
            ->selectRaw('SUM(adults + adolescents + children) as total_people')
            ->selectRaw("SUM(CASE WHEN sponsor IS NOT NULL AND sponsor != '' THEN 1 ELSE 0 END) as sponsors")
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        // Desglose por estatus de la categoría Real.
        // Nota: usamos 'people_sum' como alias porque 'total_people' colisiona con el
        // accessor del modelo Guest y devolveria siempre 0.
        $realByStatus = Guest::query()
            ->where('category', 'Real')
            ->select('status')
            ->selectRaw('COUNT(*) as records')
            ->selectRaw('SUM(adults + adolescents + children) as people_sum')
            ->groupBy('status')
            ->orderByRaw("
                CASE status
                    WHEN 'Considerado' THEN 1
                    WHEN 'Invitacion Enviada' THEN 2
                    WHEN 'Pendiente' THEN 3
                    WHEN 'Confirmado' THEN 4
                    WHEN 'No asistirá' THEN 5
                    WHEN 'No contesto' THEN 6
                    WHEN 'Por definir' THEN 7
                    ELSE 99
                END
            ")
            ->get();

        $byStatus = Guest::query()
            ->select('status')
            ->selectRaw('COUNT(*) as records')
            ->selectRaw('SUM(adults) as adults')
            ->selectRaw('SUM(adolescents) as adolescents')
            ->selectRaw('SUM(children) as children')
            ->selectRaw('SUM(adults + adolescents + children) as total_people')
            ->groupBy('status')
            ->orderByRaw("
                CASE status
                    WHEN 'Considerado' THEN 1
                    WHEN 'Invitacion Enviada' THEN 2
                    WHEN 'Pendiente' THEN 3
                    WHEN 'Confirmado' THEN 4
                    WHEN 'No asistirá' THEN 5
                    WHEN 'No contesto' THEN 6
                    WHEN 'Por definir' THEN 7
                    ELSE 99
                END
            ")
            ->get();

        $whatsappSummary = [
            '2 meses' => $this->followupCounts('whatsapp_2_months'),
            '1 mes' => $this->followupCounts('whatsapp_1_month'),
            '15 dias' => $this->followupCounts('whatsapp_15_days'),
        ];

        $realGuestNames = Guest::where('category', 'Real')->where('status', 'Confirmado')->pluck('name');
        $realCompanions = fn () => Companion::whereIn('invited_group', $realGuestNames);

        $companionsSummary = [
            'scope' => 'Categoría Real confirmada',
            'total' => (int) $realCompanions()->count(),
            'adults' => (int) $realCompanions()->where('type', 'Adulto')->count(),
            'adolescents' => (int) $realCompanions()->where('type', 'Adolescente')->count(),
            'children' => (int) $realCompanions()->where('type', 'Niño')->count(),
            'men' => (int) $realCompanions()->where('sex', 'Hombre')->count(),
            'women' => (int) $realCompanions()->where('sex', 'Mujer')->count(),
            'adult_men' => (int) $realCompanions()->where('type', 'Adulto')->where('sex', 'Hombre')->count(),
            'adult_women' => (int) $realCompanions()->where('type', 'Adulto')->where('sex', 'Mujer')->count(),
            'teen_men' => (int) $realCompanions()->where('type', 'Adolescente')->where('sex', 'Hombre')->count(),
            'teen_women' => (int) $realCompanions()->where('type', 'Adolescente')->where('sex', 'Mujer')->count(),
            'child_men' => (int) $realCompanions()->where('type', 'Niño')->where('sex', 'Hombre')->count(),
            'child_women' => (int) $realCompanions()->where('type', 'Niño')->where('sex', 'Mujer')->count(),
            'difference_vs_confirmed_people' => (int) $realCompanions()->count() - (int) $summary['real_confirmed_total_people'],
        ];

        return view('dashboard', [
            'summary' => $summary,
            'byGroup' => $byGroup,
            'byCategory' => $byCategory,
            'byStatus' => $byStatus,
            'realByStatus' => $realByStatus,
            'whatsappSummary' => $whatsappSummary,
            'companionsSummary' => $companionsSummary,
        ]);
    }

    private function followupCounts(string $column): array
    {
        return [
            'Pendiente' => (int) Guest::where($column, 'Pendiente')->count(),
            'Enviado' => (int) Guest::where($column, 'Enviado')->count(),
            'Respondio' => (int) Guest::where($column, 'Respondio')->count(),
            'No aplica' => (int) Guest::where($column, 'No aplica')->count(),
        ];
    }
}
