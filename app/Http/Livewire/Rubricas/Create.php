<?php

namespace App\Http\Livewire\Rubricas;

use Livewire\Component;
use App\Models\Rubrica;
use App\Models\ComponenteRubrica;
use App\Models\CriterioComponente;
use App\Models\CalificacionCriterio;
use Illuminate\Support\Facades\DB; // Importar DB para transacciones
use Illuminate\Support\Facades\Gate;
use App\Helpers\ContextualAuth;

class Create extends Component
{
    public $rubricaId; // Para recibir el ID de la rúbrica a editar
    public $rubricaExistente; // Para almacenar la instancia del modelo Rubrica en modo edición

    public $nombreRubrica;

    // Estos serán los "niveles de calificación" o columnas maestras
    public $nivelesCalificacion = [];
    /*
    Ejemplo de $nivelesCalificacion:
    [
        ['id_temporal' => 'nivel_1', 'nombre' => 'Muy Bueno', 'valor' => 4],
        ['id_temporal' => 'nivel_2', 'nombre' => 'Bueno', 'valor' => 3],
        // ...
    ]
    */

    public $componentes = [];
    /*
    Ejemplo de estructura para $componentes:
    $componentes = [
        [
            'id_temporal' => 'comp_1',
            'nombre' => 'Parte Escrita', // Nombre del Componente
            'ponderacion' => 60,
            'criterios' => [
                [
                    'id_temporal' => 'crit_1_1',
                    'nombre' => 'Pericia en la especialidad', // Nombre del Criterio
                    'descripciones_calificacion' => [ // Descripciones para este criterio, una por cada nivel de calificación maestro
                        'nivel_1' => 'Descripción para Pericia en Muy Bueno...', // La clave es el id_temporal del nivelCalificacion
                        'nivel_2' => 'Descripción para Pericia en Bueno...',
                    ]
                ],
                // ... otros criterios
            ]
        ],
        // ... otros componentes
    ];
    */
    public $modoEdicion = false;


    public function mount($rubricaId = null)
    {
        // Verificar permisos para gestionar rúbricas
        $this->verificarAccesoCreacionRubricas();

        $this->rubricaId = $rubricaId;

        if ($this->rubricaId) {
            $this->modoEdicion = true;
            $this->rubricaExistente = Rubrica::with([
                'componentesRubrica.criteriosComponente.calificacionesCriterio'
            ])->find($this->rubricaId);

            if ($this->rubricaExistente) {
                $this->cargarDatosParaEdicion();
            } else {
                // Rúbrica no encontrada, redirigir o mostrar error
                session()->flash('danger', 'Rúbrica para editar no encontrada.');
                return redirect()->route('rubricas.'); // O a donde consideres apropiado
            }
        } else {
            // Modo Creación: Inicializar como antes
            $this->addNivelCalificacion('Muy Bueno', 4);
            $this->addNivelCalificacion('Bueno', 3);
            $this->addNivelCalificacion('Regular', 2);
            $this->addNivelCalificacion('Insuficiente', 1);
            $this->addNivelCalificacion('No Presenta', 0);
            $this->addComponente();
        }
    }

