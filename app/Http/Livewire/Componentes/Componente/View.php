<?php

namespace App\Http\Livewire\Componentes\Componente;

use App\Models\CriterioEvaluacion;
use App\Models\CriterioCalificacion;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class View extends Component
{
    public $componenteId;
    public $nombreCriterio;
    public $criterios;
    public $calificaciones = [];
    public $nuevasCalificaciones = [];
    public $columnas = 3; // Número inicial de columnas

    protected $rules = [
        'nombreCriterio' => 'required',
        'calificaciones.*.nombre' => 'required',
        'calificaciones.*.valor' => 'required|numeric',
        'calificaciones.*.descripcion' => 'required'
    ];

    public function mount()
    {
        $this->cargarDatos();
    }

    public function cargarDatos()
{
    $this->criterios = CriterioEvaluacion::with('criteriosCalificaciones')
        ->where('componente_id', $this->componenteId)
        ->get();

    $this->calificaciones = [];

    foreach ($this->criterios as $criterio) {
        $this->calificaciones[$criterio->id] = $criterio->criteriosCalificaciones
            ->map(fn($calif) => [
                'nombre' => $calif->nombre,
                'valor' => $calif->valor,
                'descripcion' => $calif->descripcion
            ])
            ->toArray();

        // Rellenar con celdas vacías si es necesario
        while (count($this->calificaciones[$criterio->id]) < $this->columnas) {
            $this->calificaciones[$criterio->id][] = [
                'nombre' => '',
                'valor' => '',
                'descripcion' => ''
            ];
        }
    }
}

    public function storeCriterio()
    {
        $this->validateOnly('nombreCriterio');

        $criterio = CriterioEvaluacion::create([
            'componente_id' => $this->componenteId,
            'criterio' => $this->nombreCriterio,
        ]);

        // Inicializar array para el nuevo criterio
        $this->calificaciones[$criterio->id] = [];

        $this->reset('nombreCriterio');
        $this->cargarDatos();
        session()->flash('success', 'Criterio creado exitosamente.');
    }

    public function agregarColumna()
    {
        $this->columnas++;
    }

    public function eliminarColumna()
    {
        if ($this->columnas > 1) {
            $this->columnas--;
        }
    }

    public function guardarCalificaciones($criterioId)
    {
        // Filtrar solo calificaciones con datos
        $calificacionesValidas = collect($this->calificaciones[$criterioId] ?? [])
            ->filter(function ($calif) {
                return !empty($calif['nombre']) ||
                       !empty($calif['valor']) ||
                       !empty($calif['descripcion']);
            })
            ->map(function ($calif) {
                // Asegurar que todos los campos tengan valor
                return [
                    'nombre' => $calif['nombre'] ?? '',
                    'valor' => $calif['valor'] ?? '',
                    'descripcion' => $calif['descripcion'] ?? ''
                ];
            });

        // Validacion personalizada
        foreach ($calificacionesValidas as $index => $calif) {
            if (empty($calif['nombre'])) {
                throw ValidationException::withMessages([
                    "calificaciones.{$criterioId}.{$index}.nombre" => 'El nombre es requerido'
                ]);
            }

            if (empty($calif['valor'])) {
                throw ValidationException::withMessages([
                    "calificaciones.{$criterioId}.{$index}.valor" => 'El valor es requerido'
                ]);
            }
        }

        // Eliminar existentes y guardar nuevas
        CriterioCalificacion::where('criterio_id', $criterioId)->delete();

        foreach ($calificacionesValidas as $calif) {
            CriterioCalificacion::create([
                'criterio_id' => $criterioId,
                'nombre' => $calif['nombre'],
                'valor' => $calif['valor'],
                'descripcion' => $calif['descripcion']
            ]);
        }

        session()->flash('success', 'Calificaciones guardadas!');
        $this->cargarDatos();
    }

    public function render()
    {
        return view('livewire.componentes.componente.view');
    }
}
