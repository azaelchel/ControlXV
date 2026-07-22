@extends('layouts.app')

@section('title', 'QR de accesos')
@section('heading', 'QR de accesos')
@section('subheading', 'Solo familias confirmadas. Carga la imagen QR que recibirá cada familia en su link personalizado.')

@section('content')
    @php $accessTemplate = App\Models\MessageTemplate::where('name', 'Envío de QR de acceso')->first(); @endphp
    <div class="grid cols-3" style="margin-bottom: 18px;">
        <div class="card metric"><div class="label">Confirmados</div><div class="value">{{ number_format($summary['confirmed']) }}</div></div>
        <div class="card metric"><div class="label">Con QR cargado</div><div class="value">{{ number_format($summary['with_qr']) }}</div></div>
        <div class="card metric"><div class="label">Pendientes de QR</div><div class="value">{{ number_format(max(0, $summary['confirmed'] - $summary['with_qr'])) }}</div></div>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <div class="inline" style="justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 12px;">
            <div>
                <div class="section-kicker">Accesos</div>
                <h3 class="section-title" style="font-size: 20px;">Carga de QR por familia</h3>
            </div>
            @if ($accessTemplate)
                <a class="btn secondary" href="{{ route('message-sends.create', ['template_id' => $accessTemplate->id, 'status' => 'Confirmado']) }}">Preparar envío de QR</a>
            @endif
        </div>
        <form method="get" action="{{ route('access-qrs.index') }}" class="filter-row" style="grid-template-columns: 1.4fr 1fr auto;">
            <div>
                <label>Buscar</label>
                <input type="text" name="q" value="{{ $search }}" placeholder="Nombre, prefijo, telefono o familia...">
            </div>
            <div>
                <label>Grupo</label>
                <select name="group">
                    <option value="">Todos</option>
                    @foreach ($groups as $option)
                        <option value="{{ $option }}" @selected($group === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="inline" style="gap: 8px; align-items: end;">
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn secondary" href="{{ route('access-qrs.index') }}">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Familia / grupo</th>
                        <th>Contacto</th>
                        <th>Grupo</th>
                        <th>QR actual</th>
                        <th style="width: 330px;">Cargar QR</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($guests as $guest)
                        <tr>
                            <td>
                                <strong>{{ $guest->name }}</strong>
                                <div class="small">{{ $guest->prefix ?: 'Sin prefijo' }}</div>
                            </td>
                            <td>
                                <div>{{ $guest->phone ?: 'Sin telefono' }}</div>
                                <div class="small">{{ $guest->total_people }} persona(s)</div>
                            </td>
                            <td><span class="pill status-default">{{ $guest->group_name }}</span></td>
                            <td>
                                @if ($guest->access_qr_data)
                                    <span class="pill status-confirmado">QR cargado</span>
                                    <form method="post" action="{{ route('access-qrs.destroy', $guest) }}" style="display:inline; margin-left: 6px;"
                                        data-confirm-title="¿Eliminar QR de {{ $guest->name }}?"
                                        data-confirm-text="El link público quedará sin imagen QR hasta que cargues una nueva."
                                        data-confirm-button="Sí, eliminar"
                                        data-confirm-color="#d8527f">
                                        @csrf
                                        @method('delete')
                                        <button class="btn danger small" type="submit">Quitar</button>
                                    </form>
                                @else
                                    <span class="pill status-pendiente">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                <form method="post" action="{{ route('access-qrs.store', $guest) }}" enctype="multipart/form-data" class="inline" style="gap: 8px; flex-wrap: nowrap; align-items: center;">
                                    @csrf
                                    <input type="file" name="qr" accept="image/png,image/jpeg,image/webp" required style="max-width: 190px;">
                                    <button class="btn small" type="submit">Subir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="small">No hay familias confirmadas con estos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 14px 18px;">{{ $guests->links() }}</div>
    </div>
@endsection
