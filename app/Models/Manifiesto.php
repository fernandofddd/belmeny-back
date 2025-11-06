<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manifiesto extends Model
{
    use HasFactory;

    protected $table = 'z050_ManifiestoEncabezado';

    public $timestamps = false;
    
    protected $fillable = [
        'Documento',
        'Estado',
        'Chofer',
        'ChoferCI',
        'Vehiculo',
        'Placa',
        'Marca',
        'Color',
        'FechaSalida',
        'EmpresaTransporte',
        'Observacion',
        'Usuario',
        'Fecha',
    ];
}