    protected function cargarDatosParaEdicion()
    {
        $this->nombreRubrica = $this->rubricaExistente->nombre;

        // Cargar Niveles de Calificación
        // Necesitamos obtener los niveles únicos definidos para esta rúbrica.
        // La estructura actual guarda 'nombre' y 'valor' del nivel en cada 'calificaciones_criterio'.
        // Vamos a agruparlos.
        $nivelesTemp = [];
        $mapIdTemporalNiveles = []; // Para mapear el nombre_valor a un id_temporal único

        foreach ($this->rubricaExistente->componentesRubrica as $componenteDB) {
            foreach ($componenteDB->criteriosComponente as $criterioDB) {
                foreach ($criterioDB->calificacionesCriterio as $calificacionDB) {
                    $keyNivel = $calificacionDB->nombre . '_' . $calificacionDB->valor;
                    if (!isset($nivelesTemp[$keyNivel])) {
                        $idTemporal = 'nivel_' . uniqid();
                        $nivelesTemp[$keyNivel] = [
                            'id_temporal' => $idTemporal,
                            'nombre' => $calificacionDB->nombre,
                            'valor' => (float) $calificacionDB->valor, // Asegurar que sea float
                            // No necesitamos 'db_id' aquí ya que son conceptuales
                        ];
                        $mapIdTemporalNiveles[$keyNivel] = $idTemporal;
                    }
                }
            }
        }
        // Ordenar los niveles por valor descendente (opcional, pero consistente con el mount de creación)
        uasort($nivelesTemp, function ($a, $b) {
            return $b['valor'] <=> $a['valor'];
        });
        $this->nivelesCalificacion = array_values($nivelesTemp);


        // Cargar Componentes, Criterios y sus Descripciones de Calificación
        $this->componentes = [];
        foreach ($this->rubricaExistente->componentesRubrica as $componenteDB) {
            $criteriosArray = [];
            foreach ($componenteDB->criteriosComponente as $criterioDB) {
                $descripcionesCalifArray = [];
                foreach ($criterioDB->calificacionesCriterio as $calificacionDB) {
                    $keyNivel = $calificacionDB->nombre . '_' . $calificacionDB->valor;
                    if (isset($mapIdTemporalNiveles[$keyNivel])) {
                        // Usamos el id_temporal que generamos para los niveles únicos
                        $descripcionesCalifArray[$mapIdTemporalNiveles[$keyNivel]] = $calificacionDB->descripcion;
                    }
                }
                $criteriosArray[] = [
                    'id_temporal' => 'crit_' . uniqid(), // Nuevo id_temporal para la UI
                    'db_id' => $criterioDB->id, // Guardar el ID de la BD para referencia si es necesario
                    'nombre' => $criterioDB->nombre,
                    'descripciones_calificacion' => $descripcionesCalifArray,
                ];
            }

            $this->componentes[] = [
                'id_temporal' => 'comp_' . uniqid(), // Nuevo id_temporal para la UI
                'db_id' => $componenteDB->id, // Guardar el ID de la BD
                'nombre' => $componenteDB->nombre,
                'ponderacion' => (float) $componenteDB->ponderacion, // Asegurar que sea float
                'criterios' => $criteriosArray,
            ];
        }
        // Si no hay niveles (ej. rúbrica antigua o corrupta), inicializar por defecto
        if (empty($this->nivelesCalificacion) && $this->rubricaExistente) {
            $this->addNivelCalificacion('Muy Bueno', 4);
            $this->addNivelCalificacion('Bueno', 3);
            // ... y así sucesivamente, luego recargar descripciones para los criterios
            // Esto es un fallback, idealmente los datos deberían estar bien.
        }
    }

    public function addNivelCalificacion($nombre = '', $valor = '')
    {
        $nuevoIdTemporal = 'nivel_' . uniqid();
        $this->nivelesCalificacion[] = [
            'id_temporal' => $nuevoIdTemporal,
            'nombre' => $nombre,
            'valor' => $valor
        ];

        // Cuando se añade un nuevo nivel, hay que actualizar todos los criterios existentes
        // para que tengan un campo para la descripción de este nuevo nivel.
        foreach ($this->componentes as $idxComp => &$componente) {
            foreach ($componente['criterios'] as $idxCrit => &$criterio) {
                if (!isset($criterio['descripciones_calificacion'][$nuevoIdTemporal])) {
                    $criterio['descripciones_calificacion'][$nuevoIdTemporal] = '';
                }
            }
        }
    }

    public function removeNivelCalificacion($indexNivel)
    {
        if (isset($this->nivelesCalificacion[$indexNivel])) {
            $idTemporalNivelRemovido = $this->nivelesCalificacion[$indexNivel]['id_temporal'];
            unset($this->nivelesCalificacion[$indexNivel]);
            $this->nivelesCalificacion = array_values($this->nivelesCalificacion);

            // Remover las descripciones asociadas a este nivel de todos los criterios
            foreach ($this->componentes as &$componente) {
                foreach ($componente['criterios'] as &$criterio) {
                    unset($criterio['descripciones_calificacion'][$idTemporalNivelRemovido]);
                }
            }
        }
    }

    // --- Gestión de Componentes ---
    public function addComponente()
    {
        $this->componentes[] = [
            'id_temporal' => 'comp_' . uniqid(),
            'nombre' => '',
            'ponderacion' => 0,
            'criterios' => [$this->nuevoCriterio()] // Iniciar con un criterio
        ];
    }

    public function removeComponente($indexComponente)
    {
        unset($this->componentes[$indexComponente]);
        $this->componentes = array_values($this->componentes);
    }

    // --- Gestión de Criterios ---
    private function nuevoCriterio()
    {
        $descripciones = [];
        foreach ($this->nivelesCalificacion as $nivel) {
            $descripciones[$nivel['id_temporal']] = ''; // Inicializar descripción vacía para cada nivel
        }
        return [
            'id_temporal' => 'crit_' . uniqid(),
            'nombre' => '', // Nombre del criterio
            'descripciones_calificacion' => $descripciones
        ];
    }

