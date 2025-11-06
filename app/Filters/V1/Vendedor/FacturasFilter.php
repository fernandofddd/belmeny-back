<?php

namespace App\Filters\V1\Vendedor;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class FacturasFilter extends ApiFilter
{

    protected $safeParms = [
        // Encabezado
        'Documento' => ['eq'],
        'codcliente' => ['eq'],
        'CodigoVendedor' => ['eq'],
        'FechaDocumento' => ['eq', 'gt', 'lt'],
        'DiasCredito' => ['eq'],
        'FechaVencimiento' => ['eq', 'gt', 'lt'],
        'TotalFact' => ['eq'],
        'BaseImponible' => ['eq'],
        'Flete' => ['eq'],
        'Abonado' => ['eq'],
        'TasaIVA' => ['eq'],
        'Alicuota' => ['eq'],
        'Descuento' => ['eq'],
        'RetencionIVA' => ['eq'],
        'TotalPend' => ['eq'],
        'nombrecli' => ['eq']
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
