<?php

namespace App\Http\Controllers\Api;
use Carbon\Carbon;
// Controllers
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseController as BaseController;

// Models
use App\Models\Clientes;
use App\Models\VentasClientes;

// Query filters
use App\Filters\V1\ClientesFilter;

// Resources
use App\Http\Resources\ClientesResource;
use App\Http\Resources\ClientesCollection;


// Http and supports
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends BaseController
{
    protected $supervisor_general = [
        'S02' => ['S02','S06','S07'],
        'S001' => ['S08', 'S09'],
    ];

    protected $supervisor_names = [
        'S02' => 'Luis Sastre (Grupo)', // Nombre del supervisor principal del grupo
        'S001' => 'Grupo S001',
        'S01' => 'Lissete Nuñez',
       'S03' => 'Javier Villasmil',
       'S05' => 'ARELYS COLMENARES',
       'S04' => 'ADEL CODALLO',
    ];

    public function getClientes(Request $request)
    {
        $clientes = DB::table('a010_clientes as ac')
            ->leftJoin('e100_FacturaEncabezado as fe', 'ac.Codigo', '=', 'fe.codcliente')
            ->selectRaw("ac.Codigo, ac.Nombre, ac.Vendedor, ac.DireccionFiscal, ac.Telefono1, ac.Limite, ac.Descuento, ac.Dias as DiasCredito, ac.Ventas, ac.Cobranza, ac.Devolucion, ac.Catalogo, CASE WHEN MAX(fe.FechaDocumento) IS NULL OR fe.FechaDocumento = '' THEN 'N/A' ELSE MAX(fe.FechaDocumento) END AS FechaDocumento, ac.SaldoPendiente")
            ->where('ac.Vendedor', '=', $request->Vendedor)
            ->groupBy('ac.Codigo')
            ->orderBy('fe.FechaDocumento', 'DESC')
            ->paginate(15);

        return response()->json($clientes);
    }

    public function getClientesXLSX(Request $request)
    {
        $clientes = DB::table('a010_clientes as ac')
            ->leftJoin('e100_FacturaEncabezado as fe', 'ac.Codigo', '=', 'fe.codcliente')
            ->selectRaw("ac.Codigo, ac.Nombre, ac.Vendedor, ac.DireccionFiscal, ac.Telefono1, ac.Limite, ac.Descuento, ac.Dias as DiasCredito, ac.Ventas, ac.Cobranza, ac.Devolucion, ac.Catalogo, CASE WHEN MAX(fe.FechaDocumento) IS NULL OR fe.FechaDocumento = '' THEN 'N/A' ELSE MAX(fe.FechaDocumento) END AS FechaDocumento, ac.SaldoPendiente")
            ->where('ac.Vendedor', '=', $request->Vendedor)
            ->groupBy('ac.Codigo')
            ->orderBy('FechaDocumento', 'ASC')
            ->get();

        return response()->json($clientes);
    }

    public function searchClientesbyRif(Request $request)
    {
        $clientes = DB::table('a010_clientes as ac')
            ->join('e100_FacturaEncabezado as fe', 'ac.Codigo', '=', 'fe.codcliente')
            ->select('ac.Codigo', 'ac.Agencia', 'ac.Nombre', 'ac.Rif', 'ac.Vendedor', 'ac.DireccionFiscal', 'ac.Telefono1', 'ac.Limite', 'ac.Descuento', 'ac.Dias', 'ac.Ventas', 'ac.Cobranza', 'ac.Devolucion', 'ac.Catalogo', 'fe.FechaDocumento', 'ac.SaldoPendiente')
            ->where('ac.Vendedor', '=', $request->Vendedor)
            ->where('ac.Codigo', 'LIKE', '%' . $request->Codigo . '%')
            ->groupBy('ac.Codigo')
            ->orderBy('fe.FechaDocumento', 'DESC')
            ->paginate(15);

        return response()->json($clientes);
    }

    public function searchClientesbyNombre(Request $request)
    {
        $clientes = DB::table('a010_clientes as ac')
            ->join('e100_FacturaEncabezado as fe', 'ac.Codigo', '=', 'fe.codcliente')
            ->select('ac.Codigo', 'ac.Agencia', 'ac.Nombre', 'ac.Rif', 'ac.Vendedor', 'ac.DireccionFiscal', 'ac.Telefono1', 'ac.Limite', 'ac.Descuento', 'ac.Dias', 'ac.Ventas', 'ac.Cobranza', 'ac.Devolucion', 'ac.Catalogo', 'fe.FechaDocumento', 'ac.SaldoPendiente')
            ->where('ac.Vendedor', '=', $request->Vendedor)
            ->where('ac.Nombre', 'LIKE', '%' . $request->Nombre . '%')
            ->groupBy('ac.Codigo')
            ->orderBy('fe.FechaDocumento', 'DESC')
            ->paginate(15);

        return response()->json($clientes);
    }

    public function getClientesByVendedorAndRif(Request $request)
    {
        $clientes = DB::table('a010_clientes')
            ->select('*')
            ->where('Vendedor', '=', $request->Vendedor)
            ->where('Codigo', 'LIKE', '%' . $request->Codigo . '%')
            ->orderBy('Nombre', 'asc')
            ->get();

        return response()->json($clientes);
    }

    public function getClientesRif(Request $request)
    {
        $clientes = DB::table('a010_clientes')
            ->select('*')
            ->where('Codigo', '=', $request->Codigo)
            ->orderBy('Nombre', 'asc')
            ->get();

        // $porcen=DB::table('a011_clienteTopProductos')
        //     ->select('Codigo','porcentaje_vert','porcentaje_ingco')
        //     ->where('Codigo', '=', $request->Codigo)
        //     ->first();

        // foreach ($clientes as $cliente) {
        //     $cliente->porcentaje_vert= $porcen->porcentaje_vert;
        //     $cliente->porcentaje_ingco= $porcen->porcentaje_ingco;
        // }
        return response()->json($clientes);
    }

    public function getClientesVendedor(Request $request)
    {
        $clientes = DB::table('a010_clientes')
            ->select('Nombre', 'Codigo', 'Vendedor', 'SaldoPendiente')
            ->where('Vendedor', '=', $request->Vendedor)
            ->orderBy('Nombre', 'asc')
            ->get();

        return response()->json($clientes);
    }

    public function getClientesVendedorFecha(Request $request)
    {
        $cliente = DB::table('w005_ventas_clientes')->select('year')->where('codcliente', '=', $request->codcliente)->where('year', '=','2022')->first();

        $year = $cliente && isset($cliente->year) ? $cliente->year : null;

    return response()->json([
        'year' => $year
    ]);
    }

    public function getClientesVendedorFecha2(Request $request)
    {
        $cliente = DB::table('w005_ventas_clientes')->select('year')->where('codcliente', '=', $request->codcliente)->where('year', '=','2023')->first();

        $year = $cliente && isset($cliente->year) ? $cliente->year : null;

    return response()->json([
        'year' => $year
    ]);
    }

    public function getClientesVendedorFecha3(Request $request)
    {
        $cliente = DB::table('w005_ventas_clientes')->select('year')->where('codcliente', '=', $request->codcliente)->where('year', '=','2024')->first();

        $year = $cliente && isset($cliente->year) ? $cliente->year : null;

    return response()->json([
        'year' => $year
    ]);
    }

    public function getClientesVendedorFecha4(Request $request)
    {
        $cliente = DB::table('w005_ventas_clientes')->select('year')->where('codcliente', '=', $request->codcliente)->where('year', '=','2025')->first();

        $year = $cliente && isset($cliente->year) ? $cliente->year : null;

    return response()->json([
        'year' => $year
    ]);
    }

    public function getVendedor(Request $request)
    {
        if ($request->CodVendedor === "V1") {
            $vendedor = DB::table('b040_usuario')
                ->select('*')
                ->where('CodVendedor', '=', $request->CodVendedor)
                ->where('Usuario', '=', 'obelmeny')
                ->get();
        } else {
            $vendedor = DB::table('b040_usuario')
                ->select('*')
                ->where('CodVendedor', '=', $request->CodVendedor)
                ->get();
        }


        return response()->json($vendedor);
    }

    public function getClientePorNombre(Request $request)
    {
        $cliente = DB::table('a010_clientes')
            ->where('Nombre', 'LIKE', '%' . $request->Nombre . '%')
            ->where('Vendedor', '=', $request->Vendedor)
            ->orderBy('Nombre', 'asc')
            ->get();

        return response()->json($cliente);
    }

    public function getClientePorRif(Request $request)
    {
        $cliente = DB::table('a010_clientes')
            ->select('*')
            ->where('Codigo', 'LIKE', '%' . $request->Codigo . '%')
            ->where('Vendedor', '=', $request->Vendedor)
            ->orderBy('Nombre', 'asc')
            ->first();

        return response()->json($cliente);
    }

    public function getClientePorRif2(Request $request)
    {
        $cliente = DB::table('a010_clientes')
            ->select('*')
            ->where('Codigo', 'LIKE', '%' . $request->Codigo . '%')
            ->orderBy('Nombre', 'asc')
            ->first();

        return response()->json($cliente);
    }

    public function getDeudasCliente(Request $request)
    {
        $cliente = DB::table('a010_clientes as ac')
            ->join('e100_FacturaEncabezado as fe', 'ac.Codigo', '=', 'fe.codcliente')
            ->select('ac.Codigo', 'ac.Nombre', 'fe.CodigoVendedor', 'fe.TotalFinal', 'fe.FechaDocumento')
            ->where('fe.Estatus', '<', '2')
            ->where('fe.CodigoVendedor', '=', $request->CodigoVendedor)
            ->orderBy('ac.Nombre', 'ASC')
            ->get();

        return response()->json($cliente);
    }

    public function getVentasClientes(Request $request)
    {
        $ventasClientes = VentasClientes::select('vendedor', 'mes as months', 'ventas', 'year', 'codcliente')
            ->where('year', '=', $request->year)
            ->where('codcliente', '=', $request->codcliente)
            ->groupBy('months')
            ->orderByRaw('MONTH(months)')
            ->get();

        return response()->json($ventasClientes);
    }

    public function getVentasVendedorIngco(Request $request)
    {
        $ventasClientes = DB::table('w008_ventas_vendedor_top100 as ve')
            ->select('codvend', 'mes as months', 'ventas_ingco', 'year', 'nombrevend')
            ->where('year', '=', $request->year)
            ->where('codvend', '=', $request->vendedor)
            ->groupBy('months')
            ->orderByRaw('MONTH(months)')
            ->get();

        return response()->json($ventasClientes);
    }

    public function getVentasSupervisorIngco(Request $request)
    {
        $supervisorsToQuery = [];
        $displaySupervisorCode = $request->vendedor;
        $displaySupervisorName = $this->supervisor_names[$request->vendedor] ?? $request->vendedor;

        if (isset($this->supervisor_general[$request->vendedor])) {
            $supervisorsToQuery = $this->supervisor_general[$request->vendedor];
        } else {
            $supervisorsToQuery = [$request->vendedor];
        }

        $ventasClientes = DB::table('w008_ventas_supervisor_top100')
            ->select(
                DB::raw("'$displaySupervisorCode' as supervisor"),
                'mes as months',
                DB::raw('SUM(ventas_ingco) as ventas_ingco'),
                DB::raw("'$displaySupervisorName' as nombre")
            )
            ->where('year', '=', $request->year)
            ->whereIn('supervisor', $supervisorsToQuery)
            ->groupBy('months', 'year')
            ->orderByRaw('MONTH(months)')
            ->get();

        return response()->json($ventasClientes);
    }

    public function getVentasClienteIngco(Request $request)
    {
        $ventasClientes = DB::table('w008_ventas_clientes_top100 as ve')
            ->select('vendedor', 'mes as months', 'ventas_ingco', 'year', 'nombrecli')
            ->where('year', '=', $request->year)
            ->where('codcli', '=', $request->vendedor)
            ->groupBy('months')
            ->orderByRaw('MONTH(months)')
            ->get();

        return response()->json($ventasClientes);
    }


    public function getVentasVendedorVert(Request $request)
    {
        $ventasClientes = DB::table('w008_ventas_vendedor_top100')
            ->select('codvend', 'mes as months', 'ventas_vert', 'year', 'nombrevend')
            ->where('year', '=', $request->year)
            ->where('codvend', '=', $request->vendedor)
            ->groupBy('months')
            ->orderByRaw('MONTH(months)')
            ->get();


        return response()->json($ventasClientes);
    }

    public function getVentasSupervisorVert(Request $request)
    {
       $supervisorsToQuery = [];
        $displaySupervisorCode = $request->vendedor; // Código que quieres que aparezca en el resultado
        $displaySupervisorName = $this->supervisor_names[$request->vendedor] ?? $request->vendedor; // Nombre que quieres que aparezca

        // Determina qué supervisores buscar en la base de datos
        if (isset($this->supervisor_general[$request->vendedor])) {
            // Si el vendedor solicitado es un grupo (ej. S02), usa todos los códigos del grupo
            $supervisorsToQuery = $this->supervisor_general[$request->vendedor];
        } else {
            // Si no es un grupo, solo busca ese supervisor individual
            $supervisorsToQuery = [$request->vendedor];
        }

        $ventasClientes = DB::table('w008_ventas_supervisor_top100')
            ->select(
                DB::raw("'$displaySupervisorCode' as supervisor"), // Usamos el código del grupo como supervisor
                'mes as months',
                DB::raw('SUM(ventas_vert) as ventas_vert'), // Sumamos las ventas de todo el grupo
                'year',
                DB::raw("'$displaySupervisorName' as nombre") // Usamos el nombre del grupo
            )
            ->where('year', '=', $request->year)
            ->whereIn('supervisor', $supervisorsToQuery) // Filtramos por todos los supervisores del grupo
            ->groupBy('months', 'year') // Agrupamos solo por mes y año para una fila consolidada
            ->orderByRaw('MONTH(months)')
            ->get();

        return response()->json($ventasClientes);
    }

    public function getVentasClienteVert(Request $request)
    {
        $ventasClientes = DB::table('w008_ventas_clientes_top100 as ve')
            ->select('vendedor', 'mes as months', 'ventas_vert', 'year', 'nombrecli')
            ->where('year', '=', $request->year)
            ->where('codcli', '=', $request->vendedor)
            ->groupBy('months')
            ->orderByRaw('MONTH(months)')
            ->get();

        return response()->json($ventasClientes);
    }

    public function getClientesAtendidosByVendedor(Request $request)
    {
        $atendidos = DB::table('a010_clientes as ac')
            ->join('e100_FacturaEncabezado as fd', 'ac.Codigo', '=', 'fd.codcliente')
            ->select('ac.codigo', 'ac.nombre', 'ac.vendedor', 'fd.FechaDocumento', 'ac.DireccionFiscal')
            ->where('ac.Vendedor', '=', $request->CodigoVendedor)
            ->whereBetween('fd.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->whereRaw("ac.Codigo IN (SELECT codcliente FROM e100_FacturaEncabezado WHERE CodigoVendedor = '" . $request->CodigoVendedor . "' AND FechaDocumento BETWEEN '" . $request->fechaInicio . "' AND '" . $request->fechaFin . "')")
            ->groupBy('ac.Codigo')
            ->orderBy('fd.FechaDocumento', 'DESC')
            ->get();

        return response()->json($atendidos);
    }

    public function getClientesDesatendidosByVendedor(Request $request)
    {
        if ($request->loadDesatendidos === 'XLSX') {
            $desatendidos = DB::table('w001_clientes_desatendidos as ad')
                ->join('w004_zona as z', 'ad.Vendedor', '=', 'z.CodVendedor')
                ->select('ad.Codigo', 'ad.Nombre', 'z.Nombre as Vendedor', 'ad.DireccionFiscal', 'ad.SaldoPendiente', 'ad.UltimaFactura as UltimaFacturacion')
                ->where('Vendedor', '=', $request->CodigoVendedor)
                ->where('ad.SaldoPendiente', '>', 0)
                ->orderBy('ad.UltimaFactura', 'DESC')
                ->get();
        } else if ($request->loadDesatendidos === 'General') {
            $desatendidos = DB::table('a010_clientes as c')
                ->select('c.*')
                ->leftJoin(DB::raw("(
                SELECT codcliente, FechaDocumento
                FROM e100_FacturaEncabezado
                WHERE FechaDocumento BETWEEN DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 0 MONTH) AND DATE_SUB(DATE_ADD(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 1 MONTH), INTERVAL 1 DAY)
                GROUP BY codcliente
            ) as e"), function ($join) {
                    $join->on('c.Codigo', '=', 'e.codcliente');
                })
                ->where('c.Vendedor', '=', $request->CodigoVendedor)
                ->whereNull('e.codcliente')
                ->orderBy('SaldoPendiente', 'desc')
                ->paginate(15);
        } else if ($request->loadDesatendidos === 'Especifico') {
            $desatendidos = DB::table('a010_clientes as c')
                ->select('c.*')
                ->leftJoin(DB::raw("(
                SELECT codcliente, FechaDocumento
                FROM e100_FacturaEncabezado
                WHERE FechaDocumento BETWEEN ". $request->fechaInicio. ' AND ' .$request->fechaFin.
                " GROUP BY codcliente
            ) as e"), function ($join) {
                    $join->on('c.Codigo', '=', 'e.codcliente');
                })
                ->where('c.Vendedor', '=', $request->CodigoVendedor)
                ->whereNull('e.codcliente')
                ->orderBy('SaldoPendiente', 'desc')
                ->paginate(15);
        }
        return response()->json($desatendidos);
    }

   public function getClientesxSupervisor(Request $request)
    {
        // --- START: Logic to determine $supervisorsToQuery ---
        $supervisorsToQuery = [];
        $requestCodSupervisor = $request->CodSupervisor; // Get the requested supervisor code

        // Check if the requested supervisor is a key in our general supervisor map
        if ($requestCodSupervisor && isset($this->supervisor_general[$requestCodSupervisor])) {
            // If it's a general supervisor (e.g., S02), include all its associated sub-supervisors
            $supervisorsToQuery = $this->supervisor_general[$requestCodSupervisor];
        } else {
            // If it's an individual supervisor not part of a defined group (e.g., S01)
            // or any other user type, treat it as an individual supervisor to query directly.
            $supervisorsToQuery = [$requestCodSupervisor];
        }
        // --- END: Logic to determine $supervisorsToQuery ---

        // --- Query 1: Clientes Activos ---
        $listaClientesQuery = DB::table('a010_clientes as ac')
            ->join('b040_usuario as bu', 'ac.Vendedor', '=', 'bu.CodVendedor')
            ->select('bu.CodVendedor', 'bu.Nombre', DB::raw('COUNT(ac.Codigo) as Clientes_Activos'));

        // Apply the supervisor filter using whereIn
        if (!empty($supervisorsToQuery)) {
            $listaClientesQuery->whereIn('bu.CodSupervisor', $supervisorsToQuery); // <-- KEY CHANGE HERE!
        } else {
            $listaClientesQuery->where('bu.CodSupervisor', '=', $requestCodSupervisor);
        }

        $listaClientes = $listaClientesQuery
            ->whereRaw('bu.CodVendedor NOT IN ("V1", "V2", "T1", "CCS1")')
            ->groupBy('bu.CodVendedor', 'bu.Nombre') // Add bu.Nombre to groupBy for strict SQL modes
            ->orderBy('Clientes_Activos', 'DESC')
            ->get();

        // --- Query 2: Clientes Atendidos ---
        $clientesAtendidosQuery = DB::table('a010_clientes as ac')
            ->rightJoin('b040_usuario as bu', 'ac.Vendedor', '=', 'bu.CodVendedor')
            ->select('ac.Vendedor', DB::raw('COUNT(ac.Codigo) as Atendidos'));

        // Apply the supervisor filter using whereIn
        if (!empty($supervisorsToQuery)) {
            $clientesAtendidosQuery->whereIn('bu.CodSupervisor', $supervisorsToQuery); // <-- KEY CHANGE HERE!
        } else {
            $clientesAtendidosQuery->where('bu.CodSupervisor', '=', $requestCodSupervisor);
        }

        $clientes_atendidos = $clientesAtendidosQuery
            ->whereRaw('ac.Codigo IN (SELECT codcliente FROM e100_FacturaEncabezado WHERE CodigoVendedor = ac.Vendedor AND FechaDocumento BETWEEN "' . $request->fechaInicio . '" AND "' . $request->fechaFin . '")')
            ->groupBy('ac.Vendedor')
            ->get();

        return response()->json(["listaClientes" => $listaClientes, "atendidos" => $clientes_atendidos]);
    }

   public function getClientesAtendidosByGerente(Request $request)
{
    // Consulta base: lista total de clientes por supervisor
    $listaClientes = DB::table('a010_clientes as ac')
        ->join('b040_usuario as bu', 'ac.Vendedor', '=', 'bu.CodVendedor')
        ->leftJoin('b040_usuario as bu2', 'bu.CodSupervisor', '=', 'bu2.CodVendedor') // Self-join para obtener el nombre del supervisor
        ->select(
            'bu.CodSupervisor',
            DB::raw('COUNT(DISTINCT ac.Codigo) AS TotalClientes'),
            DB::raw("CASE
                        WHEN bu.CodSupervisor = 'S01' THEN 'Lisette Nuñez'
                        WHEN bu.CodSupervisor = 'S02' THEN 'Luis Sastre'
                        WHEN bu.CodSupervisor = 'S03' THEN 'Javier Villasmil'
                        WHEN bu.CodSupervisor = 'S04' THEN 'ADEL CODALLO'
                        ELSE bu2.Nombre
                     END AS NombreSupervisor")
        )
        ->whereNotIn('bu.CodVendedor', ['V1', 'V2', 'T1', 'CCS1'])
        ->whereNotIn('bu.CodSupervisor', ['S001'])
        ->groupBy('bu.CodSupervisor')
        ->get();


    // Consulta base: clientes atendidos en el rango por supervisor
    $clientes_atendidos = DB::table('a010_clientes as ac')
        ->join('b040_usuario as bu', 'ac.Vendedor', '=', 'bu.CodVendedor')
        ->leftJoin('b040_usuario as bu2', 'bu.CodSupervisor', '=', 'bu2.CodVendedor') // Self-join para obtener el nombre del supervisor
        ->select(
            'bu.CodSupervisor',
            DB::raw('COUNT(DISTINCT ac.Codigo) AS TotalClientesAtendidos'),
            DB::raw("CASE
                        WHEN bu.CodSupervisor = 'S01' THEN 'Lisette Nuñez'
                        WHEN bu.CodSupervisor = 'S02' THEN 'Lisette Nuñez'
                        WHEN bu.CodSupervisor = 'S03' THEN 'Javier Villasmil'
                        WHEN bu.CodSupervisor = 'S04' THEN 'ADEL CODALLO'
                        ELSE bu2.Nombre
                     END AS NombreSupervisor")
        )
        ->whereIn('ac.Codigo', function ($query) use ($request) {
            $query->select('codcliente')
                ->from('e100_FacturaEncabezado')
                ->whereBetween('FechaDocumento', [$request->fechaInicio, $request->fechaFin]);
        })
        ->whereNotIn('bu.CodVendedor', ['V1', 'V2', 'T1', 'CCS1'])
        ->whereNotIn('bu.CodSupervisor', ['S001'])
        ->groupBy('bu.CodSupervisor')
        ->get();

    // --- Post-procesado: agregar totales de subsupervisores al supervisor principal ---
    // Asegurarse de que $this->supervisor_general existe y es array
    // Excluir explícitamente estos supervisores del resultado final
    $excludedSupervisors = ['S001']; // <-- añade aquí otros si hace falta

    if (!empty($this->supervisor_general) && is_array($this->supervisor_general)) {
        // Convertir a collections para facilitar operaciones
        $listaCollect = collect($listaClientes);
        $atendidosCollect = collect($clientes_atendidos);

        // 1) Aplicar filtro inicial: eliminar filas ya existentes de supervisores excluidos
        $listaCollect = $listaCollect->reject(function ($row) use ($excludedSupervisors) {
            /** @var object $row */ // <--- ¡Añade esta línea! O mejor aún, el tipo específico
            return in_array($row->CodSupervisor, $excludedSupervisors);
        })->values();

        $atendidosCollect = $atendidosCollect->reject(function ($row) use ($excludedSupervisors) {
            /** @var object $row */ // <--- ¡Añade esta línea! O mejor aún, el tipo específico
            return in_array($row->CodSupervisor, $excludedSupervisors);
        })->values();

        foreach ($this->supervisor_general as $principal => $subs) {
            // si el supervisor principal está en la lista de excluidos, saltarlo
            if (in_array($principal, $excludedSupervisors)) {
                continue;
            }
            if (!is_array($subs) || count($subs) === 0) continue;

            // Asegurarnos de no considerar subs que estén en la lista excluida
            $subsFiltered = array_values(array_diff($subs, $excludedSupervisors));
            if (count($subsFiltered) === 0) {
                // nada que agregar si todos los subs están excluidos
                continue;
            }

            // Sumar TotalClientes para todos los supervisores del grupo (solo los permitidos)
            $sumTotalClientes = $listaCollect
                ->filter(function ($row) use ($subsFiltered) {
                    /** @var object $row */ // <--- ¡Añade esta línea! O mejor aún, el tipo específico
                    return in_array($row->CodSupervisor, $subsFiltered);
                })
                ->sum('TotalClientes');

            // Actualizar o añadir fila para el supervisor principal en listaClientes
            $idx = $listaCollect->search(function ($row) use ($principal) {
                /** @var object $row */ // <--- ¡Añade esta línea! O mejor aún, el tipo específico
                return $row->CodSupervisor === $principal;
            });

            // Nombre: intentar conservar NombreSupervisor existente si hay; si no, tomar el primero del grupo; si no, dejar el código
            $foundPrincipal = $listaCollect->firstWhere('CodSupervisor', $principal);
           $foundSubsFirst = $listaCollect->first(function ($r) use ($subsFiltered) {
                /** @var object $r */ // <--- ¡Añade esto!
                // O si sabes el nombre de la clase/modelo, úsalo:
                // /** @var \App\Models\TuModelo $r */

                return in_array($r->CodSupervisor, $subsFiltered);
            });

            // Código anterior (donde se asignan las variables)
            $foundPrincipal = $listaCollect->firstWhere('CodSupervisor', $principal);
            // 👉 Aquí añadimos el PHPDoc para foundPrincipal
            /** @var object|null $foundPrincipal */

            $foundSubsFirst = $listaCollect->first(function ($r) use ($subsFiltered) {
                /** @var object $r */ // Ya corregido
                return in_array($r->CodSupervisor, $subsFiltered);
            });
            // 👉 Y aquí añadimos el PHPDoc para foundSubsFirst
            /** @var object|null $foundSubsFirst */

            // --- Tu fragmento actual comienza aquí ---

            $nombreFromGroup = null;
            if ($foundPrincipal && property_exists($foundPrincipal, 'NombreSupervisor')) {
                $nombreFromGroup = $foundPrincipal->NombreSupervisor; // Ya no debería dar warning
            } elseif ($foundSubsFirst && property_exists($foundSubsFirst, 'NombreSupervisor')) {
                $nombreFromGroup = $foundSubsFirst->NombreSupervisor; // Ya no debería dar warning
            } else {
                $nombreFromGroup = $principal;
            }

            // Cerca de donde defines la colección (ej: $listaCollect = collect($listaClientes);)
            /** @var \Illuminate\Support\Collection<int, object> $listaCollect */
            // O si es un modelo/clase real:
            // /** @var \Illuminate\Support\Collection<int, \App\Models\MiCliente> $listaCollect */
            if ($idx !== false) {
                // actualizar valor (si la fila existe)
                $listaCollect[$idx]->TotalClientes = (int)$sumTotalClientes;
            } else {
                // añadir nueva fila para el supervisor principal (asegurando estructura similar a la query)
                $listaCollect->push((object)[
                    'CodSupervisor' => $principal,
                    'TotalClientes' => (int)$sumTotalClientes,
                    'NombreSupervisor' => $nombreFromGroup
                ]);
            }

            // Repetir para clientes_atendidos (TotalClientesAtendidos)
            $sumAtendidos = $atendidosCollect
                ->filter(function ($row) use ($subsFiltered) {
                    /** @var object $row */ // <--- ¡Añade esta línea! O mejor aún, el tipo específico
                    return in_array($row->CodSupervisor, $subsFiltered);
                })
                ->sum('TotalClientesAtendidos');

            $idxA = $atendidosCollect->search(function ($row) use ($principal) {
                /** @var object $row */ // <--- ¡Añade esta línea! O mejor aún, el tipo específico
                return $row->CodSupervisor === $principal;
            });

            $foundPrincipalA = $atendidosCollect->firstWhere('CodSupervisor', $principal);
            $foundSubsFirstA = $atendidosCollect->first(function ($r) use ($subsFiltered) {
                /** @var object $r */ // <--- ¡Añade esto!
                // O si sabes el nombre de la clase/modelo, úsalo:
                // /** @var \App\Models\TuModelo $r */
                return in_array($r->CodSupervisor, $subsFiltered); });

            // CÓDIGO ANTERIOR: donde se asignaron las variables 'A'
            // $foundPrincipalA = $atendidosCollect->firstWhere(...);
            /** @var object|null $foundPrincipalA */

            // $foundSubsFirstA = $atendidosCollect->first(...);
            /** @var object|null $foundSubsFirstA */

            // --- Tu fragmento actual comienza aquí ---

            $nombreFromGroupAt = null;
            if ($foundPrincipalA && property_exists($foundPrincipalA, 'NombreSupervisor')) {
                $nombreFromGroupAt = $foundPrincipalA->NombreSupervisor; // El warning debería desaparecer
            } elseif ($foundSubsFirstA && property_exists($foundSubsFirstA, 'NombreSupervisor')) {
                $nombreFromGroupAt = $foundSubsFirstA->NombreSupervisor; // El warning debería desaparecer
            } else {
                $nombreFromGroupAt = $principal;
            }

            /** @var \Illuminate\Support\Collection<int, object> $atendidosCollect */
            // O si sabes el modelo exacto (ej: \App\Models\ClienteAtendido):
            // /** @var \Illuminate\Support\Collection<int, \App\Models\ClienteAtendido> $atendidosCollect */
            if ($idxA !== false) {
                $atendidosCollect[$idxA]->TotalClientesAtendidos = (int)$sumAtendidos;
            } else {
                $atendidosCollect->push((object)[
                    'CodSupervisor' => $principal,
                    'TotalClientesAtendidos' => (int)$sumAtendidos,
                    'NombreSupervisor' => $nombreFromGroupAt
                ]);
            }
        }

        // Reindexar collections y devolver arrays
        $listaClientes = $listaCollect->values()->all();
        $clientes_atendidos = $atendidosCollect->values()->all();
    }

    return response()->json(["listaClientes" => $listaClientes, "atendidos" => $clientes_atendidos]);
}





    public function getDetalleClientesAtendidosxVendedor(Request $request)
    {
        $detalleClientes = DB::table('a010_clientes as c')
            ->select('c.*', DB::raw('MAX(e.FechaDocumento) as FechaDocumento'))
            ->join('e100_FacturaEncabezado as e', 'c.Codigo', '=', 'e.codcliente')
            ->where('c.Vendedor', '=', $request->CodigoVendedor)
            ->whereBetween('e.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->groupBy('c.Codigo')
            ->orderBy('c.SaldoPendiente', 'DESC')
            ->get();

        return response()->json($detalleClientes);
    }

    public function getZonasClientes(Request $request)
    {
        $zonasClientes = DB::table('w004_zonas_clientes')
            ->select('Zona')
            ->groupBy('Zona')
            ->orderBy('Zona', 'ASC')
            ->get();

        return response()->json($zonasClientes);
    }

    // ClientesController.php (añadir)
public function getDetalleClientesPorCodigo(Request $request)
{
    $codigo = $request->input('codigo'); // Vxx | Sxx | HABOULMOUNA ...
    $modo = $request->input('modo', 'activos'); // 'activos'|'atendidos'|'desatendidos'
    $fechaInicioRaw = $request->input('fechaInicio');
    $fechaFinRaw = $request->input('fechaFin');
    $paginate = filter_var($request->input('paginate', true), FILTER_VALIDATE_BOOLEAN);
    $perPage = intval($request->input('perPage', 15));
    // limitar perPage para proteger el servidor
    $perPage = max(5, min($perPage, 200));

    // parsear fechas de forma segura (si vienen nulas, tomar inicio y fin del mes actual)
    try {
        $fechaInicio = $fechaInicioRaw ? Carbon::parse($fechaInicioRaw)->startOfDay() : Carbon::now()->startOfMonth();
    } catch (\Exception $e) {
        $fechaInicio = Carbon::now()->startOfMonth();
    }
    try {
        $fechaFin = $fechaFinRaw ? Carbon::parse($fechaFinRaw)->endOfDay() : Carbon::now()->endOfMonth();
    } catch (\Exception $e) {
        $fechaFin = Carbon::now()->endOfMonth();
    }

    // 1) resolver vendedores según codigo / supervisor (mejorado para subsupervisores)
    $vendedores = [];

    //  Permitimos que el front envíe CodSupervisor explícito; si no viene usamos 'codigo'
    $requestedSupervisor = $request->input('CodSupervisor', $codigo);

    if ($requestedSupervisor && isset($this->supervisor_general[$requestedSupervisor])) {
        $supervisorsToQuery = $this->supervisor_general[$requestedSupervisor];
        $vendedores = DB::table('b040_usuario')
            ->whereIn('CodSupervisor', $supervisorsToQuery)
            ->whereNotIn('CodVendedor', ['V1','V2','T1','CCS1'])
            ->pluck('CodVendedor')
            ->toArray();
    } else {
        if ($requestedSupervisor && stripos($requestedSupervisor, 'S') === 0) {
            $vendedores = DB::table('b040_usuario')
                ->where('CodSupervisor', $requestedSupervisor)
                ->whereNotIn('CodVendedor', ['V1','V2','T1','CCS1'])
                ->pluck('CodVendedor')
                ->toArray();
        } elseif ($codigo && stripos($codigo, 'V') === 0) {
            $vendedores = [$codigo];
        } else {
            $vendedores = DB::table('b040_usuario')
                ->when($codigo, function($q) use ($codigo) {
                    $q->where('CodGerente', $codigo);
                })
                ->whereNotIn('CodVendedor', ['V1','V2','T1','CCS1'])
                ->pluck('CodVendedor')
                ->toArray();
        }
    }

    if (empty($vendedores)) {
        return response()->json(['data' => [], 'current_page' => 1, 'last_page' => 1, 'total' => 0]);
    }

    // --- ACTIVOS: SOLO desde tabla clientes, sin cruzar facturas (más barato) ---
    if ($modo === 'activos') {
        $q = DB::table('a010_clientes as c')
            ->leftJoin('b040_usuario as u', 'c.Vendedor', '=', 'u.CodVendedor') // <-- join para traer nombre del vendedor
            ->select('c.*', DB::raw('u.Nombre AS NombreVendedor'))
            ->whereIn('c.Vendedor', $vendedores)
            ->orderBy('c.SaldoPendiente', 'DESC')
            ->orderBy('c.Nombre', 'ASC');

        if ($paginate) {
            $pag = $q->paginate(3000);
            return $pag;
        } else {
            return response()->json(['data' => $q->get()]);
        }
    }

    // --- ATENDIDOS: clientes que SI tuvieron factura entre fechaInicio y fechaFin ---
    if ($modo === 'atendidos') {
        // subselect: obtener la última fecha de factura por cliente dentro del rango
        $sub = DB::table('e100_FacturaEncabezado')
            ->select('codcliente', DB::raw('MAX(FechaDocumento) as FechaDocumento'))
            ->whereBetween(DB::raw('DATE(FechaDocumento)'), [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->groupBy('codcliente');

        $q = DB::table('a010_clientes as c')
            ->joinSub($sub, 'fe_range', function ($join) {
                $join->on('c.Codigo', '=', 'fe_range.codcliente');
            })
            ->leftJoin('b040_usuario as u', 'c.Vendedor', '=', 'u.CodVendedor') // <-- join vendedor
            ->select('c.*', 'fe_range.FechaDocumento', DB::raw('u.Nombre AS NombreVendedor'))
            ->whereIn('c.Vendedor', $vendedores)
            // agrupar también por las columnas añadidas para evitar problemas en SQL strict
            ->groupBy('c.Codigo', 'fe_range.FechaDocumento', 'u.Nombre')
            ->orderBy('c.SaldoPendiente', 'DESC');

        if ($paginate) {
            $pag = $q->paginate(3000);
            return $pag;
        } else {
            return response()->json(['data' => $q->get()]);
        }
    }

    // --- DESATENDIDOS: clientes SIN factura en el rango ---
    if ($modo === 'desatendidos') {
        $sub = DB::table('e100_FacturaEncabezado')
            ->select('codcliente')
            ->whereBetween('FechaDocumento', [$fechaInicio, $fechaFin])
            ->groupBy('codcliente');

        // Query principal: left join a la subconsulta y quedarnos con NULLs => desatendidos
        $q = DB::table('a010_clientes as c')
            ->leftJoinSub($sub, 'fe_range', function ($join) {
                $join->on('c.Codigo', '=', 'fe_range.codcliente');
            })
            ->leftJoin('b040_usuario as u', 'c.Vendedor', '=', 'u.CodVendedor') // <-- join vendedor
            ->select('c.*', DB::raw('u.Nombre AS NombreVendedor'))
            ->whereIn('c.Vendedor', $vendedores)
            ->whereNull('fe_range.codcliente')
            ->orderBy('c.SaldoPendiente', 'DESC');

        if ($paginate) {
            $pag = $q->paginate(3000);
            return $pag;
        } else {
            return response()->json(['data' => $q->get()]);
        }
    }

    return response()->json(['error' => 'Modo inválido'], 400);
}








    public function getClientesDeudoresXLSX(Request $request)
    {
        $clientes = DB::table('e100_FacturaEncabezado as fe')
            ->join('w004_zona as z', 'fe.CodigoVendedor', '=', 'z.CodVendedor')
            ->select('fe.Documento', 'z.CodVendedor as Vendedor', 'z.Nombre', 'fe.CodCliente as CodigoCliente', 'fe.NombreCli as NombreCliente', 'fe.FechaDocumento', 'fe.FechaVencimiento', 'fe.TotalFact as Total Facturado', 'fe.TotalPend as Total Pendiente', 'fe.Estatus as Estado')
            ->whereRaw("fe.Estatus <= 1 and fe.FechaVencimiento < NOW() and fe.FechaVencimiento > '2023-06-01 00:00:00' and z.CodVendedor = '" . $request->CodigoVendedor . "'")
            ->groupBy('fe.Documento')
            ->orderBy('fe.NombreCli', 'ASC')
            ->orderBy('fe.FechaVencimiento', 'DESC')
            ->get();

        return response()->json($clientes);
    }
}
