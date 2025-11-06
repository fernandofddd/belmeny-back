<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaDetalle extends Model
{
    use HasFactory;

    protected $table = 'e110_FacturaDetalle';
    // protected $table = 'Temp110_FacturaDetalle';

    public $timestamps = false;

    protected $fillable = [
        'Documento',
        'Agencia',
        'Grupo',
        'fechadoc',
        'Cliente',
        'Codigo',
        'Nombre',
        'Cantidad',
        'TasaIVA',
        'PrecioUnitario',
        'SubImpuesto',
        'Subtotal',
        'CodigoVendedor',
        'CodCliente',
    ];
}
