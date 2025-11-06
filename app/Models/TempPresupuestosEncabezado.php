<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempPresupuestosEncabezado extends Model
{
    use HasFactory;

    protected $table = 'temp_w010_PresupuestoEncabezado';

    public $timestamps = false;

    protected $fillable = [
        'Documento',
        'Codcliente',
        'NombreCliente',
        'Vendedor',
        'FormaPago',
        'FechaPresupuesto',
        'Monto',
        'Convertido',
        'Descuento',
        'DiasPromocion',
        'TipoPromocion',
        'MontoPromocion',
        'Lubricante',
    ];
}
