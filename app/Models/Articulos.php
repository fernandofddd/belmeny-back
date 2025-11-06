<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Articulos extends Model
{
    use HasFactory;

    protected $table = 'a020_articulos';

    public $timestamps = false;

    protected $fillable = [
        'Codigo',
        'Nombre',
        'Precio1',
        'Precio2',
        'Precio3',
        'Precio4',
        'Precio5',
        'Existencia',
        'RutaImagen',
        'VentaMinima',
        'Empresa'
    ];
}
