<?php

namespace App\Http\Controllers\Api;

// Controllers
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseController as BaseController;

// Models
use App\Models\PedidoEncabezado;
use App\Models\PedidoDetalle;
use App\Models\FacturaDetalle;

// Query filters
use App\Filters\V1\Vendedor\PedidosFilter;
use App\Filters\V1\Vendedor\ProdPedidosFilter;

// Resources
use App\Http\Resources\Vendedor\Pedidos\PedidosResource;
use App\Http\Resources\Vendedor\Pedidos\PedidosCollection;
use App\Http\Resources\Vendedor\Pedidos\ProdPedidosCollection;
use App\Http\Resources\Vendedor\Pedidos\ProdPedidosResource;

// Http and support
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PedidosController extends BaseController
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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //funcion para obtener los encabezados de los pedidos del vendedor
    public function index(Request $request)
    {
        $filter = new PedidosFilter();
        $filterItems = $filter->transform($request);

        $pedidos = PedidoEncabezado::where($filterItems)->groupBy('Documento')->orderByDesc('fechayhora');
        $montoTotal = PedidoEncabezado::where($filterItems)->sum('Monto');

        return new PedidosCollection($pedidos->paginate(15)->appends($request->query())->appends($montoTotal));
    }
    //funcion para obtener pedidos para el usuario Gerente de sus vendedores
    public function getPedidosxGerente(Request $request)
    {
        $supervisorsToQuery = [];
        
        if ($request->CodSupervisor && substr($request->CodSupervisor, 0, 1) === 'S') {
            if (isset($this->supervisor_general[$request->CodSupervisor])) {
                $supervisorsToQuery = $this->supervisor_general[$request->CodSupervisor];
            } else {
                $supervisorsToQuery = [$request->CodSupervisor];
            }
        }

        $pedidosQuery = DB::table('e010_PedidoEncabezado as pe')
            ->join('w004_zona as z', 'pe.Vendedor', '=', 'z.CodVendedor')
            ->select('pe.*', 'z.Nombre');
        if (!empty($supervisorsToQuery)) {
            $pedidosQuery->whereIn('z.CodSupervisor', $supervisorsToQuery);
        } else {
            $pedidosQuery->where('z.CodSupervisor', '=', $request->CodSupervisor);
        }
        
        $pedidos = $pedidosQuery->orderBy('pe.fechayhora', 'DESC')
            ->paginate(15);

        return response()->json($pedidos);
    }

    //funcion para obtener pedidos para el usuario Gerencia
   public function getPedidosxCobranzasGerencia(Request $request)
{
    $supervisorsToQuery = [];

    // 1) Si nos pasan CodGerente -> obtenemos todos los supervisores asociados a ese gerente
    if ($request->filled('CodGerente')) {
        $supervisorsToQuery = DB::table('w004_zona')
            ->where('CodGerente', $request->CodGerente)
            ->pluck('CodSupervisor')
            ->unique()
            ->filter()
            ->values()
            ->toArray();
    }
    // 2) Si nos pasan CodSupervisor -> expandimos usando supervisor_general o usamos el propio,
    //     pero si viene 'ALL' lo interpretamos como "sin filtro" (no aplicamos condicion por supervisor)
    elseif ($request->filled('CodSupervisor')) {
        $cod = $request->CodSupervisor;

        if (strtoupper($cod) === 'ALL') {
            // no filtrar por supervisor -> dejamos supervisorsToQuery vacío (sin where)
            $supervisorsToQuery = [];
        } elseif (substr($cod, 0, 1) === 'S') {
            // si tenemos una expansión predefinida para este supervisor, úsala
            if (isset($this->supervisor_general[$cod]) && is_array($this->supervisor_general[$cod])) {
                $supervisorsToQuery = $this->supervisor_general[$cod];
            } else {
                $supervisorsToQuery = [$cod];
            }
        } else {
            // código que no comienza con 'S' -> lo tratamos como supervisor simple
            $supervisorsToQuery = [$cod];
        }
    }

    // Query base: unimos con w004_zona para traer nombre de vendedor
    $pedidosQuery = DB::table('e010_PedidoEncabezado as pe')
        ->join('w004_zona as z', 'pe.Vendedor', '=', 'z.CodVendedor')
        ->select('pe.*', 'z.Nombre as VendedorNombre', 'z.CodSupervisor');

    // Aplicamos filtro por supervisores solo si tenemos una lista explícita
    // (si supervisorsToQuery está vacío => no filtramos por supervisor, lo que corresponde a 'ALL')
    if (!empty($supervisorsToQuery)) {
        $pedidosQuery->whereIn('z.CodSupervisor', $supervisorsToQuery);
    }

    // Filtro por rango de fechas (si aplica)
    if ($request->filled('fechaInicio') && $request->filled('fechaFin')) {
        // comparamos por DATE(...) si 'fechayhora' incluye hora
        $pedidosQuery->whereBetween(DB::raw('DATE(pe.fechayhora)'), [$request->fechaInicio, $request->fechaFin]);
    }

    // Filtros de búsqueda (whatToSearch + Busqueda)
    if ($request->filled('whatToSearch') && $request->filled('Busqueda')) {
        $q = trim($request->Busqueda);
        switch ($request->whatToSearch) {
            case 'Documento':
                $pedidosQuery->where('pe.Documento', 'like', "%{$q}%");
                break;
            case 'Cliente':
                // ajusta el nombre del campo si tu tabla usa 'nombrecli' en lugar de 'NombreCliente'
                $pedidosQuery->where('pe.NombreCliente', 'like', "%{$q}%");
                break;
            case 'Vendedor':
                if ($q !== '') {
                    // varios códigos separados por coma -> whereIn
                    if (strpos($q, ',') !== false) {
                        $codes = array_filter(array_map('trim', explode(',', $q)));
                        if (!empty($codes)) {
                            $pedidosQuery->whereIn('z.CodVendedor', $codes);
                        }
                    }
                    // código único (ej. V123 o 123)
                    elseif (preg_match('/^[Vv]?\d+$/', $q)) {
                        $pedidosQuery->where('z.CodVendedor', $q);
                    }
                    // texto -> buscar por nombre (case-insensitive)
                    else {
                        $pedidosQuery->whereRaw('LOWER(z.Nombre) LIKE ?', ['%' . mb_strtolower($q) . '%']);
                    }
                }
                break;
        }
    }

    // Paginación y preservado de parámetros en los links
    $perPage = intval($request->get('per_page', 15));
    $pedidos = $pedidosQuery
        ->orderBy('pe.fechayhora', 'DESC')
        ->paginate($perPage)
        ->appends($request->all());

    return response()->json($pedidos);
}



    //funcion para obtener el monto y la cantidad de los productos del pedido
    public function getMontoProductosPedido(Request $request)
    {
        $subtotal = DB::table('e020_PedidoDetalle')
            ->select(DB::raw("SUM(Subtotal) as Subtotal"), DB::raw("COUNT(Codigo) as CantProductos "))
            ->where('Documento', '=', $request->Documento)
            ->get();
        // $montoTotal = PedidoEncabezado::where('Vendedor', $request->Vendedor)->sum('Monto');
        return response()->json($subtotal);
    }
    //funcion para obtener productos de un pedido
    public function getProductosPedido(Request $request)
    {
        $documento = $request->input('Documento');

    
    $productos = DB::table('e020_PedidoDetalle')
        ->select('Agencia', 'Documento', 'CodigoCliente', 'Codigo', 'Nombre', 'ListaPrecio', 'PrecioUnit', 'Cantidad', 'Subtotal', 'FechaHora', 'Descargado')
        ->where('Documento', $documento)
        ->paginate(50);

    return new ProdPedidosCollection($productos->appends($request->query()));
    }
    //funcion para obtener productos de un pedido PDF
    public function getProductosPedidoPDF(Request $request)
    {
        $filter = new ProdPedidosFilter();
        $filterItems = $filter->transform($request);
        $productos = PedidoDetalle::where($filterItems);

        return new ProdPedidosCollection($productos->paginate(500)->appends($request->query()));
    }
    //funcion para obtener busqueda por filtrado (Documento, Cliente, Fecha, Vendedor)
    public function getPedidosPorBusqueda(Request $request)
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

        $query = DB::table('e010_PedidoEncabezado as pe')
            ->join('w004_zona as z', 'pe.Vendedor', '=', 'z.CodVendedor')
            ->select('pe.*', 'z.Nombre');

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
                $query->where('pe.Documento', 'LIKE', '%' . $request->Busqueda . '%');
                break;

            case 'Cliente':
                $query->where('pe.NombreCliente', 'LIKE', '%' . $request->Busqueda . '%');
                break;

            case 'Fecha':
                $query->whereBetween('pe.fechayhora', [$request->fechaInicio, $request->fechaFin]);
                break;

            case 'Vendedor':
                $query->where('z.Nombre', 'LIKE', '%' . $request->Busqueda . '%');
                break;

            default:
                break;
        }
        $pedidos = $query->orderBy('pe.fechayhora', 'DESC')
            ->paginate(15);

        return response()->json($pedidos);
    }

    //funcion para obtener los productos que anteriormente el cliente ya compro
    public function getSugeridos(Request $request)
    {
        $ZonasVentas = $request->ZonasVenta;
        $empresas = $ZonasVentas === '001,003,004,CCS' ? ['001', '003', '004', 'CCS'] : ['001', '003', '004'];
        $condicion = '=';
        if ($request->marca == 'INGCO') {
            $condicion = '=';
        } else if ($request->marca == 'VERT') {
            $condicion = '<>';
        }
        $vista = $request->vistaFlag;
        $busqueda = $vista !== '1' ? 300 : 102;

        $sugeridos = FacturaDetalle::select('e110_FacturaDetalle.Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(a020_articulos.Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"), 'a020_articulos.Existencia','e110_FacturaDetalle.Cantidad',  'e110_FacturaDetalle.fechadoc as FechaHora', 'e110_FacturaDetalle.Cliente', 'a020_articulos.Precio1','a020_articulos.Precio2','a020_articulos.Precio3','a020_articulos.Precio4','a020_articulos.Precio5', 'a020_articulos.VentaMinima', 'a020_articulos.RutaImagen', 'a020_articulos.UnidEmpaque', 'a020_articulos.Grupo', 'a020_articulos.Subgrupo','a020_articulos.Empresa')
            ->join('a020_articulos', 'e110_FacturaDetalle.Codigo', '=', 'a020_articulos.Codigo')
            ->where('e110_FacturaDetalle.CodCliente', '=', $request->RifCliente)
            ->where('a020_articulos.Grupo', $condicion, '021')
            ->whereNotIn('a020_articulos.Grupo', ['032'])
            ->whereIn('a020_articulos.Empresa', $empresas)
            ->groupBy('e110_FacturaDetalle.Codigo')
            ->orderBy('a020_articulos.orden', 'ASC')
            ->orderBy('a020_articulos.Subgrupo', 'ASC')
            ->orderBy('a020_articulos.Nombre', 'ASC')
            ->paginate($busqueda);

        return response()->json($sugeridos);
    }
    //obtener pedidos sugeridos por fecha
    public function getSugeridosByFecha(Request $request)
    {
        $ZonasVentas = $request->ZonasVenta;
        $empresas = $ZonasVentas === '001,003,004,CCS' ? ['001', '003', '004', 'CCS'] : ['001', '003', '004'];
        $condicion = '=';
        if ($request->marca == 'INGCO') {
            $condicion = '=';
        } else if ($request->marca == 'VERT') {
            $condicion = '<>';
        }
        $vista = $request->vistaFlag;
        $busqueda = $vista !== '1' ? 300 : 102;

        $sugeridos = FacturaDetalle::select('e110_FacturaDetalle.Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(a020_articulos.Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre_nuevo"), 'e110_FacturaDetalle.Cantidad', 'a020_articulos.Existencia', 'e110_FacturaDetalle.fechadoc', 'e110_FacturaDetalle.Cliente', 'e110_FacturaDetalle.PrecioUnitario', 'a020_articulos.VentaMinima', 'a020_articulos.RutaImagen', 'a020_articulos.UnidEmpaque', 'a020_articulos.Grupo', 'a020_articulos.Subgrupo','a020_articulos.Empresa')
            ->join('a020_articulos', 'e110_FacturaDetalle.Codigo', '=', 'a020_articulos.Codigo')
            ->where('e110_FacturaDetalle.CodCliente', '=', $request->RifCliente)
            ->whereBetween('e110_FacturaDetalle.fechadoc', [$request->fechaInicio, $request->fechaFin])
            ->whereNotIn('a020_articulos.Grupo', ['032'])
            ->whereIn('a020_articulos.Empresa', $empresas)
            ->groupBy('e110_FacturaDetalle.Codigo')
            ->orderBy('e110_FacturaDetalle.Cantidad', 'DESC')
            // ->orderBy('e210_FacturaDetalle.fechadoc', 'ASC')
            // ->orderBy('a020_articulos.Nombre', 'ASC')
            // ->orderBy('a020_articulos.Subgrupo', 'ASC')
            ->paginate($busqueda);

        return response()->json($sugeridos);
    }
    //funcion para obtener los sugeridos en pdf
    public function getSugeridosPDF(Request $request)
    {
        $condicion = '=';
        if ($request->marca == 'INGCO') {
            $condicion = '=';
        } else if ($request->marca == 'VERT') {
            $condicion = '<>';
        }

        $sugeridos = FacturaDetalle::select('e110_FacturaDetalle.Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(a020_articulos.Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre_nuevo"), 'e110_FacturaDetalle.Cantidad', 'a020_articulos.Existencia', 'e110_FacturaDetalle.fechadoc', 'e110_FacturaDetalle.Cliente', 'a020_articulos.RutaImagen')
            ->join('a020_articulos', 'e110_FacturaDetalle.Codigo', '=', 'a020_articulos.Codigo')
            ->where('e110_FacturaDetalle.CodCliente', '=', $request->RifCliente)
            ->groupBy('e110_FacturaDetalle.Codigo')
            ->orderBy('e110_FacturaDetalle.Cantidad', 'DESC')
            ->paginate(500);

        return response()->json($sugeridos);
    }
    //funcion para obtener pedidos por supervisor
    public function getPedidosPorSupervisor(Request $request)
    {
        $pedidos = DB::table('e010_PedidoEncabezado as pe')
            ->join('w004_zona as z', 'pe.Vendedor', '=', 'z.CodVendedor')
            ->where('z.CodSupervisor', '=', $request->CodSupervisor)
            ->get();

        return response()->json($pedidos);
    }
}
