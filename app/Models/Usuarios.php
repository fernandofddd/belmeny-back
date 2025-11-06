<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    use HasFactory;

    protected $table = 'b040_usuario';

    public $timestamps = false;

    protected $fillable = [
        'Usuario',
        'Nombre',
        'clave',
        'correo',
        'CodVendedor',
        'CodSupervisor',
        'CodGerente',
        'ZonasVenta',
        'VendeLubricante'
    ];
}