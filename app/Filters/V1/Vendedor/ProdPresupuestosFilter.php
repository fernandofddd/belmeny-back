<?php

namespace App\Filters\V1\Vendedor;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class ProdPresupuestosFilter extends ApiFilter
{

    protected $safeParms = [
        'Documento' => ['eq'],
        'CodigoCliente' => ['eq'],
        'Codigo' => ['eq'],
        'Nombre' => ['eq'],
        'PrecioUnit' => ['eq'],
        'Cantidad' => ['eq'],
        'Subtotal' => ['eq'],
    ];

    protected $columnMap = [];

    protected $operatorMap = [
        'eq' => '=',
        'lt' => '<',
        'lte' => '<=',
        'gt' => '>',
        'gte' => '>=',
    ];
}
