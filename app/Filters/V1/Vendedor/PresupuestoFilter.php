<?php

namespace App\Filters\V1\Vendedor;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class PresupuestoFilter extends ApiFilter
{

    protected $safeParms = [
        // Encabezado
        'id' => ['eq'],
        'Documento' => ['eq', 'like'],
        'Codcliente' => ['eq'],
        'NombreCliente' => ['eq'],
        'Vendedor' => ['eq'],
        'FechaPresupuesto' => ['eq'],
        'FormaPago' => ['eq'],
        'Monto' => ['eq', 'gt', 'lt'],
        'Convertido' => ['eq'],
        'Descuento' => ['eq'],
        'DiasPromocion' => ['eq'],
        'TipoPromocion' => ['eq'],
        'MontoPromocion' => ['eq'],
        'Lubricante' => ['eq'],
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
