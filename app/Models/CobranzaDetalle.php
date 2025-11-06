<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CobranzaDetalle extends Model
{
    use HasFactory;

    protected $table = 's020_CobranzaDetalle';

    protected $fillable = [
        'Documento',
        'FormaPago', //Transferencia o Efectivo
        'DocumentoFormaPago', //Referencia
        'BancoPago',
        'FechaPago',
        'MontoParcial',
        'Recibo',
        'TasadelDia',
    ];
}