    public function addCriterio($indexComponente)
    {
        $this->componentes[$indexComponente]['criterios'][] = $this->nuevoCriterio();
    }

    public function removeCriterio($indexComponente, $indexCriterio)
    {
        unset($this->componentes[$indexComponente]['criterios'][$indexCriterio]);
        $this->componentes[$indexComponente]['criterios'] = array_values($this->componentes[$indexComponente]['criterios']);
    }

    // --- Validación y Guardado ---
    protected function rules()
    {
        $rules = [
            'nombreRubrica' => 'required|string|min:3',

            'nivelesCalificacion' => 'required|array|min:1',
            'nivelesCalificacion.*.nombre' => 'required|string|min:1',
            'nivelesCalificacion.*.valor' => 'required|numeric',

            'componentes' => 'required|array|min:1',
            'componentes.*.nombre' => 'required|string|min:3',
            'componentes.*.ponderacion' => 'required|numeric|min:0|max:100', // Suma debe ser 100
            'componentes.*.criterios' => 'required|array|min:1',
            'componentes.*.criterios.*.nombre' => 'required|string|min:3',
            // Para las descripciones, la validación se vuelve más compleja
            // porque las claves son dinámicas (id_temporal del nivel)
            // 'componentes.*.criterios.*.descripciones_calificacion' => 'required|array',
            // 'componentes.*.criterios.*.descripciones_calificacion.*' => 'required|string|min:3', // Valida cada descripción
        ];
        // Validación para descripciones_calificacion
        foreach ($this->componentes as $compIndex => $componente) {
            foreach ($componente['criterios'] as $critIndex => $criterio) {
                foreach ($this->nivelesCalificacion as $nivelIndex => $nivel) {
                    $rules["componentes.{$compIndex}.criterios.{$critIndex}.descripciones_calificacion.{$nivel['id_temporal']}"] = 'required|string|min:3';
                }
            }
        }

        return $rules;
    }

    // Personalizar mensajes de error para las descripciones dinámicas
    protected function validationAttributes()
    {
        $attributes = [];
        foreach ($this->componentes as $compIndex => $componente) {
            foreach ($componente['criterios'] as $critIndex => $criterio) {
                foreach ($this->nivelesCalificacion as $nivelIndex => $nivel) {
                    $attributes["componentes.{$compIndex}.criterios.{$critIndex}.descripciones_calificacion.{$nivel['id_temporal']}"] = "descripción para el criterio '" . substr($criterio['nombre'] ?? 'Criterio ' . ($critIndex + 1), 0, 20) . "...' en el nivel '" . $nivel['nombre'] . "'";
                }
            }
        }
        return $attributes;
    }


    public function updated($propertyName)
    {
        // Puede ser intensivo validar en cada update con esta estructura
        // $this->validateOnly($propertyName);
    }

    // public function saveRubrica()
    // {
    //     $this->validate();

    //     $totalPonderacion = 0;
    //     foreach ($this->componentes as $componente) {
    //         $totalPonderacion += floatval($componente['ponderacion']);
    //     }

    //     if (round($totalPonderacion, 2) != 100.00) {
    //         $this->addError('ponderacion_total', 'La suma de las ponderaciones de los componentes debe ser 100. Actual: ' . $totalPonderacion);
    //         return;
    //     }

    //     DB::transaction(function () {
    //         $rubrica = Rubrica::create(['nombre' => $this->nombreRubrica]);

    //         // Mapear id_temporal de niveles a los nombres y valores para el guardado
    //         $mapNivelesGuardados = [];
    //         foreach ($this->nivelesCalificacion as $nivelData) {
    //             $mapNivelesGuardados[$nivelData['id_temporal']] = [
    //                 'nombre' => $nivelData['nombre'],
    //                 'valor' => $nivelData['valor']
    //             ];
    //         }

    //         foreach ($this->componentes as $compData) {
    //             $componenteRubrica = $rubrica->componentesRubrica()->create([
    //                 'nombre' => $compData['nombre'],
    //                 'ponderacion' => $compData['ponderacion']
    //             ]);

    //             foreach ($compData['criterios'] as $critData) {
    //                 $criterioComponente = $componenteRubrica->criteriosComponente()->create([
    //                     'nombre' => $critData['nombre']
    //                 ]);

