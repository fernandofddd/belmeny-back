<?php

namespace App\Filters\V1\Vendedor;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class CobranzasFilter extends ApiFilter
{

    protected $safeParms = [
        // Encabezado
        'Documento' => ['eq'],
        'CodCliente' => ['eq'],
        'NombreCliente' => ['eq'],
        'FechaCobranza' => ['eq'],
        'Responsable' => ['eq'],
        'TotalCobranza' => ['eq'],
        'Comentarios' => ['eq'],
        'Usuario' => ['eq'],
        'DocumentoAfectado' => ['eq'],
        'MontoFactura' => ['eq'],
        'Descargado' => ['eq'],
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
