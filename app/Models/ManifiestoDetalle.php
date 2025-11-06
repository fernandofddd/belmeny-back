<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManifiestoDetalle extends Model
{
    use HasFactory;

    protected $table = 'z051_ManifiestoDetalle';

    public $timestamps = false;

    protected $fillable = [
        'DocumentoDetalle',
        'DocPedido',
        'DocFactura',
        'NombreCliente',
        'Cliente',
        'FFacturacion',
        'Zona',
        'SubZona',
        'Cajas',
        'Bolsas',
        'BaseImponible',
        'FechaSalidaDetalle',
        'DireccionDespacho',
        'Vendedor'
    ];
}
