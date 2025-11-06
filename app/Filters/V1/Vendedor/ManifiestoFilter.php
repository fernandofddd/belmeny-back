<?php

namespace App\Filters\V1\Vendedor;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class ManifiestoFilter extends ApiFilter
{

    protected $safeParms = [
        // Encabezado
        'Documento' => ['eq'],
        'Estado' => ['eq'],
        'Chofer' => ['eq'],
        'ChoferCI' => ['eq'],
        'Vehiculo' => ['eq'],
        'Placa' => ['eq'],
        'Marca' => ['eq'],
        'Color' => ['eq'],
        'FechaSalida' => ['eq'],
        'EmpresaTransporte' => ['eq'],
        'Observacion' => ['eq'],
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
