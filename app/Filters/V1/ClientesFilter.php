<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class ClientesFilter extends ApiFilter
{

    protected $safeParms = [
        // Encabezado
        'Codigo' => ['eq'],
        'Empresa' => ['eq'],
        'Nombre' => ['eq'],
        'Rif' => ['eq'],
        'Vendedor' => ['eq'],
        'DireccionFiscal' => ['eq'],
        'Telefono1' => ['eq'],
        'Correo' => ['eq'],
        'Limite' => ['eq'],
        'Descuento' => ['eq'],
        'Dias' => ['eq'],
        'Ventas' => ['eq'],
        'Cobranzas' => ['eq'],
        'Devolucion' => ['eq'],
        'Catalogo' => ['eq'],
        'SaldoPendiente' => ['eq'],
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
