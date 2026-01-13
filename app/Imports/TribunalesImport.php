<?php

namespace App\Imports;

use App\Models\Estudiante;
use App\Models\User;
use App\Models\Tribunale;
use App\Models\MiembrosTribunal;
use App\Models\TribunalLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TribunalesImport implements WithMultipleSheets
{
    protected $carrera_periodo_id;
    protected $fecha;
    protected $primeraHoja;

    /**
     * Constructor para recibir el carrera_periodo_id y la fecha
     */
    public function __construct($carrera_periodo_id, $fecha)
    {
        $this->carrera_periodo_id = $carrera_periodo_id;
        $this->fecha = $fecha;
    }

    /**
     * Especifica qué hojas importar (solo la primera)
     */
    public function sheets(): array
    {
        // Crear instancia de la primera hoja
        $this->primeraHoja = new TribunalesFirstSheetImport($this->carrera_periodo_id, $this->fecha);

        return [
            0 => $this->primeraHoja, // Solo procesar la primera hoja (índice 0)
        ];
    }

    /**
     * Obtener errores (delegar a la primera hoja)
     */
    public function getErrores(): array
    {
        return $this->primeraHoja ? $this->primeraHoja->getErrores() : [];
    }

    /**
     * Obtener cantidad de tribunales exitosos (delegar a la primera hoja)
     */
    public function getExitosos(): int
    {
        return $this->primeraHoja ? $this->primeraHoja->getExitosos() : 0;
    }
}

/**
 * Clase interna para procesar solo la primera hoja del Excel
 */
class TribunalesFirstSheetImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    protected $carrera_periodo_id;
    protected $fecha;
    protected $errores = [];
    protected $exitosos = 0;

    public function __construct($carrera_periodo_id, $fecha)
    {
        $this->carrera_periodo_id = $carrera_periodo_id;
        $this->fecha = $fecha;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        // Agrupar filas por tribunal
        $tribunalesAgrupados = $this->agruparPorTribunal($rows);

        // Procesar cada tribunal (agrupados por estudiante)
        foreach ($tribunalesAgrupados as $nombreEstudiante => $filasTribunal) {
            try {
                $this->procesarTribunal($nombreEstudiante, $filasTribunal);
                $this->exitosos++;
            } catch (\Exception $e) {
                // Obtener el nombre del tribunal desde la primera fila para el mensaje de error
                $nombreTribunal = $filasTribunal[0]['tribunal'] ?? 'Desconocido';
                $this->errores[] = [
                    'tribunal' => "{$nombreTribunal} - {$nombreEstudiante}",
                    'mensaje' => $e->getMessage()
                ];
            }
        }
    }

    /**
     * Agrupa las filas del Excel por estudiante (cada estudiante = 1 tribunal con 3 miembros)
     * Maneja celdas combinadas propagando valores de filas anteriores
     */
    protected function agruparPorTribunal(Collection $rows): array
    {
        $tribunales = [];

        // Variables para propagar valores de celdas combinadas
        $ultimoTribunal = null;
        $ultimoCaso = null;
        $ultimoEstudiante = null;
        $ultimoHorario = null;
        $ultimoLaboratorio = null;

        foreach ($rows as $index => $row) {
            // Validar que la fila tenga datos mínimos (al menos designación y docentes)
            if (empty($row['designacion']) && empty($row['docentes'])) {
                continue; // Saltar fila completamente vacía
            }

            // Propagar valores de celdas combinadas
            // Si la celda está vacía, usar el último valor conocido
            $tribunal = trim($row['tribunal'] ?? '');
            if (!empty($tribunal)) {
                $ultimoTribunal = $tribunal;
            } else {
                $tribunal = $ultimoTribunal;
            }

            $caso = trim($row['caso'] ?? '');
            if (!empty($caso)) {
                $ultimoCaso = $caso;
            } else {
                $caso = $ultimoCaso;
            }

            $estudiante = trim($row['estudiante'] ?? '');
            if (!empty($estudiante)) {
                $ultimoEstudiante = $estudiante;
            } else {
                $estudiante = $ultimoEstudiante;
            }

            $horario = trim($row['horario'] ?? '');
            if (!empty($horario)) {
                $ultimoHorario = $horario;
            } else {
                $horario = $ultimoHorario;
            }

            $laboratorio = trim($row['laboratorio'] ?? '');
            if (!empty($laboratorio)) {
                $ultimoLaboratorio = $laboratorio;
            } else {
                $laboratorio = $ultimoLaboratorio;
            }

            // Validar que tengamos al menos tribunal y estudiante
            if (empty($tribunal)) {
                $this->errores[] = [
                    'tribunal' => "Fila " . ($index + 7), // +7 porque headingRow=6 y el índice empieza en 0
                    'mensaje' => "La columna TRIBUNAL está vacía y no se pudo propagar de filas anteriores"
                ];
                continue;
            }

            if (empty($estudiante)) {
                $this->errores[] = [
                    'tribunal' => $tribunal,
                    'mensaje' => "La columna ESTUDIANTE está vacía en la fila " . ($index + 7)
                ];
                continue;
            }

            // Crear fila completa con valores propagados
            $filaCompleta = [
                'tribunal' => $tribunal,
                'caso' => $caso,
                'estudiante' => $estudiante,
                'designacion' => trim($row['designacion'] ?? ''),
                'docentes' => trim($row['docentes'] ?? ''),
                'horario' => $horario,
                'laboratorio' => $laboratorio,
                'firma' => trim($row['firma'] ?? ''),
            ];

            // CAMBIO CLAVE: Agrupar por ESTUDIANTE, no por TRIBUNAL
            // Cada estudiante = 1 tribunal con 3 miembros
            if (!isset($tribunales[$estudiante])) {
                $tribunales[$estudiante] = [];
            }

            $tribunales[$estudiante][] = $filaCompleta;
        }

        return $tribunales;
    }

    /**
     * Procesa un tribunal individual (3 filas)
     */
    protected function procesarTribunal(string $nombreEstudiante, array $filas)
    {
        // Validar que haya exactamente 3 filas (Presidente + 2 Integrantes)
        if (count($filas) !== 3) {
            throw new \Exception("El tribunal para '{$nombreEstudiante}' debe tener exactamente 3 miembros (Presidente, Integrante 1, Integrante 2). Encontrados: " . count($filas));
        }

        // Extraer datos de la primera fila (datos comunes)
        $primeraFila = $filas[0];

        // Obtener el nombre del tribunal desde la primera fila (ya viene propagado)
        $nombreTribunal = $primeraFila['tribunal'] ?? null;

        // Validar y obtener estudiante
        $estudiante = $this->buscarEstudiante($primeraFila['estudiante'] ?? '');
        if (!$estudiante) {
            throw new \Exception("Estudiante '{$primeraFila['estudiante']}' no encontrado en el periodo académico");
        }

        // Validar que el estudiante no tenga tribunal ya asignado
        $tribunalExistente = Tribunale::where('carrera_periodo_id', $this->carrera_periodo_id)
            ->where('estudiante_id', $estudiante->id)
            ->where('es_plantilla', false)
            ->first();

        if ($tribunalExistente) {
            throw new \Exception("El estudiante '{$estudiante->getNombreCompleto()}' ya tiene un tribunal asignado");
        }

        // Procesar horario
        $horarioData = $this->procesarHorario($primeraFila['horario'] ?? '');

        // Obtener laboratorio
        $laboratorio = trim($primeraFila['laboratorio'] ?? '');

        // Validar solapamiento de horarios (solo en el mismo laboratorio)
        $this->validarHorariosSolapados(
            $this->fecha,
            $horarioData['hora_inicio'],
            $horarioData['hora_fin'],
            $laboratorio
        );

        // Procesar miembros del tribunal
        $miembros = $this->procesarMiembros($filas);

        // Obtener el caso desde la primera fila (ya viene propagado)
        $caso = $primeraFila['caso'] ?? null;
        $caso = !empty(trim($caso)) ? trim($caso) : null;

        // Crear tribunal en una transacción
        DB::transaction(function () use ($estudiante, $horarioData, $miembros, $nombreTribunal, $laboratorio, $caso) {
            // Crear tribunal
            $tribunal = Tribunale::create([
                'carrera_periodo_id' => $this->carrera_periodo_id,
                'estudiante_id' => $estudiante->id,
                'fecha' => $this->fecha,
                'hora_inicio' => $horarioData['hora_inicio'],
                'hora_fin' => $horarioData['hora_fin'],
                'laboratorio' => !empty($laboratorio) ? $laboratorio : null,
                'nombre_tribunal' => !empty($nombreTribunal) ? $nombreTribunal : null,
                'caso' => $caso,
                'estado' => 'ABIERTO',
                'es_plantilla' => false,
                'descripcion_plantilla' => null,
            ]);

            // Crear miembros
            foreach ($miembros as $status => $userId) {
                MiembrosTribunal::create([
                    'tribunal_id' => $tribunal->id,
                    'user_id' => $userId,
                    'status' => $status,
                ]);
            }

            // Registrar log
            TribunalLog::create([
                'tribunal_id' => $tribunal->id,
                'user_id' => auth()->id(),
                'accion' => 'TRIBUNAL_CREADO',
                'descripcion' => $nombreTribunal
                    ? "Tribunal '{$nombreTribunal}' importado desde Excel para {$estudiante->getNombreCompleto()}"
                    : "Tribunal importado desde Excel para {$estudiante->getNombreCompleto()}",
            ]);
        });
    }

    /**
     * Normaliza un texto removiendo tildes y convirtiendo Ñ→N
     * Para comparación de nombres se normaliza TODO (tildes y eñes)
     */
    protected function normalizarTexto(string $texto): string
    {
        $texto = trim($texto);

        // Remover tildes ANTES de convertir a mayúsculas (para manejar minúsculas también)
        $tildes = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'ñ', 'Ñ'];
        $sinTildes = ['a', 'e', 'i', 'o', 'u', 'u', 'A', 'E', 'I', 'O', 'U', 'U', 'n', 'N'];
        $texto = str_replace($tildes, $sinTildes, $texto);

        // Convertir a mayúsculas DESPUÉS de remover tildes
        $texto = strtoupper($texto);

        return $texto;
    }

    /**
     * Busca un estudiante por nombre (formato: APELLIDO1 APELLIDO2 NOMBRE1)
     */
    protected function buscarEstudiante(string $nombreCompleto): ?Estudiante
    {
        $nombreCompleto = trim($nombreCompleto);

        if (empty($nombreCompleto)) {
            return null;
        }

        // El Excel trae: "APELLIDO1 APELLIDO2 NOMBRE1"
        // En BD tenemos: nombres (dos nombres) y apellidos (dos apellidos)

        // Estrategia: buscar por coincidencia parcial en la concatenación de apellidos y nombres
        // Normalizar el nombre del Excel (sin tildes)
        $nombreExcelNormalizado = $this->normalizarTexto($nombreCompleto);

        $estudiante = Estudiante::where('carrera_periodo_id', $this->carrera_periodo_id)
            ->get()
            ->first(function ($est) use ($nombreExcelNormalizado) {
                // Crear variaciones del nombre completo del estudiante (sin tildes)
                $nombreBD = $this->normalizarTexto($est->apellidos . ' ' . $est->nombres);

                // Comparación exacta
                if ($nombreBD === $nombreExcelNormalizado) {
                    return true;
                }

                // Comparación por palabras (todas las palabras del Excel deben estar en BD)
                $palabrasExcel = array_filter(explode(' ', $nombreExcelNormalizado));
                $palabrasBD = array_filter(explode(' ', $nombreBD));

                $todasLasPalabrasCoinciden = true;
                foreach ($palabrasExcel as $palabra) {
                    if (!in_array($palabra, $palabrasBD)) {
                        $todasLasPalabrasCoinciden = false;
                        break;
                    }
                }

                return $todasLasPalabrasCoinciden;
            });

        return $estudiante;
    }

    /**
     * Busca un docente por nombre (formato: Ing. NOMBRE APELLIDO)
     * Búsqueda flexible: busca por palabras clave en cualquier orden
     */
    protected function buscarDocente(string $nombreCompleto): ?User
    {
        $nombreCompleto = trim($nombreCompleto);

        if (empty($nombreCompleto)) {
            return null;
        }

        // Remover prefijos comunes
        $nombreCompleto = preg_replace('/^(Ing\.|Dr\.|Msc\.|Phd\.|Mgtr\.)\s*/i', '', $nombreCompleto);
        $nombreCompleto = trim($nombreCompleto);

        // El Excel trae: "NOMBRE APELLIDO" (ej: "Tatiana Gualotuña", "Mauricio Campaña")
        // En BD puede tener:
        // - name: "TATIANA MARISOL" o "Germán" (puede estar vacío en algunos casos)
        // - lastname: "GUALOTUÑA ALVAREZ" o "Rodríguez" (puede estar vacío)

        // Normalizar el nombre del Excel (sin tildes)
        $nombreExcelNormalizado = $this->normalizarTexto($nombreCompleto);
        $palabrasExcel = array_filter(explode(' ', $nombreExcelNormalizado));

        // Si no hay palabras para buscar, retornar null
        if (empty($palabrasExcel)) {
            return null;
        }

        // Guardar referencia a $this para usar en el closure
        $self = $this;

        // Palabras a ignorar en la búsqueda (preposiciones, artículos, etc.)
        $palabrasIgnorar = ['DE', 'LA', 'LOS', 'LAS', 'DEL', 'Y'];

        // Filtrar palabras a ignorar del Excel
        $palabrasExcelFiltradas = array_filter($palabrasExcel, function($palabra) use ($palabrasIgnorar) {
            return !in_array($palabra, $palabrasIgnorar);
        });

        // Buscar todos los candidatos y calcular su puntuación
        $candidatos = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Docente', 'Super Admin']);
        })
        ->get()
        ->map(function ($user) use ($palabrasExcelFiltradas, $self, $palabrasIgnorar) {
            // Crear el nombre completo del usuario en BD (sin tildes)
            $nombreBD = $self->normalizarTexto(trim($user->name . ' ' . $user->lastname));

            // Si el nombre en BD está vacío, saltar
            if (empty($nombreBD)) {
                return null;
            }

            $palabrasBD = array_filter(explode(' ', $nombreBD));

            // Filtrar palabras a ignorar de la BD también
            $palabrasBDFiltradas = array_filter($palabrasBD, function($palabra) use ($palabrasIgnorar) {
                return !in_array($palabra, $palabrasIgnorar);
            });

            // Contar coincidencias EXACTAS solamente
            $coincidenciasExactas = 0;
            $totalPalabrasExcel = count($palabrasExcelFiltradas);

            foreach ($palabrasExcelFiltradas as $palabraExcel) {
                foreach ($palabrasBDFiltradas as $palabraBD) {
                    // Solo coincidencia EXACTA (no parciales para evitar falsos positivos)
                    if ($palabraBD === $palabraExcel) {
                        $coincidenciasExactas++;
                        break;
                    }
                }
            }

            $porcentaje = $totalPalabrasExcel > 0 ? ($coincidenciasExactas / $totalPalabrasExcel) : 0;

            return [
                'user' => $user,
                'nombreBD' => $nombreBD,
                'palabrasBD' => array_values($palabrasBDFiltradas),
                'coincidencias' => $coincidenciasExactas,
                'total' => $totalPalabrasExcel,
                'porcentaje' => $porcentaje,
            ];
        })
        ->filter() // Eliminar nulls
        ->filter(function ($candidato) {
            // Solo considerar candidatos con coincidencia 100% (todas las palabras del Excel deben estar en BD)
            return $candidato['porcentaje'] >= 1.0;
        })
        ->sortByDesc(function ($candidato) {
            // Ordenar por número de coincidencias (más coincidencias = mejor match)
            // En caso de empate, preferir nombres más cortos (más específicos)
            return $candidato['coincidencias'] * 1000 - count($candidato['palabrasBD']);
        });

        $docente = $candidatos->first()['user'] ?? null;

        return $docente;
    }

    /**
     * Procesa el horario (formato: HH:MM-HH:MM)
     */
    protected function procesarHorario(string $horario): array
    {
        $horario = trim($horario);

        if (empty($horario)) {
            throw new \Exception("El horario está vacío");
        }

        // Formato esperado: "08:00-09:00" o "08:00 - 09:00"
        $partes = preg_split('/\s*-\s*/', $horario);

        if (count($partes) !== 2) {
            throw new \Exception("Formato de horario inválido '{$horario}'. Use formato HH:MM-HH:MM");
        }

        $horaInicio = trim($partes[0]);
        $horaFin = trim($partes[1]);

        // Validar formato de horas
        if (!preg_match('/^\d{2}:\d{2}$/', $horaInicio)) {
            throw new \Exception("Hora de inicio inválida '{$horaInicio}'. Use formato HH:MM");
        }

        if (!preg_match('/^\d{2}:\d{2}$/', $horaFin)) {
            throw new \Exception("Hora de fin inválida '{$horaFin}'. Use formato HH:MM");
        }

        // Validar que hora_fin > hora_inicio
        if ($horaFin <= $horaInicio) {
            throw new \Exception("La hora de fin debe ser mayor que la hora de inicio");
        }

        return [
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
        ];
    }

    /**
     * Procesa los miembros del tribunal (3 filas)
     */
    protected function procesarMiembros(array $filas): array
    {
        $miembros = [
            'PRESIDENTE' => null,
            'INTEGRANTE1' => null,
            'INTEGRANTE2' => null,
        ];

        $designacionesEsperadas = [
            'presidente' => 'PRESIDENTE',
            'integrante 1' => 'INTEGRANTE1',
            'integrante 2' => 'INTEGRANTE2',
            'integrante1' => 'INTEGRANTE1',
            'integrante2' => 'INTEGRANTE2',
        ];

        foreach ($filas as $fila) {
            $designacion = strtolower(trim($fila['designacion'] ?? ''));
            $nombreDocente = trim($fila['docentes'] ?? '');

            // Ignorar filas con designación vacía o inválida (como "on" que viene de checkboxes)
            if (empty($designacion) || strlen($designacion) < 3) {
                continue;
            }

            // Normalizar designación
            $statusFinal = $designacionesEsperadas[$designacion] ?? null;

            if (!$statusFinal) {
                throw new \Exception("Designación inválida '{$designacion}'. Use: Presidente, Integrante 1, Integrante 2");
            }

            // Buscar docente
            $docente = $this->buscarDocente($nombreDocente);

            if (!$docente) {
                throw new \Exception("Docente '{$nombreDocente}' no encontrado en el sistema");
            }

            // Validar que no sea el mismo docente repetido
            if (in_array($docente->id, array_filter($miembros))) {
                throw new \Exception("El docente '{$nombreDocente}' está asignado múltiples veces en el mismo tribunal");
            }

            $miembros[$statusFinal] = $docente->id;
        }

        // Validar que todos los roles estén asignados
        if (in_array(null, $miembros)) {
            throw new \Exception("Faltan miembros del tribunal. Debe haber Presidente, Integrante 1 e Integrante 2");
        }

        // Validar que los 3 docentes sean diferentes
        $idsUnicos = array_unique(array_values($miembros));
        if (count($idsUnicos) !== 3) {
            throw new \Exception("Los 3 miembros del tribunal deben ser docentes diferentes");
        }

        return $miembros;
    }

    /**
     * Valida que no haya solapamiento de horarios en el mismo laboratorio
     */
    protected function validarHorariosSolapados(string $fecha, string $horaInicio, string $horaFin, ?string $laboratorio = null)
    {
        $query = Tribunale::where('carrera_periodo_id', $this->carrera_periodo_id)
            ->where('fecha', $fecha)
            ->where('es_plantilla', false);

        // Solo validar solapamiento en el mismo laboratorio (si se especifica)
        if (!empty($laboratorio)) {
            $query->where('laboratorio', $laboratorio);
        }

        $tribunalesEnFecha = $query->get();

        foreach ($tribunalesEnFecha as $tribunal) {
            // Verificar solapamiento
            $solapamiento = (
                $horaInicio < $tribunal->hora_fin &&
                $horaFin > $tribunal->hora_inicio
            );

            if ($solapamiento) {
                $estudiante = $tribunal->estudiante;
                $labInfo = !empty($laboratorio) ? " en el laboratorio {$laboratorio}" : "";
                throw new \Exception(
                    "El horario {$horaInicio}-{$horaFin}{$labInfo} se solapa con el tribunal de " .
                    "{$estudiante->getNombreCompleto()} ({$tribunal->hora_inicio}-{$tribunal->hora_fin})"
                );
            }
        }
    }

    /**
     * Define la fila donde comienzan los encabezados
     */
    public function headingRow(): int
    {
        return 6; // Ajustar según el Excel
    }

    /**
     * Obtener errores
     */
    public function getErrores(): array
    {
        return $this->errores;
    }

    /**
     * Obtener cantidad de tribunales exitosos
     */
    public function getExitosos(): int
    {
        return $this->exitosos;
    }
}
