<div>
    @include('partials.alerts')
    @include('livewire.periodos.profile.modals')

    <div class="fs-2 fw-semibold mb-4">
        <a href="{{route('periodos.')}}">Períodos</a> /
        {{$periodo->codigo_periodo}}
    </div>


    <div class="mt-4 d-flex flex-row align-items-center">
        <h3 class="me-3">Carreras</h3>
        <button data-bs-toggle="modal" data-bs-target="#createDataModal" class="btn btn-sm btn-info me-3"
            style="height: max-content">
            Añadir Carrera
        </button>
    </div>
    {{-- contenedor con cards que impriman las carreras por periodo --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($periodos_carreras as $periodoCarrera)
            <a href="{{ route('periodos.tribunales.index', ['carreraPeriodoId' => $periodoCarrera->id]) }}"
                class="card p-4" style="width: max-content">
                <h2 class="text-xl font-bold">{{ $periodoCarrera->carrera->nombre }}</h2>
                <p>Director: {{ $periodoCarrera->director->name }}</p>
                <p>Docente Apoyo: {{ $periodoCarrera->docenteApoyo->name }}</p>
            </a>
        @endforeach

        @if ($periodos_carreras->isEmpty())
            {{-- Si no hay carreras disponibles para el periodo --}}
            <div class="bg-white shadow-md rounded-lg p-4">
                <p class="text-xl font-bold">No hay carreras disponibles para este periodo</p>
            </div>
        @endif

    </div>

</div>
