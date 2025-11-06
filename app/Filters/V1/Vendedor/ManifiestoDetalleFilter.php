<?php

namespace App\Filters\V1\Vendedor;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class ManifiestoDetalleFilter extends ApiFilter
{

    protected $safeParms = [
        // Encabezado
        'DocumentoDetalle' => ['eq'],
        'DocPedido' => ['eq'],
        'DocFactura' => ['eq'],
        'Cliente' => ['eq'],
        'FFacturacion' => ['eq'],
        'Zona' => ['eq'],
        'SubZona' => ['eq'],
        'Cajas' => ['eq'],
        'Bolsas' => ['eq'],
        'BaseImponible' => ['eq'],
        'FechaSalida' => ['eq'],
        'DireccionDespacho' => ['eq'],
        'Vendedor' => ['eq']
    ];

    protected $columnMap = [];

    protected $operatorMap = [
        'eq' => '=',
        'lt' => '<',
        'lte' => '<=',
        'gt' => '>',
        'gte' => '>=',
        'like' => '%'
    ];
}
