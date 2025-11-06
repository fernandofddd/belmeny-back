<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    use HasFactory;

    protected $table = 'a010_clientes';

    public $timestamps = null;

    protected $fillable = [
        'Codigo',
        'Agencia',
        'Nombre',
        'Rif',
        'Vendedor',
        'DireccionFiscal',
        'Telefono1',
        'Correo',
        'Limite',
        'Descuento',
        'Dias',
        'Ventas',
        'Cobranzas',
        'Devolucion',
        'Catalogo',
        'SaldoPendiente',
        'ListaPrecios'
    ];
}
