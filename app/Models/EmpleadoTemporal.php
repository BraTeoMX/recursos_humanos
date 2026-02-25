<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmpleadoTemporal extends Model
{
    use HasFactory;

    protected $table = 'empleados_temporales';

    protected $fillable = [
        'numero_tag',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
    ];

    /**
     * Nombre completo del empleado.
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    /**
     * Relación: un empleado puede tener muchos registros de asistencia.
     */
    public function registrosAsistencias(): HasMany
    {
        return $this->hasMany(RegistroAsistencia::class, 'empleado_temporal_id');
    }
}
