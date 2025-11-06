<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Metas extends Model
{
    use HasFactory;

    protected $table = 'w003_metas';

    public $timestamps = null;

    protected $fillable = [
        'vendedor',
        'nombre',
        'vert',
        'ingco',
        'wadfow',
        'imou',
        'quilosa',
        'fleximatic',
        'articulo',
        'global',
        'zona',
        'total_vendido',
        'PeriodoInicio',
        'PeriodoFin',
        'VentasIngco',
        'VentasVert',
        'VentasWadfow'
    ];
}
