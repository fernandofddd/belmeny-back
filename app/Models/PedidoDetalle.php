<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoDetalle extends Model
{
    use HasFactory;

    protected $table = 'e020_PedidoDetalle';

    public $timestamps = false;

    protected $fillable = [
        'Documento',
        'CodigoCliente',
        'Codigo',
        'Nombre',
        'ListaPrecio',
        'PrecioUnit',
        'Cantidad',
        'Subtotal',
        'FechaHora',
        'Descargado',
        'Agencia'
    ];
}
