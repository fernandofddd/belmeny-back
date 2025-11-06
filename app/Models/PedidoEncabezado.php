<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoEncabezado extends Model
{
    use HasFactory;

    protected $table = 'e010_PedidoEncabezado';

    public $timestamps = false;

    protected $fillable = [
        'Documento',
        'Latitud',
        'Longitud',
        'Codcliente',
        'NombreCliente',
        'Vendedor',
        'TipoPedido',
        'FormaPago',
        'fechayhora',
        'NumeroOrden',
        'Responsable',
        'Comentarios',
        'Descargado',
        'Monto',
        'AplicaDescuento',
        'Equipo',
        'Version',
        'Descuento',
        'Agencia',
        'FormaPagoDescuento',
    ];
}
