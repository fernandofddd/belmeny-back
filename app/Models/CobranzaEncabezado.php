<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CobranzaEncabezado extends Model
{
    use HasFactory;

    protected $table = 's010_CobranzaEncabezado';

    protected $fillable = [
        'Documento',
        'CodCliente',
        'NombreCliente',
        'FechaCobranza',
        'Responsable',
        'TotalCobranza',
        'Comentarios',
        'Usuario',
        'DocumentoAfectado',
        'MontoFactura',
        'Descargado',
    ];
}
