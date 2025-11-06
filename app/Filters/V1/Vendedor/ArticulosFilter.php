<?php

namespace App\Filters\V1\Vendedor;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class ArticulosFilter extends ApiFilter
{

    protected $safeParms = [
        // Encabezado
        'Codigo' => ['eq'],
        'Nombre' => ['eq'],
        'Precio1' => ['eq'],
        'Precio2' => ['eq'],
        'Precio3' => ['eq'],
        'Precio4' => ['eq'],
        'Existencia' => ['eq'],
        'VentaMinima' => ['eq'],
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
