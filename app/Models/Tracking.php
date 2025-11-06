<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    use HasFactory;

    protected $table = 's060_despacho';

    public $timestamps = null;

    protected $fillable = [
        'Documento',
        'NombreCliente',
        'CodVendedor',
        'FechaCreacion',
        'FinDepositario',
        'FinEmpacador',
        'Facturacion',
        'FechaEnvio',
        'FechaAnulacion',
        'FechaSalida',
        'Cajas',
        'Bolsas',
        'Estado',
        'DocManifiesto',
    ];
}
