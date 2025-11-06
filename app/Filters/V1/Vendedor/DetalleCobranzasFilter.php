<?php

namespace App\Filters\V1\Vendedor;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class DetalleCobranzasFilter extends ApiFilter
{

    protected $safeParms = [
        // Encabezado
        'Documento' => ['eq'],
        'FormaPago' => ['eq'], //Transferencia o Efectivo
        'DocumentoFormaPago' => ['eq'], //Referencia
        'BancoPago' => ['eq'],
        'FechaPago' => ['eq'],
        'MontoParcial' => ['eq', 'lt', 'lte', 'gt', 'gte'],
        'Recibo' => ['eq'],
        'TasadelDia' => ['eq'],
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
