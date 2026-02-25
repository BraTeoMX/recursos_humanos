<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmpleadoTemporal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmpleadoTemporalController extends Controller
{
    /**
     * Muestra el listado de empleados temporales con formulario de registro.
     */
    public function index(): Response
    {
        $empleados = EmpleadoTemporal::orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->get();

        return Inertia::render('admin/EmpleadosTemporales/Index', [
            'empleados' => $empleados,
        ]);
    }

    /**
     * Almacena un nuevo empleado temporal en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'numero_tag'       => 'required|string|max:50|unique:empleados_temporales,numero_tag',
            'nombre'           => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
        ], [
            'numero_tag.required'       => 'El número de tag es obligatorio.',
            'numero_tag.unique'         => 'Este número de tag ya está registrado.',
            'nombre.required'           => 'El nombre es obligatorio.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
        ]);

        EmpleadoTemporal::create($validated);

        return redirect()->route('admin.empleados-temporales.index')
            ->with('success', 'Empleado registrado correctamente.');
    }

    /**
     * Elimina un empleado temporal.
     */
    public function destroy(EmpleadoTemporal $empleadoTemporal): RedirectResponse
    {
        $empleadoTemporal->delete();

        return redirect()->route('admin.empleados-temporales.index')
            ->with('success', 'Empleado eliminado correctamente.');
    }
}
