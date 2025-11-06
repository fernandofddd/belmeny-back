<?php

namespace App\Filters\V1\Vendedor;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class ProdFacturasFilter extends ApiFilter
{

    protected $safeParms = [
        'Documento' => ['eq'],
        'Cliente' => ['eq'],
        'Codigo' => ['eq'],
        'Nombre' => ['eq'],
        'Cantidad' => ['eq'],
        'TasaIVA' => ['eq'],
        'PrecioUnitario' => ['eq'],
        'SubImpuesto' => ['eq'],
        'Subtotal' => ['eq'],
        'CodigoVendedor' => ['eq'],
        'CodCliente' => ['eq'],
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
