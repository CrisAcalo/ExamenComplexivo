<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\PlantillaActaWord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PlantillasActaWord extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $nombre;
    public $descripcion;
    public $archivoWord;
    public $plantillaAEliminarId;

    public $keyWord = '';

    // Variables disponibles para mostrar al usuario
    public $variablesDisponibles = [
        '${tribunal_id}' => 'ID del tribunal',
        '${estudiante_nombre}' => 'Nombre del estudiante',
        '${estudiante_apellidos}' => 'Apellidos del estudiante',
        '${estudiante_cedula}' => 'Cédula del estudiante',
        '${carrera_nombre}' => 'Nombre de la carrera',
        '${carrera_modalidad}' => 'Modalidad de la carrera',
        '${periodo_codigo}' => 'Código del período',
        '${fecha_examen}' => 'Fecha del examen',
        '${presidente_nombre}' => 'Nombre del presidente',
        '${presidente_cedula}' => 'Cédula del presidente',
        '${integrante1_nombre}' => 'Nombre del integrante 1',
        '${integrante1_cedula}' => 'Cédula del integrante 1',
        '${integrante2_nombre}' => 'Nombre del integrante 2',
        '${integrante2_cedula}' => 'Cédula del integrante 2',
        '${director_nombre}' => 'Nombre del director',
        '${director_cedula}' => 'Cédula del director',
        '${nota_componente_teorico}' => 'Nota componente teórico (sobre 20)',
        '${nota_componente_practico}' => 'Nota componente práctico (sobre 20)',
        '${nota_final}' => 'Nota final total (sobre 20)',
        '${nota_final_letras}' => 'Nota final en letras',
        '${aprobado}' => 'SÍ o NO (texto)',
        '${aprobado_si}' => 'X si aprobó (para casilla Sí)',
        '${aprobado_no}' => 'X si no aprobó (para casilla No)',
        '${fecha_actual}' => 'Fecha actual',

        '${estudiante_id}' => 'ID del estudiante',
        '${estudiante_nombre_completo}' => 'Nombre completo del estudiante (apellidos + nombres)',
        '${carrera_nombre_procesado}' => 'Nombre de carrera sin las últimas dos palabras',
        '${fecha_examen_formato_completo}' => 'Fecha en formato dd/mm/yyyy',
        
        // Variables de calificación detallada
        '${nota_teorico_sobre_20}' => 'Calificación teórica sobre 20 puntos',
        '${ponderacion_teorico}' => 'Ponderación del componente teórico (%)',
        '${calificacion_teorico_ponderada}' => 'Calificación teórica ponderada',
        '${componente1_calificacion_ponderada}' => 'Calificación ponderada del primer componente',
        
        '${nota_practico_sobre_20}' => 'Calificación práctica sobre 20 puntos',
        '${ponderacion_practico}' => 'Ponderación del componente práctico (%)',
        '${calificacion_practico_ponderada}' => 'Calificación práctica ponderada',
        '${componente2_calificacion_ponderada}' => 'Calificación ponderada del segundo componente',
        
        // Variables para componentes de rúbrica individuales
        '${componente1_nombre}' => 'Nombre del primer componente de rúbrica',
        '${componente1_nota}' => 'Nota del primer componente',
        '${componente1_ponderacion}' => 'Ponderación del primer componente',
        
        '${componente2_nombre}' => 'Nombre del segundo componente de rúbrica',
        '${componente2_nota}' => 'Nota del segundo componente',
        '${componente2_ponderacion}' => 'Ponderación del segundo componente',
        
        // Variables para fechas
        '${fecha_formato_barra}' => 'Fecha en formato dd/mm/yyyy',
        '${fecha_formato_mes_dia_ano}' => 'Fecha en formato mm/dd/yyyy',
    ];

    protected $rules = [
        'nombre' => 'required|string|max:100',
        'descripcion' => 'nullable|string',
        'archivoWord' => 'required|file|mimes:docx,doc|max:10240',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre de la plantilla es obligatorio',
        'archivoWord.required' => 'Debes seleccionar un archivo Word',
        'archivoWord.mimes' => 'El archivo debe ser Word (.docx o .doc)',
        'archivoWord.max' => 'El archivo no debe pesar más de 10MB',
    ];

    public function mount()
    {
        $this->verificarAcceso();
    }

    protected function verificarAcceso()
    {
        $user = auth()->user();

        if (!$user || !$user->hasRole('Super Admin')) {
            session()->flash('error', 'No tienes permisos para acceder a esta sección.');
            abort(403);
        }
    }

    public function render()
    {
        $keyWord = '%' . $this->keyWord . '%';

        $plantillas = PlantillaActaWord::with(['usuarioCreador', 'usuarioActualizador'])
            ->latest()
            ->where(function($query) use ($keyWord) {
                $query->where('nombre', 'LIKE', $keyWord)
                    ->orWhere('descripcion', 'LIKE', $keyWord);
            })
            ->paginate(10);

        return view('livewire.plantillas-acta-word.view', [
            'plantillas' => $plantillas,
        ]);
    }

    public function testLivewire()
    {
        \Log::info('TEST LIVEWIRE FUNCIONANDO!');
        session()->flash('success', 'Livewire funciona correctamente!');
    }

    public function updatedArchivoWord()
    {
        \Log::info('ARCHIVO DETECTADO!');
        \Log::info('Nombre archivo: ' . ($this->archivoWord ? $this->archivoWord->getClientOriginalName() : 'NULL'));
        session()->flash('info', 'Archivo detectado: ' . ($this->archivoWord ? $this->archivoWord->getClientOriginalName() : 'NULL'));
    }

    public function subirPlantilla()
    {
        $this->validate([
            'nombre' => 'required|string|max:100',
            'archivoWord' => 'required|file|mimes:docx,doc|max:10240',
        ], [
            'nombre.required' => 'El nombre de la plantilla es obligatorio.',
            'archivoWord.required' => 'Debe seleccionar un archivo Word.',
            'archivoWord.mimes' => 'El archivo debe ser Word (.docx o .doc).',
            'archivoWord.max' => 'El archivo no debe pesar más de 10MB.',
        ]);

        try {
            // Guardar archivo en storage/plantillas-word
            $path = $this->archivoWord->store('plantillas-word', 'public');

            // Crear registro en BD
            PlantillaActaWord::create([
                'nombre' => $this->nombre,
                'archivo_path' => $path,
                'descripcion' => $this->descripcion,
                'activa' => false,
                'creado_por' => Auth::id(),
                'actualizado_por' => Auth::id(),
            ]);

            $this->resetInput();
            session()->flash('success', 'Plantilla subida exitosamente.');
            $this->dispatchBrowserEvent('closeModal');

        } catch (\Exception $e) {
            session()->flash('danger', 'Error al subir la plantilla: ' . $e->getMessage());
        }
    }

    public function activar($id)
    {
        $plantilla = PlantillaActaWord::findOrFail($id);
        $plantilla->activar();

        session()->flash('success', "Plantilla '{$plantilla->nombre}' activada exitosamente.");
    }

    public function desactivar($id)
    {
        $plantilla = PlantillaActaWord::findOrFail($id);
        $plantilla->update(['activa' => false]);

        session()->flash('info', "Plantilla '{$plantilla->nombre}' desactivada.");
    }

    public function confirmDelete($id)
    {
        $this->plantillaAEliminarId = $id;
        $this->dispatchBrowserEvent('openDeleteModal');
    }

    public function delete()
    {
        if (!$this->plantillaAEliminarId) {
            return;
        }

        $plantilla = PlantillaActaWord::findOrFail($this->plantillaAEliminarId);

        if ($plantilla->activa) {
            session()->flash('warning', 'No puedes eliminar una plantilla activa. Desactívala primero.');
            return;
        }

        // Eliminar archivo del storage
        if (Storage::disk('public')->exists($plantilla->archivo_path)) {
            Storage::disk('public')->delete($plantilla->archivo_path);
        }

        $nombrePlantilla = $plantilla->nombre;
        $plantilla->delete();

        session()->flash('success', "Plantilla '{$nombrePlantilla}' eliminada exitosamente.");
        $this->plantillaAEliminarId = null;
        $this->dispatchBrowserEvent('closeDeleteModal');
    }

    private function resetInput()
    {
        $this->nombre = null;
        $this->descripcion = null;
        $this->archivoWord = null;
        $this->resetValidation();
    }
}
