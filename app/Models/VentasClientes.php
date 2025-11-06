<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentasClientes extends Model
{
    use HasFactory;

    protected $table = 'w005_ventas_clientes';

    public $timestamps = null;

    protected $fillable = [
        'vendedor',
        'ventas',
        'mes',
        'codcliente',
        'nombrecli',
        'year'
    ];
}