    //                 // Guardar las calificaciones_criterio
    //                 foreach ($critData['descripciones_calificacion'] as $idTemporalNivel => $descripcionEspecifica) {
    //                     if (isset($mapNivelesGuardados[$idTemporalNivel])) {
    //                         $nivelInfo = $mapNivelesGuardados[$idTemporalNivel];
    //                         $criterioComponente->calificacionesCriterio()->create([
    //                             'nombre' => $nivelInfo['nombre'], // Nombre del nivel (Muy Bueno)
    //                             'valor' => $nivelInfo['valor'],   // Valor del nivel (4)
    //                             'descripcion' => $descripcionEspecifica // Descripción específica de este criterio para este nivel
    //                         ]);
    //                     }
    //                 }
    //             }
    //         }
    //     });

    //     session()->flash('success', 'Rúbrica creada exitosamente.');
    //     return redirect()->route('rubricas.');
    // }

    public function saveRubrica() // Renombrar a algo como handleSubmit o processForm
    {
        $this->validate();

        $totalPonderacion = 0;
        foreach ($this->componentes as $componente) {
            $totalPonderacion += floatval($componente['ponderacion']);
        }

        if (round($totalPonderacion, 2) != 100.00) {
            $this->addError('ponderacion_total', 'La suma de las ponderaciones de los componentes debe ser 100. Actual: ' . $totalPonderacion);
            return;
        }

        DB::transaction(function () {
            if ($this->modoEdicion && $this->rubricaExistente) {
                // --- LÓGICA DE ACTUALIZACIÓN ---
                $this->rubricaExistente->update(['nombre' => $this->nombreRubrica]);
                $rubrica = $this->rubricaExistente;

                // Eliminar componentes, criterios y calificaciones antiguas asociadas a esta rúbrica
                // Esto es más simple que intentar hacer un diff y actualizar/crear/eliminar individualmente
                foreach ($rubrica->componentesRubrica as $componenteDB) {
                    foreach ($componenteDB->criteriosComponente as $criterioDB) {
                        $criterioDB->calificacionesCriterio()->delete(); // Elimina calificaciones
                    }
                    $componenteDB->criteriosComponente()->delete(); // Elimina criterios
                }
                $rubrica->componentesRubrica()->delete(); // Elimina componentes

            } else {
                // --- LÓGICA DE CREACIÓN ---
                $rubrica = Rubrica::create(['nombre' => $this->nombreRubrica]);
            }

            // --- RECREAR/CREAR estructura (común para creación y actualización después de limpiar) ---
            $mapNivelesGuardados = [];
            foreach ($this->nivelesCalificacion as $nivelData) {
                $mapNivelesGuardados[$nivelData['id_temporal']] = [
                    'nombre' => $nivelData['nombre'],
                    'valor' => $nivelData['valor']
                ];
            }

            foreach ($this->componentes as $compData) {
                $componenteRubrica = $rubrica->componentesRubrica()->create([
                    'nombre' => $compData['nombre'],
                    'ponderacion' => $compData['ponderacion']
                ]);

                foreach ($compData['criterios'] as $critData) {
                    $criterioComponente = $componenteRubrica->criteriosComponente()->create([
                        'nombre' => $critData['nombre']
                    ]);

                    foreach ($critData['descripciones_calificacion'] as $idTemporalNivel => $descripcionEspecifica) {
                        if (isset($mapNivelesGuardados[$idTemporalNivel])) {
                            $nivelInfo = $mapNivelesGuardados[$idTemporalNivel];
                            $criterioComponente->calificacionesCriterio()->create([
                                'nombre' => $nivelInfo['nombre'],
                                'valor' => $nivelInfo['valor'],
                                'descripcion' => $descripcionEspecifica
                            ]);
                        }
                    }
                }
            }
        });

        session()->flash('success', $this->modoEdicion ? 'Rúbrica actualizada exitosamente.' : 'Rúbrica creada exitosamente.');
        return redirect()->route('rubricas.');
    }

    /**
     * Verificar acceso para crear/editar rúbricas usando ContextualAuth
     */
    private function verificarAccesoCreacionRubricas()
    {
        $user = auth()->user();

        // Verificar si tiene permisos globales para gestionar rúbricas
        if (Gate::allows('gestionar rubricas')) {
            return true;
        }

        // Verificar si es Director o Docente de Apoyo con permisos contextuales
        $userContext = ContextualAuth::getUserContextInfo($user);
        if (($userContext['carreras_director']->isNotEmpty() || $userContext['carreras_apoyo']->isNotEmpty()) &&
            Gate::allows('ver rubricas')) {
            return true;
        }

        abort(403, 'No tienes permisos para crear o editar rúbricas.');
    }

    public function render()
    {
        return view('livewire.rubricas.create.create');
    }
}
