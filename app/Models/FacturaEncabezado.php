<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaEncabezado extends Model
{
    use HasFactory;

    protected $table = 'e100_FacturaEncabezado';

    public $timestamps = false;

    protected $fillable = [
        'Documento',
        'Agencia',
        'codcliente',
        'nombrecli',
        'CodigoVendedor',
        'FechaDocumento',
        'DiasCredito',
        'FechaVencimiento',
        'TotalFact',
        'BaseImponible',
        'Flete',
        'Abonado',
        'TasaIVA',
        'Alicuota',
        'Descuento',
        'RetencionIVA',
        'TotalPend',
        'Estatus',
        'DiasVencido'
    ];
}
