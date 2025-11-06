<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exhibidor extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_exhibidores';

    public $timestamps = null;

    protected $fillable = [
        'nro_solicitud',
        'nro_pedido',
        'usuario',
        'cliente',
        'marca',
        'motivo_solicitud',
        'tipo_exhibidor',
        'ancho',
        'alto',
        'material_exhibidor',
        'monto_exhibidor',
        'aprobacion_supervisor',
        'aprobacion_gerencia',
        'leido_vendedor',
        'leido_supervisor',
        'leido_gerencia',
        'fecha_solicitud',
    ];
}
