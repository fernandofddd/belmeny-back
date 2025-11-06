<?php

namespace App\Filters\V1\Vendedor;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class PedidosFilter extends ApiFilter
{

    protected $safeParms = [
        // Encabezado
        'Documento' => ['eq'],
        'Agencia' => ['eq'],
        'Latitud' => ['eq'],
        'Longitud' => ['eq'],
        'Codcliente' => ['eq'],
        'NombreCliente' => ['eq'],
        'Vendedor' => ['eq'],
        'TipoPedido' => ['eq'],
        'FormaPago' => ['eq'],
        'fechayhora' => ['eq', 'gt', 'lt'],
        'NumeroOrden' => ['eq'],
        'Responsable' => ['eq'],
        'Comentarios' => ['eq'],
        'Descargado' => ['eq'],
        'Monto' => ['eq', 'gt', 'lt'],
        'AplicaDescuento' => ['eq'],
        'Equipo' => ['eq'],
        'Version' => ['eq'],
        'Descuento' => ['eq']
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
