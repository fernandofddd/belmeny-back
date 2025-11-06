<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentasVendedores extends Model
{
    use HasFactory;

    protected $table = 'w006_ventas_vendedores';

    public $timestamps = null;

    protected $fillable = [
        'vendedor',
        'ventas',
        'mes',
        'year'
    ];
}
