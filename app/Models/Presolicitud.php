<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presolicitud extends Model
{
    use HasFactory;

    protected $table = 'presolicitud_exhibidores';

    public $timestamps = false;

    public $fillable = [
        'cliente',
        'sugerencia',
        'imagenes_id',
        'fecha_solicitud',
        'rif',
        'marca'
    ];
}
