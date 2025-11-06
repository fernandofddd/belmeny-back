<?php

namespace App\Http\Controllers\Api;

// Controllers
use App\Http\Controllers\Controller;

// Models
use App\Models\CobranzaEncabezado;
use App\Models\CobranzaDetalle;

// Query filters
use App\Filters\V1\Vendedor\CobranzasFilter;
use App\Filters\V1\Vendedor\DetalleCobranzasFilter;

// Resources
use App\Http\Resources\Vendedor\CobranzasVendedor\CobranzasResource;
use App\Http\Resources\Vendedor\CobranzasVendedor\CobranzasCollection;
use App\Http\Resources\Vendedor\CobranzasVendedor\DetalleCobranzaResource;
use App\Http\Resources\Vendedor\CobranzasVendedor\DetalleCobranzaCollection;

// Http and supports
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CobranzasController extends Controller
{
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

    public function getCobranzasxCliente(Request $request)
    {
        $supervisorsToQuery = [];
        if ($request->Usuario && substr($request->Usuario, 0, 1) === 'S') {
            if (isset($this->supervisor_general[$request->Usuario])) {
                $supervisorsToQuery = $this->supervisor_general[$request->Usuario];
            } else {
                $supervisorsToQuery = [$request->Usuario];
            }
        }
        $cobranza = null; 

        if (substr($request->Usuario, 0, 1) === 'V') {
            $cobranza = DB::table("s010_CobranzaEncabezado as c")
                ->join('w004_zona as z', 'c.Usuario', '=', 'z.CodVendedor')
                ->select('c.*', 'z.Nombre')
                ->where('c.Usuario', '=', $request->Usuario) // Filters directly by the user's V code
                ->orderBy('c.FechaCobranza', 'desc')
                ->paginate(15);
        } else {
            $cobranzaQuery = DB::table("s010_CobranzaEncabezado as c")
                ->join('w004_zona as z', 'c.Usuario', '=', 'z.CodVendedor')
                ->select('c.*', 'z.Nombre');
            if (!empty($supervisorsToQuery)) {
            $cobranzaQuery->whereIn('z.CodSupervisor', $supervisorsToQuery);
            } else {
                $cobranzaQuery->where('z.CodSupervisor', '=', $request->Usuario);
            }

            $cobranza = $cobranzaQuery->orderBy('c.FechaCobranza', 'desc')
                ->paginate(15);
        }
        
        return response()->json($cobranza);
    }

    public function getDetallesCobranzas(Request $request)
    {
        $filter = new DetalleCobranzasFilter();
        $filterItems = $filter->transform($request);
        $productos = CobranzaDetalle::where($filterItems);

        return new DetalleCobranzaCollection($productos->paginate(50)->appends($request->query()));
    }

    public function getCobranzasPorBusqueda(Request $request)
    {
        $codUsuario = $request->CodUsuario;

        $supervisorsToQuery = [];
        if ($codUsuario && substr($codUsuario, 0, 1) === 'S') {
            if (isset($this->supervisor_general[$codUsuario])) {
                $supervisorsToQuery = $this->supervisor_general[$codUsuario];
            } else {
                $supervisorsToQuery = [$codUsuario];
            }
        }

        $query = DB::table('s010_CobranzaEncabezado as c')
            ->join('w004_zona as z', 'c.Usuario', '=', 'z.CodVendedor')
            ->select('c.*', 'z.Nombre');

        if (substr($codUsuario, 0, 1) === 'V') {
            $query->where('z.CodVendedor', '=', $codUsuario);
        } else {
            if (!empty($supervisorsToQuery)) {
                $query->whereIn('z.CodSupervisor', $supervisorsToQuery);
            } else {
                $query->where('z.CodSupervisor', '=', $codUsuario);
            }
        }
        switch ($request->whatToSearch) {
            case 'Documento':
                $query->where('c.Documento', 'LIKE', '%' . $request->Busqueda . '%');
                break;

            case 'Cliente':
                $query->where('c.NombreCliente', 'LIKE', '%' . $request->Busqueda . '%');
                break;

            case 'Fecha':
                $query->whereBetween('c.FechaCobranza', [$request->fechaInicio, $request->fechaFin]);

            case 'Vendedor':
                $query->where('z.Nombre', 'LIKE', '%' . $request->Busqueda . '%');
                break;

            default:
                break;
        }

        // Finally, execute the query with pagination
        $cobranzas = $query->orderBy('c.FechaCobranza', 'desc')->paginate(15);

        return response()->json($cobranzas);
    }

    public function getListaMasterCobranzas(Request $request)
    {
        $infoMaster = DB::table('Master_Cobranza')
            ->select('*')
            ->orderBy('CodigoVendedor', 'asc')
            ->get();

        return response()->json($infoMaster);
    }

    public function getListaMasterCobranzasZona(Request $request)
    {
        $infoMasterZona = DB::table('Master_Cobranza as mc')
            ->join('metas as m', 'mc.CodigoVendedor', '=', 'm.vendedor')
            ->select('mc.CodigoVendedor', 'mc.Nombre', 'mc.Facturado', 'mc.Pendiente', 'mc.Pagado', 'm.zona')
            ->where('m.zona', '=', $request->zona)
            ->orderBy('mc.CodigoVendedor', 'asc')
            ->get();

        return response()->json($infoMasterZona);
    }

    public function getCobranzaVendedor(Request $request)
    {
        $cobranzaVendedor = DB::table('Master_Cobranza')
            ->select('*')
            ->where('CodigoVendedor', '=', $request->CodigoVendedor)
            ->orderBy('CodigoVendedor', 'asc')
            ->get();

        return response()->json($cobranzaVendedor);
    }

    public function getListaDocVencidos(Request $request)
    {
        $docVencidos = DB::table('e100_FacturaEncabezado as fe')
            ->join('b010_vendedores as v', 'fe.CodigoVendedor', '=', 'v.Codigo')
            ->select('fe.CodigoVendedor', 'v.Nombre', DB::raw('count(fe.Documento) as Nro_documentos'))
            ->where('fe.DiasVencido', '>', '0')
            ->where('fe.Estatus', '<', '2')
            ->whereBetween('fe.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->groupBy('fe.CodigoVendedor')
            ->orderBy('Nro_documentos', 'desc')
            ->get();

        return response()->json($docVencidos);
    }

    public function getDellateDocVencido(Request $request)
    {
        $docVencidos = DB::table('e100_FacturaEncabezado as fe')
            ->join('b010_vendedores as v', 'fe.CodigoVendedor', '=', 'v.Codigo')
            ->select('fe.nombrecli', 'fe.Documento', 'fe.DiasVencido', 'fe.FechaDocumento', 'fe.FechaVencimiento', 'fe.BaseImponible')
            ->where('fe.DiasVencido', '>', '0')
            ->where('fe.Estatus', '<', '2')
            ->where('fe.CodigoVendedor', '=', $request->CodigoVendedor)
            ->whereBetween('fe.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->orderBy('fe.DiasVencido', 'desc')
            ->get();

        return response()->json($docVencidos);
    }

    public function getCobranzasPendientes(Request $request)

    {
        $docVencidos = DB::table('e100_FacturaEncabezado')
            ->select('Documento', 'codcliente', 'FechaDocumento', 'FechaVencimiento', 'TotalFact', 'TotalPend')
            ->where('CodigoVendedor', '=', $request->CodigoVendedor)
            ->orderBy('FechaVencimiento', 'desc')
            ->get();

        return response()->json($docVencidos);
    }

    public function getCobranzasVendedorXLSX(Request $request)
{
    $cod = $request->get('codvendedor');

    // Normalizar valores posibles que indican "todos"
    if (is_null($cod) || $cod === '' || strtolower($cod) === 'null' || strtoupper($cod) === 'ALL') {
        $cod = null;
    }

    $cobranzaVendedor = null;

    // Caso: no se pasa ningún vendedor => Gerencia: devolver todo (pendientes)
    if (is_null($cod)) {
        $cobranzaVendedor = DB::table('e100_FacturaEncabezado AS fe')
            ->select('fe.Documento', 'fe.codcliente', 'fe.nombrecli', 'fe.FechaDocumento', 'fe.FechaVencimiento', 'fe.TotalFact', 'fe.Abonado', 'fe.TotalPend')
            ->join('w004_zona AS z', 'fe.CodigoVendedor', '=', 'z.CodVendedor')
            ->whereColumn('fe.TotalFact', '>', 'fe.Abonado')
            ->orderBy('fe.nombrecli')
            ->orderBy('fe.FechaDocumento', 'desc')
            ->get();

        return response()->json($cobranzaVendedor);
    }

    // Caso: supervisor (empieza por 'S')
    if (substr($cod, 0, 1) === 'S') {
        // construir lista de supervisores a consultar (expandir grupos si aplica)
        $supervisorsToQuery = [];
        if (isset($this->supervisor_general[$cod]) && is_array($this->supervisor_general[$cod])) {
            $supervisorsToQuery = $this->supervisor_general[$cod];
        } else {
            $supervisorsToQuery = [$cod];
        }

        $queryBuilder = DB::table('e100_FacturaEncabezado AS fe')
            ->select('fe.Documento', 'fe.codcliente', 'fe.nombrecli', 'fe.FechaDocumento', 'fe.FechaVencimiento', 'fe.TotalFact', 'fe.Abonado', 'fe.TotalPend')
            ->join('w004_zona AS z', 'fe.CodigoVendedor', '=', 'z.CodVendedor')
            ->whereColumn('fe.TotalFact', '>', 'fe.Abonado');

        if (!empty($supervisorsToQuery)) {
            // filtrar por los supervisores del grupo
            $queryBuilder->whereIn('z.CodSupervisor', $supervisorsToQuery);
        } else {
            // fallback: filtra por el supervisor exacto recibido
            $queryBuilder->where('z.CodSupervisor', $cod);
        }

        $cobranzaVendedor = $queryBuilder
            ->orderBy('fe.nombrecli')
            ->orderBy('fe.FechaDocumento', 'desc')
            ->get();

        return response()->json($cobranzaVendedor);
    }

    // Caso: vendedor individual u otro código (ej. Vxxx)
    $cobranzaVendedor = DB::table('e100_FacturaEncabezado AS fe')
        ->select('fe.Documento', 'fe.codcliente', 'fe.nombrecli', 'fe.FechaDocumento', 'fe.FechaVencimiento', 'fe.TotalFact', 'fe.Abonado', 'fe.TotalPend')
        ->whereColumn('fe.TotalFact', '>', 'fe.Abonado')
        ->where('fe.CodigoVendedor', $cod)
        ->orderBy('fe.nombrecli')
        ->orderBy('fe.FechaDocumento', 'desc')
        ->get();

    return response()->json($cobranzaVendedor);
}


    public function getCobranzasxCliente2(Request $request)
    {
        $supervisorsToQuery = [];

        $usuario = $request->get('Usuario');

        if ($usuario && substr($usuario, 0, 1) === 'S') {
            if (isset($this->supervisor_general[$usuario])) {
                $supervisorsToQuery = $this->supervisor_general[$usuario];
            } else {
                $supervisorsToQuery = [$usuario];
            }
        }

        if (substr($usuario, 0, 1) === 'V') {
            $query = DB::table("s010_CobranzaEncabezado as c")
                ->join('w004_zona as z', 'c.Usuario', '=', 'z.CodVendedor')
                ->select('c.*', 'z.Nombre')
                ->where('c.Usuario', '=', $usuario)
                ->orderBy('c.FechaCobranza', 'desc');
        } else {
            $query = DB::table("s010_CobranzaEncabezado as c")
                ->join('w004_zona as z', 'c.Usuario', '=', 'z.CodVendedor')
                ->select('c.*', 'z.Nombre');

            if (!empty($supervisorsToQuery)) {
                $query->whereIn('z.CodSupervisor', $supervisorsToQuery);
            } else {
                $query->where('z.CodSupervisor', '=', $usuario);
            }

            $query = $query->orderBy('c.FechaCobranza', 'desc');
        }

        $perPage = intval($request->get('per_page', 15));
        $cobranzas = $query->paginate($perPage);

        // normalizar respuesta: meta, links, data
        return response()->json([
            'meta' => [
                'current_page' => $cobranzas->currentPage(),
                'last_page' => $cobranzas->lastPage(),
                'per_page' => $cobranzas->perPage(),
                'total' => $cobranzas->total(),
            ],
            'links' => [
                'first' => $cobranzas->url(1),
                'last' => $cobranzas->url($cobranzas->lastPage()),
                'prev' => $cobranzas->previousPageUrl(),
                'next' => $cobranzas->nextPageUrl(),
            ],
            'data' => $cobranzas->items(),
        ]);
    }

    // 2) getAllCobranzas2 - para gerencia (puedes filtrar por fecha, etc.)
    public function getAllCobranzas2(Request $request)
    {
        $query = DB::table("s010_CobranzaEncabezado as c")
            ->join('w004_zona as z', 'c.Usuario', '=', 'z.CodVendedor')
            ->select('c.*', 'z.Nombre')
            ->orderBy('c.FechaCobranza', 'desc');

        $perPage = intval($request->get('per_page', 15));
        $cobranzas = $query->paginate($perPage);

        return response()->json([
            'meta' => [
                'current_page' => $cobranzas->currentPage(),
                'last_page' => $cobranzas->lastPage(),
                'per_page' => $cobranzas->perPage(),
                'total' => $cobranzas->total(),
            ],
            'links' => [
                'first' => $cobranzas->url(1),
                'last' => $cobranzas->url($cobranzas->lastPage()),
                'prev' => $cobranzas->previousPageUrl(),
                'next' => $cobranzas->nextPageUrl(),
            ],
            'data' => $cobranzas->items(),
        ]);
    }

    // 3) getDetallesCobranzas2 - devuelve detalle (paginado) y normalizado
    public function getDetallesCobranzas2(Request $request)
    {
        $filter = new DetalleCobranzasFilter();
        $filterItems = $filter->transform($request);
        $productosQuery = CobranzaDetalle::where($filterItems);

        $perPage = intval($request->get('per_page', 50));
        $productos = $productosQuery->paginate($perPage);

        return response()->json([
            'meta' => [
                'current_page' => $productos->currentPage(),
                'last_page' => $productos->lastPage(),
                'per_page' => $productos->perPage(),
                'total' => $productos->total(),
            ],
            'links' => [
                'first' => $productos->url(1),
                'last' => $productos->url($productos->lastPage()),
                'prev' => $productos->previousPageUrl(),
                'next' => $productos->nextPageUrl(),
            ],
            'data' => $productos->items(),
        ]);
    }

    // 4) getCobranzasPorBusqueda2 - búsqueda con filtros y paginado
    public function getCobranzasPorBusqueda2(Request $request)
{
    // Normalizar CodUsuario: tratar "null", "" o "ALL" como sin filtro
    $codUsuario = $request->get('CodUsuario');
    if ($codUsuario === 'null' || $codUsuario === '' || strtoupper($codUsuario) === 'ALL') {
        $codUsuario = null;
    }

    $supervisorsToQuery = [];
    if ($codUsuario && substr($codUsuario, 0, 1) === 'S') {
        if (isset($this->supervisor_general[$codUsuario])) {
            $supervisorsToQuery = $this->supervisor_general[$codUsuario];
        } else {
            $supervisorsToQuery = [$codUsuario];
        }
    }

    $query = DB::table('s010_CobranzaEncabezado as c')
        ->join('w004_zona as z', 'c.Usuario', '=', 'z.CodVendedor')
        ->select('c.*', 'z.Nombre');

    // Aplicar filtro por usuario/supervisor solo si viene algo válido
    if ($codUsuario) {
        if (substr($codUsuario, 0, 1) === 'V') {
            // usuario vendedor específico
            $query->where('z.CodVendedor', '=', $codUsuario);
        } else {
            // supervisor o gerente: si tenemos lista, whereIn; si no, usar el codUsuario como supervisor
            if (!empty($supervisorsToQuery)) {
                $query->whereIn('z.CodSupervisor', $supervisorsToQuery);
            } else {
                $query->where('z.CodSupervisor', '=', $codUsuario);
            }
        }
    } else {
        // codUsuario null => no filtramos por supervisor (traemos todo)
    }

    // Aplicar filtros de búsqueda
    $what = $request->get('whatToSearch');
    $q = $request->get('Busqueda');

    if ($what) {
        switch ($what) {
            case 'Documento':
                if ($q !== null && $q !== '') {
                    $query->where('c.Documento', 'LIKE', '%' . $q . '%');
                }
                break;

            case 'Cliente':
                if ($q !== null && $q !== '') {
                    $query->where('c.NombreCliente', 'LIKE', '%' . $q . '%');
                }
                break;

            case 'Fecha':
                // validar fechas antes de aplicar
                $fechaInicio = $request->get('fechaInicio');
                $fechaFin = $request->get('fechaFin');
                if ($fechaInicio && $fechaFin) {
                    // comparar por DATE en caso de tener hora
                    $query->whereBetween(DB::raw('DATE(c.FechaCobranza)'), [$fechaInicio, $fechaFin]);
                }
                break;

            case 'Vendedor':
                if ($q !== null && $q !== '') {
                    // si viene lista de códigos separados por coma -> whereIn
                    if (strpos($q, ',') !== false) {
                        $codes = array_filter(array_map('trim', explode(',', $q)));
                        if (!empty($codes)) {
                            $query->whereIn('z.CodVendedor', $codes);
                        }
                    }
                    // si parece un código V123 -> buscar por código exacto
                    elseif (preg_match('/^[Vv]?\d+$/', $q)) {
                        $query->where('z.CodVendedor', $q);
                    }
                    // si viene texto -> buscar por nombre (case-insensitive)
                    else {
                        $query->whereRaw('LOWER(z.Nombre) LIKE ?', ['%' . mb_strtolower($q) . '%']);
                    }
                }
                break;

            default:
                // sin filtro extra
                break;
        }
    }

    // Paginación y orden
    $perPage = intval($request->get('per_page', 15));
    $cobranzas = $query->orderBy('c.FechaCobranza', 'desc')->paginate($perPage);

    // Normalizar la respuesta con meta/links/data (consistente con tus otros endpoints)
    return response()->json([
        'meta' => [
            'current_page' => $cobranzas->currentPage(),
            'last_page' => $cobranzas->lastPage(),
            'per_page' => $cobranzas->perPage(),
            'total' => $cobranzas->total(),
        ],
        'links' => [
            'first' => $cobranzas->url(1),
            'last' => $cobranzas->url($cobranzas->lastPage()),
            'prev' => $cobranzas->previousPageUrl(),
            'next' => $cobranzas->nextPageUrl(),
        ],
        'data' => $cobranzas->items(),
    ]);
}

}

