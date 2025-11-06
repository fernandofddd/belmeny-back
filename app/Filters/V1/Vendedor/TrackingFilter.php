<?php

namespace App\Filters\V1\Vendedor;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class TrackingFilter extends ApiFilter
{

    protected $safeParms = [
        // Encabezado
        'Documento' => ['eq'],
        'NombreCliente' => ['eq'],
        'CodVendedor' => ['eq'],
        'FechaCreacion' => ['eq'],
        'FinDepositario' => ['eq'],
        'FinEmpacador' => ['eq'],
        'Facturacion' => ['eq'],
        'FechaEnvio' => ['eq'],
        'FechaAnulacion' => ['eq'],
        'FechaSalida' => ['eq'],
        'Cajas' => ['eq'],
        'Bolsas' => ['eq'],
        'Estado' => ['eq'],
        'DocManifiesto' => ['eq'],
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
