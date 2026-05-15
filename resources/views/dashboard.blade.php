@extends('layouts.app')

@section('title', 'Resumen general')
@section('heading', 'Resumen general')
@section('subheading', 'Vista ejecutiva basada en la hoja Resumen del Excel, con impacto actual por familias o grupos, categorías, estatus e invitados')

@section('content')
    <div class="grid cols-4" style="margin-bottom: 18px;">
        <div class="card metric">
            <div class="label">Total general</div>
            <div class="value">{{ number_format($summary['records']) }}</div>
        </div>
        <div class="card metric">
            <div class="label">Base real</div>
            <div class="value">{{ number_format($summary['real_records']) }}</div>
        </div>
        <div class="card metric">
            <div class="label">Total personas</div>
            <div class="value">{{ number_format($summary['total_people']) }}</div>
        </div>
        <div class="card metric">
            <div class="label">Total padrinos</div>
            <div class="value">{{ number_format($summary['sponsors']) }}</div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <div class="section-kicker">Mini reporte</div>
        <h3 class="section-title">Totales generales</h3>
        <div class="grid cols-4" style="margin-top: 16px;">
            <div class="muted-box"><strong>Adultos</strong><br>{{ number_format($summary['adults']) }}</div>
            <div class="muted-box"><strong>Adolescentes</strong><br>{{ number_format($summary['adolescents']) }}</div>
            <div class="muted-box"><strong>Niños</strong><br>{{ number_format($summary['children']) }}</div>
            <div class="muted-box"><strong>Registros confirmados</strong><br>{{ number_format($summary['confirmed_records']) }}</div>
        </div>
        <div class="grid cols-4" style="margin-top: 14px;">
            <div class="muted-box"><strong>Adultos confirmados</strong><br>{{ number_format($summary['confirmed_adults']) }}</div>
            <div class="muted-box"><strong>Adolescentes confirmados</strong><br>{{ number_format($summary['confirmed_adolescents']) }}</div>
            <div class="muted-box"><strong>Niños confirmados</strong><br>{{ number_format($summary['confirmed_children']) }}</div>
            <div class="muted-box"><strong>Total general confirmado</strong><br>{{ number_format($summary['confirmed_total_people']) }}</div>
        </div>

        <div class="muted-box" style="margin-top: 18px; border-style: solid; padding: 18px 20px; background: #f8efff;">
            <div class="section-kicker" style="margin-bottom: 10px;">Distinción importante</div>
            <div class="grid cols-2">
                <div>
                    <div class="small">Invitados registrados</div>
                    <div style="font-size: 38px; font-weight: 800; color: #7f46b0;">{{ number_format($summary['companions']) }}</div>
                </div>
                <div>
                    <div class="small">Invitados confirmados</div>
                    <div style="font-size: 38px; font-weight: 800; color: #4a2f60;">{{ number_format($summary['confirmed_total_people']) }}</div>
                </div>
            </div>
            <div style="margin-top: 12px; color: #5f4c70; line-height: 1.7;">
                Aquí se distingue que <strong>invitados registrados</strong> es el total de personas cargadas en el módulo de invitados,
                mientras que <strong>invitados confirmados</strong> es la suma total de personas de los registros con estatus
                <strong>Confirmado</strong>. La meta operativa es que ambos números queden iguales.
                @if ($companionsSummary['difference_vs_confirmed_people'] === 0)
                    Ahorita ya están alineados.
                @elseif ($companionsSummary['difference_vs_confirmed_people'] > 0)
                    Ahorita hay {{ $companionsSummary['difference_vs_confirmed_people'] }} invitados más que invitados confirmados.
                @else
                    Ahorita faltan {{ abs($companionsSummary['difference_vs_confirmed_people']) }} invitados para igualar a los invitados confirmados.
                @endif
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <div class="section-kicker">Totales por estatus</div>
        <h3 class="section-title">Estado actual de invitaciones</h3>
        <div class="table-wrap" style="margin-top: 16px;">
            <table>
                <thead>
                    <tr>
                        <th>Estatus</th>
                        <th>Registros</th>
                        <th>Adultos</th>
                        <th>Adolescentes</th>
                        <th>Niños</th>
                        <th>Total personas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($byStatus as $row)
                        <tr>
                            <td>{{ $row->status }}</td>
                            <td>{{ $row->records }}</td>
                            <td>{{ $row->adults }}</td>
                            <td>{{ $row->adolescents }}</td>
                            <td>{{ $row->children }}</td>
                            <td>{{ $row->total_people }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <div class="section-kicker">Resumen por grupo</div>
        <h3 class="section-title">Impacto actual por familias o grupos</h3>
        <div class="table-wrap" style="margin-top: 16px;">
            <table>
                <thead>
                    <tr>
                        <th>Grupo</th>
                        <th>Registros</th>
                        <th>Adultos</th>
                        <th>Adolescentes</th>
                        <th>Niños</th>
                        <th>Total personas</th>
                        <th>Padrinos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($byGroup as $row)
                        <tr>
                            <td>{{ $row->group_name }}</td>
                            <td>{{ $row->records }}</td>
                            <td>{{ $row->adults }}</td>
                            <td>{{ $row->adolescents }}</td>
                            <td>{{ $row->children }}</td>
                            <td>{{ $row->total_people }}</td>
                            <td>{{ $row->sponsors }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <div class="section-kicker">Totales por categoría</div>
        <h3 class="section-title">Reales vs probables</h3>
        <div class="table-wrap" style="margin-top: 16px;">
            <table>
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th>Registros</th>
                        <th>Adultos</th>
                        <th>Adolescentes</th>
                        <th>Niños</th>
                        <th>Total personas</th>
                        <th>Padrinos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($byCategory as $row)
                        <tr>
                            <td>{{ $row->category }}</td>
                            <td>{{ $row->records }}</td>
                            <td>{{ $row->adults }}</td>
                            <td>{{ $row->adolescents }}</td>
                            <td>{{ $row->children }}</td>
                            <td>{{ $row->total_people }}</td>
                            <td>{{ $row->sponsors }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <div class="section-kicker">Resumen de invitados</div>
        <h3 class="section-title">Composición de invitados</h3>
        <div class="grid cols-6" style="margin-top: 16px;">
            <div class="muted-box"><strong>Total invitados</strong><br>{{ $companionsSummary['total'] }}</div>
            <div class="muted-box"><strong>Adultos</strong><br>{{ $companionsSummary['adults'] }}</div>
            <div class="muted-box"><strong>Adolescentes</strong><br>{{ $companionsSummary['adolescents'] }}</div>
            <div class="muted-box"><strong>Niños</strong><br>{{ $companionsSummary['children'] }}</div>
            <div class="muted-box"><strong>Hombres</strong><br>{{ $companionsSummary['men'] }}</div>
            <div class="muted-box"><strong>Mujeres</strong><br>{{ $companionsSummary['women'] }}</div>
        </div>
        <div class="grid cols-3" style="margin-top: 14px;">
            <div class="muted-box"><strong>Adultos hombres</strong><br>{{ $companionsSummary['adult_men'] }}</div>
            <div class="muted-box"><strong>Adultos mujeres</strong><br>{{ $companionsSummary['adult_women'] }}</div>
            <div class="muted-box"><strong>Adolescentes hombres</strong><br>{{ $companionsSummary['teen_men'] }}</div>
            <div class="muted-box"><strong>Adolescentes mujeres</strong><br>{{ $companionsSummary['teen_women'] }}</div>
            <div class="muted-box"><strong>Niños hombres</strong><br>{{ $companionsSummary['child_men'] }}</div>
            <div class="muted-box"><strong>Niñas / mujeres</strong><br>{{ $companionsSummary['child_women'] }}</div>
        </div>
    </div>

    <div class="card">
        <div class="section-kicker">Seguimiento WhatsApp</div>
        <h3 class="section-title">Control de envíos y respuesta</h3>
        <div class="table-wrap" style="margin-top: 16px;">
            <table>
                <thead>
                    <tr>
                        <th>Etapa</th>
                        <th>Pendiente</th>
                        <th>Enviado</th>
                        <th>Respondio</th>
                        <th>No aplica</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($whatsappSummary as $stage => $counts)
                        <tr>
                            <td>{{ $stage }}</td>
                            <td>{{ $counts['Pendiente'] }}</td>
                            <td>{{ $counts['Enviado'] }}</td>
                            <td>{{ $counts['Respondio'] }}</td>
                            <td>{{ $counts['No aplica'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
