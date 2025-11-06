<?php

namespace App\Http\Controllers\Api;

// Controllers
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseController as BaseController;

// Models
use App\Models\FacturaEncabezado;
use App\Models\FacturaDetalle;
use App\Models\Clientes;

// Query filters
use App\Filters\V1\Vendedor\FacturasFilter;
use App\Filters\V1\Vendedor\ProdFacturasFilter;

// Resources
use App\Http\Resources\Vendedor\Facturas\FacturasResource;
use App\Http\Resources\Vendedor\Facturas\FacturasCollection;
use App\Http\Resources\Vendedor\Facturas\ProdFacturasCollection;
use App\Http\Resources\Vendedor\Facturas\ProdFacturasResource;

// Http and supports
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturasController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $excludedVendors = ['V1', 'V2', 'T1', 'CCS1'];

    protected $supervisor_general = [
        'S02' => ['S02','S06', 'S07'],
        'S001' => ['S08', 'S09'],
    ];
    
    protected $supervisor_names = [
        'S02' => 'Luis Sastre (Grupo)', // Nombre del supervisor principal del grupo
        'S001' => 'Grupo S001',
        'S01' => 'Lissete Nuñez',
       'S03' => 'Javier Villasmil',
       'S05' => 'ARELYS COLMENARES',
       'S04' => 'ADEL CODALLO',
       'S06' => 'Antonio Perez',
        'S07' => 'Carlos Valiente',
    ];
    
    public function index(Request $request)
    {
        $filter = new FacturasFilter();
        $filterItems = $filter->transform($request);

        $facturas = FacturaEncabezado::where($filterItems)->orderByDesc('FechaDocumento');

        return new FacturasCollection($facturas->paginate(15)->appends($request->query()));
    }

    public function getFacturasxGerente(Request $request)
    {
        $supervisorsToQuery = [];
        
        if ($request->CodSupervisor && substr($request->CodSupervisor, 0, 1) === 'S') {
            if (isset($this->supervisor_general[$request->CodSupervisor])) {
                $supervisorsToQuery = $this->supervisor_general[$request->CodSupervisor];
            } else {
                $supervisorsToQuery = [$request->CodSupervisor];
            }
        }
        $facturasQuery = DB::table('e100_FacturaEncabezado as fe')
            ->join('w004_zona as z', 'fe.CodigoVendedor', '=', 'z.CodVendedor')
            ->select('fe.*', 'z.Nombre');
            if(!empty($supervisorsToQuery)){
                $facturasQuery->whereIn('z.CodSupervisor', $supervisorsToQuery);
            }else{
                $facturasQuery->where('z.CodSupervisor', '=', $request->CodSupervisor);
            }           

            $facturas = $facturasQuery->orderBy('fe.FechaDocumento', 'DESC')
            ->paginate(15);

        return response()->json($facturas);
    }

    public function getFacturasxCobranzasGerencia(Request $request)
    {
        // Inicializamos
    $supervisorsToQuery = [];

    // 1) Si nos pasan CodGerente -> obtenemos todos los supervisores asociados a ese gerente
    if ($request->filled('CodGerente')) {
        // Ajusta 'CodGerente' si el campo tiene otro nombre en tu tabla 'w004_zona'
        $supervisorsToQuery = DB::table('w004_zona')
            ->where('CodGerente', $request->CodGerente)
            ->pluck('CodSupervisor')
            ->unique()
            ->filter()    // elimina null/'' si hay
            ->values()
            ->toArray();
    }
    // 2) Si nos pasan CodSupervisor -> expandimos usando supervisor_general o usamos el propio
    elseif ($request->filled('CodSupervisor')) {
        $cod = $request->CodSupervisor;

        // Si se pasa 'ALL' (o similar) interpretamos como no filtrar por supervisor
        if (strtoupper($cod) === 'ALL') {
            $supervisorsToQuery = []; // sin filtro
        } elseif (substr($cod, 0, 1) === 'S') {
            if (isset($this->supervisor_general[$cod]) && is_array($this->supervisor_general[$cod])) {
                $supervisorsToQuery = $this->supervisor_general[$cod];
            } else {
                $supervisorsToQuery = [$cod];
            }
        } else {
            // si viene otro formato, lo tratamos como un supervisor simple
            $supervisorsToQuery = [$cod];
        }
    }

    // Construimos la query principal
    $facturasQuery = DB::table('e100_FacturaEncabezado as fe')
        ->join('w004_zona as z', 'fe.CodigoVendedor', '=', 'z.CodVendedor')
        // selecciona solo los campos que necesites; aquí devuelvo la factura y nombre vendedor y supervisor
        ->select('fe.*', 'z.Nombre as VendedorNombre', 'z.CodSupervisor');

    // Aplicamos filtro por supervisores si tenemos alguno
    if (!empty($supervisorsToQuery)) {
        $facturasQuery->whereIn('z.CodSupervisor', $supervisorsToQuery);
    }

    // Filtro opcional por fecha recibido desde frontend (si quieres)
    if ($request->filled('fechaInicio') && $request->filled('fechaFin')) {
        $facturasQuery->whereBetween('fe.FechaDocumento', [$request->fechaInicio, $request->fechaFin]);
    }

    // Puedes añadir otros filtros que consideres (estado, cliente, etc.)
    if ($request->filled('whatToSearch') && $request->filled('Busqueda')) {
    $q = $request->Busqueda;
    switch ($request->whatToSearch) {
        case 'Documento':
            $facturasQuery->where('fe.Documento', 'like', "%{$q}%");
            break;
        case 'Cliente':
            $facturasQuery->where('fe.nombrecli', 'like', "%{$q}%");
            break;
        case 'Vendedor':
    $q = trim($request->Busqueda ?? '');

    if ($q === '') {
        break;
    }

    // Si vienen varios códigos separados por comas -> whereIn sobre CodVendedor
    if (strpos($q, ',') !== false) {
        $codes = array_filter(array_map('trim', explode(',', $q)));
        if (!empty($codes)) {
            $facturasQuery->whereIn('z.CodVendedor', $codes);
        }
    }
    // Si parece un código (ej. empieza por V o es solo números) -> igualdad
    elseif (preg_match('/^[Vv]?\d+$/', $q)) {
        $facturasQuery->where('z.CodVendedor', $q);
    }
    // Si es texto -> buscar por nombre (case-insensitive)
    else {
        // Usamos LOWER para comparación insensible a mayúsculas (funciona en MySQL/Postgres)
        $facturasQuery->whereRaw('LOWER(z.Nombre) LIKE ?', [ '%'.mb_strtolower($q).'%']);
    }
    break;

    }
}

if ($request->filled('fechaInicio') && $request->filled('fechaFin')) {
    $facturasQuery->whereBetween('fe.FechaDocumento', [$request->fechaInicio, $request->fechaFin]);
}

    // Paginación configurable
    $perPage = intval($request->get('per_page', 15));
    $facturas = $facturasQuery->orderBy('fe.FechaDocumento', 'DESC')
        ->paginate($perPage);
    
    return response()->json($facturas);
    }

    public function MontoTotalFacturas(Request $request)
    {
        $TotalFinal = FacturaEncabezado::where('CodigoVendedor', $request->CodigoVendedor)
            ->whereBetween('FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->sum('TotalFinal');
        return response()->json($TotalFinal);
    }

    public function getProgresoMensual(Request $request)
    {
        $TotalVendido = FacturaEncabezado::where('CodigoVendedor', $request->CodigoVendedor)
            ->whereBetween('FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->sum('TotalFinal');
        return response()->json($TotalVendido);
    }

    public function getProductosFactura(Request $request)
    {
        $filter = new ProdFacturasFilter();
        $filterItems = $filter->transform($request);
        $productos = FacturaDetalle::where($filterItems);

        return new ProdFacturasCollection($productos->paginate(50)->appends($request->query()));
    }

    public function getCantidadProductos(Request $request)
    {
        $cantProds = DB::table("e110_FacturaDetalle")
            ->select(DB::raw("COUNT(Codigo) AS CantProductos"))
            ->where("Documento", "=", $request->Documento)
            ->get();

        return response()->json($cantProds);
    }

    public function getProductosFacturaPDF(Request $request)
    {
        $productos = DB::table("e110_FacturaDetalle as f")
            ->leftJoin("w050_inventario_codBarras_nueva as cb", "f.Codigo", '=', 'cb.codigo')
            ->select("f.*", DB::raw("COALESCE(cb.codalternativo, 'N/A') as codalternativo"))
            ->where("Documento", "=", $request->Documento)
            ->where("f.CodCliente", $request->CodigoCliente)
            ->groupBy("f.Codigo")
            ->paginate(500);

        return response()->json($productos);
    }

    public function getClientePorRif(Request $request)
    {
        $cliente = Clientes::where('Rif', $request->Rif)->get();

        return response()->json($cliente);
    }

    public function getFacturasPorBusqueda(Request $request)
    {
        $codUsuario = $request->CodUsuario;

        // --- START: Logic to determine $supervisorsToQuery ---
        $supervisorsToQuery = [];
        // Only if $codUsuario starts with 'S', then it might be a supervisor (individual or group)
        if ($codUsuario && substr($codUsuario, 0, 1) === 'S') {
            if (isset($this->supervisor_general[$codUsuario])) {
                // If it's a general supervisor, include all its associated sub-supervisors
                $supervisorsToQuery = $this->supervisor_general[$codUsuario];
            } else {
                // If it's an individual supervisor not part of a defined group
                $supervisorsToQuery = [$codUsuario];
            }
        }
        // --- END: Logic to determine $supervisorsToQuery ---

        $query = DB::table('e100_FacturaEncabezado as fe')
            ->join('w004_zona as z', 'fe.CodigoVendedor', '=', 'z.CodVendedor')
            ->select('fe.*', 'z.Nombre');

        // --- Initial filtering based on CodUsuario type ---
        if (substr($codUsuario, 0, 1) === 'V') {
            // For vendors ('V' codes), filter by their CodVendedor
            $query->where('z.CodVendedor', '=', $codUsuario);
        } else {
            // For non-'V' users (including supervisors and others like CCS1)
            // Apply the supervisor group logic
            if (!empty($supervisorsToQuery)) {
                // If it's a supervisor group (or an 'S' individual), use whereIn
                $query->whereIn('z.CodSupervisor', $supervisorsToQuery);
            } else {
                // If it's a non-'V' user that's not an 'S' supervisor (e.g., CCS1),
                // or an 'S' supervisor not recognized as a group key, filter by exact CodSupervisor.
                $query->where('z.CodSupervisor', '=', $codUsuario);
            }
        }

        // --- Dynamic search criteria based on whatToSearch ---
        switch ($request->whatToSearch) {
            case 'Documento':
                $query->where('fe.Documento', 'LIKE', '%' . $request->Busqueda . '%');
                // The initial CodUsuario filter already handles whether it's a Vendedor or Supervisor.
                // No need for redundant where clauses here inside the switch.
                break;

            case 'Cliente':
                $query->where('fe.nombrecli', 'LIKE', '%' . $request->Busqueda . '%');
                // The initial CodUsuario filter already handles whether it's a Vendedor or Supervisor.
                // No need for redundant where clauses here inside the switch.
                break;

            case 'Fecha':
                $query->whereBetween('fe.FechaDocumento', [$request->fechaInicio, $request->fechaFin]);
                // The initial CodUsuario filter already handles whether it's a Vendedor or Supervisor.
                // No need for redundant where clauses here inside the switch.
                break;

            case 'Vendedor':
                // This case is special because it filters by 'Nombre' regardless of CodUsuario type.
                // The initial CodUsuario filter needs to stay as it narrows down the scope first.
                $query->where('z.Nombre', 'LIKE', '%' . $request->Busqueda . '%');
                break;

            default:
                // No additional search filter applied if whatToSearch is not recognized
                break;
        }

        // Finally, execute the query with pagination
        $facturas = $query->orderBy('fe.FechaDocumento', 'desc')->paginate(15);

        return response()->json($facturas);
    }
    public function getFacturasPorFecha(Request $request)
    {
        $facturas = DB::table('e100_FacturaEncabezado')
            ->where('CodigoVendedor', '=', $request->CodigoVendedor)
            ->whereBetween('FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->orderBy('FechaDocumento', 'desc')
            ->paginate(15);

        return response()->json($facturas);

        // $montoFacturas = DB::table('e200_FacturaEncabezado')
        //     ->where('CodigoVendedor', '=', $request->CodigoVendedor)
        //     ->whereBetween('FechaDocumento', [$request->fechaInicio, $request->fechaFin])
        //     ->sum('TotalFinal');


        // return response()->json([
        //     'facturas' => $facturas,
        //     'montoFacturas' => $montoFacturas,
        // ]);
    }

    public function getFacturasPorCliente(Request $request)
    {
        $pedidos = DB::table('e010_PedidoEncabezado')
            ->where('NombreCliente', 'LIKE', '%' . $request->NombreCliente . '%')
            ->where('Vendedor', '=', $request->Vendedor)
            ->orderBy('fechayhora', 'desc')
            ->get();

        return response()->json($pedidos);
    }

    public function getFacturasCliente(Request $request)
    {
        $facturas = FacturaEncabezado::select('*')
            ->where('CodigoVendedor', '=', $request->Vendedor)
            ->where('nombrecli', 'like', '%' . $request->nombrecli . '%')
            ->orderBy('FechaDocumento', 'desc')
            ->paginate(15);

        return response()->json($facturas);
    }

    public function getFacturasDocumento(Request $request)
    {
        //Hacer un switch para ahorrar codigo aca, en busqueda por fecha y cliente

        $facturas = FacturaEncabezado::select('*')
            ->where('CodigoVendedor', '=', $request->Vendedor)
            ->where('Documento', 'like', '%' . $request->Documento . '%')
            ->orderBy('FechaDocumento', 'desc')
            ->paginate(15);

        return response()->json($facturas);
    }

    // GERENTE
    public function getFacturasPorSupervisor(Request $request)
    {
        $facturas = DB::table('e100_FacturaEncabezado as fe')
            ->join('w004_zona as z', 'fe.CodigoVendedor', '=', 'z.CodVendedor')
            ->where('z.CodSupervisor', '=', $request->CodSupervisor)
            ->get();

        return response()->json($facturas);
    }
}
