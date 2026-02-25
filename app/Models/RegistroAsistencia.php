<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroAsistencia extends Model
{
    use HasFactory;

    protected $table = 'registros_asistencias';

    protected $fillable = [
        'empleado_temporal_id',
        'tipo',
        'fecha_hora',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    /**
     * Relación: un registro pertenece a un empleado temporal.
     */
    public function empleadoTemporal(): BelongsTo
    {
        return $this->belongsTo(EmpleadoTemporal::class, 'empleado_temporal_id');
    }
}
