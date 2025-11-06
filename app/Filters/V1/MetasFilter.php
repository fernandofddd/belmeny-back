<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class MetasFilter extends ApiFilter
{

    protected $safeParms = [
        // Encabezado
        'vendedor' => ['eq'],
        'vert' => ['eq'],
        'ingco' => ['eq'],
        'wadfow' => ['eq'],
        'imou' => ['eq'],
        'quilosa' => ['eq'],
        'fleximatic' => ['eq'],
        'articulo' => ['eq'],
        'global' => ['eq'],
        'zona' => ['eq'],
        'total_vendido' => ['eq'],
        'PeriodoInicio' => ['eq'],
        'PeriodoFin' => ['eq'],
        'VentasIngco' => ['eq'],
        'VentasVert' => ['eq'],
        'VentasWadfow' => ['eq']
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
