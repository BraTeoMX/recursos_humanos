<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\EmpleadoTemporal;
use App\Models\RegistroAsistencia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PortalPublicoController extends Controller
{
    /**
     * Vista: Menú principal del portal público.
     */
    public function menu(): Response
    {
        return Inertia::render('public/MenuPrincipal');
    }

    /**
     * Vista: Registro de asistencia con tabla de registros del día.
     */
    public function registroAsistencia(): Response
    {
        $registrosHoy = RegistroAsistencia::with('empleadoTemporal')
            ->whereDate('fecha_hora', Carbon::today())
            ->where('tipo', 'asistencia')
            ->orderByDesc('fecha_hora')
            ->get()
            ->map(fn($r) => [
                'id'             => $r->id,
                'numero_tag'     => $r->empleadoTemporal->numero_tag,
                'nombre_completo' => $r->empleadoTemporal->nombre_completo,
                'fecha_hora'     => $r->fecha_hora->format('H:i:s'),
                'tipo'           => $r->tipo,
            ]);

        return Inertia::render('public/RegistroAsistencia', [
            'registrosHoy' => $registrosHoy,
        ]);
    }

    /**
     * Vista: Registro de retardo.
     */
    public function registroRetardo(): Response
    {
        $registrosHoy = RegistroAsistencia::with('empleadoTemporal')
            ->whereDate('fecha_hora', Carbon::today())
            ->where('tipo', 'retardo')
            ->orderByDesc('fecha_hora')
            ->get()
            ->map(fn($r) => [
                'id'             => $r->id,
                'numero_tag'     => $r->empleadoTemporal->numero_tag,
                'nombre_completo' => $r->empleadoTemporal->nombre_completo,
                'fecha_hora'     => $r->fecha_hora->format('H:i:s'),
                'tipo'           => $r->tipo,
            ]);

        return Inertia::render('public/RegistroRetardo', [
            'registrosHoy' => $registrosHoy,
        ]);
    }

    /**
     * Vista: Registro de visitas.
     */
    public function registroVisitas(): Response
    {
        return Inertia::render('public/RegistroVisitas');
    }

    // -------------------------------------------------------------------------
    // Endpoints JSON para búsqueda AJAX (llamados desde el frontend Vue)
    // -------------------------------------------------------------------------

    /**
     * Busca un empleado por número de tag exacto.
     * GET /api/publico/buscar-por-tag?tag=XXX
     */
    public function buscarPorTag(Request $request): JsonResponse
    {
        $request->validate(['tag' => 'required|string|max:50']);

        $tag = Str::upper(trim((string) $request->string('tag')));

        $empleado = EmpleadoTemporal::query()
            ->whereRaw('UPPER(numero_tag) = ?', [$tag])
            ->first();

        if (! $empleado) {
            return response()->json(['encontrado' => false, 'mensaje' => 'Tag no encontrado.'], 404);
        }

        return response()->json([
            'encontrado'       => true,
            'id'               => $empleado->id,
            'numero_tag'       => $empleado->numero_tag,
            'nombre'           => $empleado->nombre,
            'apellido_paterno' => $empleado->apellido_paterno,
            'apellido_materno' => $empleado->apellido_materno,
        ]);
    }

    /**
     * Busca empleados por nombre (búsqueda flexible con LIKE).
     * GET /api/publico/buscar-por-nombre?nombre=XXX
     */
    public function buscarPorNombre(Request $request): JsonResponse
    {
        $request->validate(['nombre' => 'required|string|min:2|max:100']);

        $tokens = collect(preg_split('/\s+/', trim((string) $request->string('nombre'))))
            ->filter(fn($token) => $token !== '')
            ->map(fn($token) => mb_strtolower($token))
            ->values();

        $empleados = EmpleadoTemporal::query()
            ->when($tokens->isNotEmpty(), function ($query) use ($tokens) {
                foreach ($tokens as $token) {
                    $query->whereRaw(
                        "LOWER(CONCAT_WS(' ', nombre, apellido_paterno, COALESCE(apellido_materno, ''))) LIKE ?",
                        ['%' . $token . '%']
                    );
                }
            })
            ->limit(10)
            ->get(['id', 'numero_tag', 'nombre', 'apellido_paterno', 'apellido_materno']);

        return response()->json($empleados);
    }

    /**
     * Guarda un nuevo registro de asistencia/retardo/visita.
     * POST /publico/guardar-registro
     */
    public function guardarRegistro(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'empleado_temporal_id' => 'required|exists:empleados_temporales,id',
            'tipo'                 => 'required|in:asistencia,retardo,visita',
        ]);

        $registro = RegistroAsistencia::create([
            'empleado_temporal_id' => $validated['empleado_temporal_id'],
            'tipo'                 => $validated['tipo'],
            'fecha_hora'           => now(),
        ]);

        $registro->load('empleadoTemporal');

        return response()->json([
            'exito'          => true,
            'mensaje'        => '¡Registro guardado correctamente!',
            'id'             => $registro->id,
            'numero_tag'     => $registro->empleadoTemporal->numero_tag,
            'nombre_completo' => $registro->empleadoTemporal->nombre_completo,
            'fecha_hora'     => $registro->fecha_hora->format('H:i:s'),
            'tipo'           => $registro->tipo,
        ]);
    }

    /**
     * Refresca la tabla de registros del día.
     * GET /api/publico/registros-hoy?tipo=asistencia
     */
    public function registrosHoy(Request $request): JsonResponse
    {
        $tipo = $request->get('tipo', 'asistencia');

        $registros = RegistroAsistencia::with('empleadoTemporal')
            ->whereDate('fecha_hora', Carbon::today())
            ->where('tipo', $tipo)
            ->orderByDesc('fecha_hora')
            ->get()
            ->map(fn($r) => [
                'id'             => $r->id,
                'numero_tag'     => $r->empleadoTemporal->numero_tag,
                'nombre_completo' => $r->empleadoTemporal->nombre_completo,
                'fecha_hora'     => $r->fecha_hora->format('H:i:s'),
                'tipo'           => $r->tipo,
            ]);

        return response()->json($registros);
    }
}
