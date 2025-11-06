<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresupuestosDetalle extends Model
{
    use HasFactory;

    protected $table = 'w020_PresupuestoDetalle';

    public $timestamps = false;

    protected $fillable = [
        'Documento',
        'Agencia',
        'CodigoCliente',
        'Codigo',
        'Nombre',
        'ListaPrecio',
        'PrecioUnit',
        'Cantidad',
        'Subtotal',
        'Convertido',
        'Grupo',
        'Subgrupo'
    ];
}
