<?php

namespace App\Filters\V1\Vendedor;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class ProdPedidosFilter extends ApiFilter
{

    protected $safeParms = [
        'Documento' => ['eq'],
        'CodigoCliente' => ['eq'],
        'Codigo' => ['eq'],
        'Nombre' => ['eq'],
        'ListaPrecio' => ['eq'],
        'PrecioUnit' => ['eq'],
        'Cantidad' => ['eq'],
        'Subtotal' => ['eq'],
        'FechaHora' => ['eq'],
        'Descargado' => ['eq'],
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
