<?php

namespace App\Http\Controllers\Api;

// Controllers
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseController as BaseController;

// Models
use App\Models\Metas;
use App\Models\FacturaEncabezado;
use App\Models\FacturaDetalle;
use App\Models\VentasVendedores;
use App\Models\VentasClientes;
use App\Models\CustomUser;

// Query filters
use App\Filters\V1\MetasFilter;

// Resources
use App\Http\Resources\MetasResource;
use App\Http\Resources\MetasCollection;

// Http and supports
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends BaseController
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

    public function linkApk(Request $request)
    {
        $link = DB::table('w002_Link_APK')
            ->select('*')
            ->get();

        return response()->json($link);
    }

    public function getUsuario(Request $request)
    {
        $usuario = CustomUser::select('*')
            ->where('Usuario', '=', $request->Usuario)
            ->get();

        return response()->json($usuario);
    }

    public function getMetasPorVendedor(Request $request)
    {
        $filter = new MetasFilter();
        $filterItems = $filter->transform($request);

        $metaxVendedor = Metas::where($filterItems);

        return new MetasCollection($metaxVendedor->paginate(1)->appends($request->query()));
    }

    public function getTopVendedoresZona(Request $request)
    {
        $topVendedores = Metas::select('vendedor', 'total_vendido', 'Nombre', 'global')
            ->where('zona', '=', $request->zona)
            ->where('PeriodoInicio', '=', $request->PeriodoInicio)
            ->where('PeriodoFin', '=', $request->PeriodoFin)
            ->groupBy('vendedor')
            ->orderBy('total_vendido', 'desc')
            ->get();

        return response()->json($topVendedores);
    }

    public function getClientesAtendidos(Request $request)
    {
        $clientesAtendidos = FacturaEncabezado::select('codcliente', 'CodigoVendedor')
            ->where('CodigoVendedor', '=', $request->CodigoVendedor)
            ->whereBetween('FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->groupBy('codcliente')
            ->get();

        return response()->json($clientesAtendidos);
    }

    public function getTopVendedoresNacional(Request $request)
    {
        $topVendedores = Metas::select('w003_metas.vendedor', 'w003_metas.total_vendido', 'bv.Nombre')
            ->join('b010_vendedores as bv', 'bv.Codigo', '=', 'w003_metas.vendedor')
            ->where('w003_metas.PeriodoInicio', '=', $request->PeriodoInicio)
            ->where('w003_metas.PeriodoFin', '=', $request->PeriodoFin)
            ->groupBy('w003_metas.vendedor')
            ->orderBy('w003_metas.total_vendido', 'desc')
            ->get();

        return response()->json($topVendedores);
    }

    public function getInfoCobranza(Request $request)
    {
        $infoPagado = FacturaEncabezado::select(DB::raw('ROUND((sum(TotalFact) + sum(Abonado)), 2) as Pagado'))
            ->where('Estatus', '<>', '0')
            ->where('CodigoVendedor', '=', $request->CodigoVendedor)
            ->whereBetween('FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->get();

        $infoPendiente = FacturaEncabezado::select(DB::raw('ROUND(sum(TotalPend), 2) as Pendiente'))
            ->where('Estatus', '=', '0')
            ->whereRaw('DATEDIFF(NOW(), FechaVencimiento) > 0')
            ->where('CodigoVendedor', '=', $request->CodigoVendedor)
            ->whereBetween('FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->get();

        return response()->json(['Pagado' => $infoPagado, 'Pendiente' => $infoPendiente]);
    }

    public function getInfoCobranzaPorZona(Request $request)
    {
        $infoPagado = FacturaEncabezado::select(DB::raw('sum(e100_FacturaEncabezado.TotalFinal) as Cancelado'), 'm.zona')
            ->join('w003_metas as m', 'e100_FacturaEncabezado.CodigoVendedor', '=', 'm.vendedor')
            ->where('e100_FacturaEncabezado.Estatus', '=', '2')
            ->where('m.zona', '=', $request->zona)
            ->whereBetween('e100_FacturaEncabezado.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->groupBy('m.zona')
            ->get();

        $infoAbonado = FacturaEncabezado::select(DB::raw('sum(e100_FacturaEncabezado.TotalFinal) as Abonado'), 'm.zona')
            ->join('w003_metas as m', 'e100_FacturaEncabezado.CodigoVendedor', '=', 'm.vendedor')
            ->where('e100_FacturaEncabezado.Estatus', '=', '1')
            ->where('m.zona', '=', $request->zona)
            ->whereBetween('e100_FacturaEncabezado.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->groupBy('m.zona')
            ->get();

        $infoPendiente = FacturaEncabezado::select(DB::raw('sum(e100_FacturaEncabezado.TotalFinal) as Pendiente'), 'm.zona')
            ->join('w003_metas as m', 'e100_FacturaEncabezado.CodigoVendedor', '=', 'm.vendedor')
            ->where('e100_FacturaEncabezado.Estatus', '=', '0')
            ->where('m.zona', '=', $request->zona)
            ->whereBetween('e100_FacturaEncabezado.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->groupBy('m.zona')
            ->get();

        return response()->json(['Cancelado' => $infoPagado, 'Abonado' => $infoAbonado, 'Pendiente' => $infoPendiente]);
    }

    // Para obtener ventas de años pasados
    public function getVentasPasados(Request $request)
    {
        $ventasPasadas = VentasVendedores::select('vendedor', 'ventas', 'mes', 'year')
            ->where('vendedor', '=', $request->vendedor)
            ->where('year', '=', $request->year)
            ->get();

        return response()->json($ventasPasadas);
    }

    //funcion para obtener topclientes de los vendedores
    public function getTopClientesVendedor(Request $request)
    {
        $topclientes = VentasClientes::select('ventas', 'nombrecli')
            ->where('vendedor', '=', $request->vendedor)
            ->where('year', '=', $request->year)
            ->where('mes', '=', $request->mes)
            ->orderBy('ventas','desc')
            ->limit(10)
            ->get();

        return response()->json($topclientes);
    }

    //obtiene el porcentaje de compra de los top 100 productos por cliente de un vendedor
    public function getPorcentajeClientes(Request $request){
        $resultados = DB::table('b040_usuario AS a')
            ->join('a010_clientes AS b', 'a.CodVendedor', '=', 'b.Vendedor')
            ->select('b.porcentaje_vert','b.porcentaje_ingco','b.codigo as clientes', 'a.CodVendedor', 'b.Nombre')
            ->where('a.CodVendedor', $request->codVendedor)
            ->groupBy('b.codigo')
            ->orderBy('porcentaje_ingco', 'desc')
            ->orderBy('porcentaje_vert', 'desc')
            ->limit(20)
            ->get();
        return response()->json($resultados);
    }

    public function getPorcentajeClientesCompleto(Request $request){
        $resultados = DB::table('b040_usuario AS a')
            ->join('a010_clientes AS b', 'a.CodVendedor', '=', 'b.Vendedor')
            ->select('b.porcentaje_vert','b.porcentaje_ingco','b.codigo as clientes', 'a.CodVendedor', 'b.Nombre')
            ->where('a.CodVendedor', $request->codVendedor)
            ->groupBy('b.codigo')
            ->orderBy('porcentaje_ingco', 'desc')
            ->orderBy('porcentaje_vert', 'desc')
            ->paginate(600);
        return response()->json($resultados);
    }

    //obtiene el porcentaje de compra de los top 100 productos un vendedor
    public function getPorcentajeVendedor(Request $request){
        $resultados = DB::table('b040_usuario')
            ->select('porcen_vert','porcen_ingco')
            ->where('CodVendedor', $request->CodVendedor)
            ->first();
        return response()->json($resultados);
    }

     //obtiene el porcentaje de compra de los top 100 productos por vendedor de un supervisor
    public function getPorcentajeVendedores(Request $request)
    {
        $supervisorsToQuery = [];
        if ($request->codSupervisor && substr($request->codSupervisor, 0, 1) === 'S') {
            if (isset($this->supervisor_general[$request->codSupervisor])) {
                $supervisorsToQuery = $this->supervisor_general[$request->codSupervisor];
            } else {
                $supervisorsToQuery = [$request->codSupervisor];
            }
        }
        $query = DB::table('b040_usuario')
            ->select('porcen_vert as porcentaje_vert', 'porcen_ingco as porcentaje_ingco', 'CodVendedor', 'Nombre');
        if (!empty($supervisorsToQuery)) {
            $query->whereIn('CodSupervisor', $supervisorsToQuery);
        } else {
            $query->where('CodSupervisor', $request->codSupervisor);
        }
        $resultados = $query->orderBy('porcentaje_ingco', 'desc')->get();
        return response()->json($resultados);
    }

    //obtiene el porcentaje de compra de los top 100 productos un supervisor
    public function getPorcentajeSupervisor(Request $request){
        $resultados = DB::table('b040_usuario')
            ->select('porcen_vert','porcen_ingco')
            ->where('CodVendedor', $request->codSupervisor)
            ->first();
        return response()->json($resultados);
    }

    public function getPorcentajeSupervisores(Request $request){
        $resultados = DB::table('b040_usuario')
            ->selectRaw('ROUND(SUM(porcen_vert)/COUNT(*),2) as porcentaje_vert,ROUND(SUM(porcen_ingco)/COUNT(*),2) as porcentaje_ingco, CodVendedor,CodSupervisor, Nombre')
            ->where('CodVendedor','LIKE', "%S0%")
            ->where('Usuario','!=', "lfernandez")
            ->where('Usuario','!=', "frgarcia")
            ->where('Usuario','!=', "DeniseValencia")
            ->where('Usuario','!=', "adiaz")
            ->where('Usuario','!=', "lvillalobos")
            ->groupBy('CodVendedor')
            ->orderBy('CodVendedor')
            ->get();
        return response()->json($resultados);
    }

    public function getPorcentajeGerente(Request $request){
        $resultados = DB::table('b040_usuario')
            ->selectRaw('ROUND(SUM(porcen_vert)/COUNT(*),2) as porcen_vert,ROUND(SUM(porcen_ingco)/COUNT(*),2) as porcen_ingco, CodVendedor,CodSupervisor, Nombre')
            ->where('CodVendedor','LIKE', "%S0%")
            ->where('Usuario','!=', "lfernandez")
            ->where('Usuario','!=', "frgarcia")
            ->where('Usuario','!=', "DeniseValencia")
            ->where('Usuario','!=', "adiaz")
            ->where('Usuario','!=', "lvillalobos")
            ->first();
        return response()->json($resultados);
    }

    public function getVentasAnualesEnCurso(Request $request)
    {
        $field = "TotalNeto";
        $query = FacturaEncabezado::query();

        if ($request->CodigoVendedor && substr($request->CodigoVendedor, 0, 1) === 'V') {
            $query->where('CodigoVendedor', $request->CodigoVendedor);
            $field = "TotalNeto";
        } elseif ($request->CodigoVendedor && substr($request->CodigoVendedor, 0, 1) === 'S') {
            $query->join('w004_zona', 'e100_FacturaEncabezado.CodigoVendedor', '=', 'w004_zona.CodVendedor')
                ->where('w004_zona.CodSupervisor', $request->CodigoVendedor)
                ->groupBy('w004_zona.Sector');
            $field = "TotalNeto";
        } else {
            $query->whereNotIn('CodigoVendedor', ['V1', 'V2', 'T1', 'CCS1']);
            $field = "TotalNeto";
        }

        switch ($request->year) {
            case 2023:
                $startDate = "2023-01-01 00:00:00";
                $endDate = "2023-12-31 23:59:59";
                break;

            case 2024:
                $startDate = "2024-01-01 00:00:00";
                $endDate = "2024-12-31 23:59:59";
                break;

            case 2025:
                $startDate = "2025-01-01 00:00:00";
                $endDate = "2025-12-31 23:59:59";
                break;

            default:
                $startDate = "2025-01-01 00:00:00";
                $endDate = "2025-12-31 23:59:59";
                break;
        }

        $query->whereBetween('FechaDocumento', [$startDate, $endDate])
            ->select(DB::raw("
                   SUM(CASE WHEN MONTH(FechaDocumento) = 1 THEN " . $field . " ELSE 0 END) as Enero,
                   SUM(CASE WHEN MONTH(FechaDocumento) = 2 THEN " . $field . " ELSE 0 END) as Febrero,
                   SUM(CASE WHEN MONTH(FechaDocumento) = 3 THEN " . $field . " ELSE 0 END) as Marzo,
                   SUM(CASE WHEN MONTH(FechaDocumento) = 4 THEN " . $field . " ELSE 0 END) as Abril,
                   SUM(CASE WHEN MONTH(FechaDocumento) = 5 THEN " . $field . " ELSE 0 END) as Mayo,
                   SUM(CASE WHEN MONTH(FechaDocumento) = 6 THEN " . $field . " ELSE 0 END) as Junio,
                   SUM(CASE WHEN MONTH(FechaDocumento) = 7 THEN " . $field . " ELSE 0 END) as Julio,
                   SUM(CASE WHEN MONTH(FechaDocumento) = 8 THEN " . $field . " ELSE 0 END) as Agosto,
                   SUM(CASE WHEN MONTH(FechaDocumento) = 9 THEN " . $field . " ELSE 0 END) as Septiembre,
                   SUM(CASE WHEN MONTH(FechaDocumento) = 10 THEN " . $field . " ELSE 0 END) as Octubre,
                   SUM(CASE WHEN MONTH(FechaDocumento) = 11 THEN " . $field . " ELSE 0 END) as Noviembre,
                   SUM(CASE WHEN MONTH(FechaDocumento) = 12 THEN " . $field . " ELSE 0 END) as Diciembre
                "));

        $result = $query->get();

        return response()->json($result->first());
    }

     public function getVentasAnualesEnCursoSupervisores(Request $request)
    {
        // --- START: Logic to determine $supervisorsToQuery ---
        $supervisorsToQuery = [];
        // Only proceed if CodigoSupervisor is provided and starts with 'S'
        if ($request->CodigoSupervisor && substr($request->CodigoSupervisor, 0, 1) === 'S') {
            if (isset($this->supervisor_general[$request->CodigoSupervisor])) {
                // If it's a group, use the array of associated supervisors
                $supervisorsToQuery = $this->supervisor_general[$request->CodigoSupervisor];
            } else {
                // If it's not a group, put it in a single-element array
                $supervisorsToQuery = [$request->CodigoSupervisor];
            }
        }
        // --- END: Logic to determine $supervisorsToQuery ---

        $query = FacturaEncabezado::query();

        // --- Adaptación en esta sección para manejar grupos de supervisores ---
        if ($request->CodigoSupervisor && substr($request->CodigoSupervisor, 0, 1) === 'S') {
            $query->join('w004_zona', 'e100_FacturaEncabezado.CodigoVendedor', '=', 'w004_zona.CodVendedor')
                ->whereRaw('e100_FacturaEncabezado.CodigoVendedor NOT IN ("V1","V2","T1","CCS1")')
                ->groupBy('w004_zona.Sector');

            // Aplicamos el filtro de supervisor si $supervisorsToQuery no está vacío
            if (!empty($supervisorsToQuery)) {
                $query->whereIn('w004_zona.CodSupervisor', $supervisorsToQuery); // <-- ¡CAMBIO CLAVE AQUÍ!
            } else {
                // Si no es un grupo reconocido, pero es un supervisor individual,
                // mantenemos el filtro original.
                $query->where('w004_zona.CodSupervisor', $request->CodigoSupervisor);
            }
        }

        // --- Lógica para determinar el rango de fechas (sin cambios) ---
        $startDate = '';
        $endDate = '';

        switch ($request->year) {
            case 2023:
                $startDate = "2023-01-01 00:00:00";
                $endDate = "2023-12-31 23:59:59";
                break;

            case 2024:
                $startDate = "2024-01-01 00:00:00";
                $endDate = "2024-12-31 23:59:59";
                break;

            case 2025:
                $startDate = "2025-01-01 00:00:00";
                $endDate = "2025-12-31 23:59:59";
                break;

            default:
                // Current year logic, can be dynamic with Carbon if needed
                $startDate = "2025-01-01 00:00:00"; // Assuming 2025 is the default current year
                $endDate = "2025-12-31 23:59:59";
                break;
        }

        $query->whereBetween('FechaDocumento', [$startDate, $endDate])
            ->select(DB::raw("
                w004_zona.Sector,
                SUM(CASE WHEN MONTH(FechaDocumento) = 1 THEN TotalNeto ELSE 0 END) as Enero,
                SUM(CASE WHEN MONTH(FechaDocumento) = 2 THEN TotalNeto ELSE 0 END) as Febrero,
                SUM(CASE WHEN MONTH(FechaDocumento) = 3 THEN TotalNeto ELSE 0 END) as Marzo,
                SUM(CASE WHEN MONTH(FechaDocumento) = 4 THEN TotalNeto ELSE 0 END) as Abril,
                SUM(CASE WHEN MONTH(FechaDocumento) = 5 THEN TotalNeto ELSE 0 END) as Mayo,
                SUM(CASE WHEN MONTH(FechaDocumento) = 6 THEN TotalNeto ELSE 0 END) as Junio,
                SUM(CASE WHEN MONTH(FechaDocumento) = 7 THEN TotalNeto ELSE 0 END) as Julio,
                SUM(CASE WHEN MONTH(FechaDocumento) = 8 THEN TotalNeto ELSE 0 END) as Agosto,
                SUM(CASE WHEN MONTH(FechaDocumento) = 9 THEN TotalNeto ELSE 0 END) as Septiembre,
                SUM(CASE WHEN MONTH(FechaDocumento) = 10 THEN TotalNeto ELSE 0 END) as Octubre,
                SUM(CASE WHEN MONTH(FechaDocumento) = 11 THEN TotalNeto ELSE 0 END) as Noviembre,
                SUM(CASE WHEN MONTH(FechaDocumento) = 12 THEN TotalNeto ELSE 0 END) as Diciembre
            "));

        $result = $query->get();

        return response()->json($result);
    }

    public function getVentasNacionalesPorYear(Request $request)
    {
        $totalventasxyear = VentasClientes::select(DB::raw('sum(ventas) as total_vendido'), 'mes', 'year')
            ->where('year', '=', $request->year)
            ->groupBy('mes')
            ->get();

        return response()->json($totalventasxyear);
    }

    public function getVentasZonasPorYear(Request $request)
    {
        $totalventasxzona = VentasVendedores::select('m.zona', DB::raw('sum(w006_ventas_vendedores.ventas) as ventas_zona'), 'w006_ventas_vendedores.year')
            ->join('w003_metas as m', 'm.vendedor', '=', 'w006_ventas_vendedores.vendedor')
            ->where('w006_ventas_vendedores.year', '=', $request->year)
            ->groupBy('m.zona')
            ->orderBy('ventas_zona')
            ->get();

        return response()->json($totalventasxzona);
    }

    public function getVentasZonasMensual(Request $request)
    {
        $totalventasxzona = VentasVendedores::select('m.zona', 'w006_ventas_vendedores.mes', DB::raw('sum(w006_ventas_vendedores.ventas) as ventas_zona'))
            ->join('w003_metas as m', 'm.vendedor', '=', 'w006_ventas_vendedores.vendedor')
            ->where('w006_ventas_vendedores.year', '=', '2022')
            ->where('m.zona', '=', $request->zona)
            ->groupBy('w006_ventas_vendedores.mes')
            ->get();

        return response()->json($totalventasxzona);
    }

    public function getTopVendedoresYearZona(Request $request)
    {
        $topNacional = VentasVendedores::select('bv.Nombre', DB::raw('sum(w006_ventas_vendedores.ventas) as total_ventas'))
            ->join('b010_vendedores as bv', 'bv.Codigo', '=', 'w006_ventas_vendedores.vendedor')
            ->join('w003_metas as m', 'm.vendedor', '=', 'w006_ventas_vendedores.vendedor')
            ->where('w006_ventas_vendedores.year', '=', $request->year)
            ->where('m.zona', '=', $request->zona)
            ->groupBy('w006_ventas_vendedores.vendedor')
            ->orderBy('total_ventas', 'desc')
            ->get();

        return response()->json($topNacional);
    }

    public function getTopVendedoresPorYear(Request $request)
    {
        $topNacional = VentasVendedores::select('bv.Nombre', DB::raw('sum(w006_ventas_vendedores.ventas) as total_ventas'))
            ->join('b010_vendedores as bv', 'bv.Codigo', '=', 'w006_ventas_vendedores.vendedor')
            ->where('w006_ventas_vendedores.year', '=', $request->year)
            ->groupBy('w006_ventas_vendedores.vendedor')
            ->orderBy('total_ventas', 'desc')
            ->get();

        return response()->json($topNacional);
    }

    public function getListaMetas(Request $request)
{
    // Determinar arreglo de supervisores a consultar
    $supervisorsToQuery = [];
    if ($request->CodSupervisor && substr($request->CodSupervisor, 0, 1) === 'S') {
        if (isset($this->supervisor_general[$request->CodSupervisor])) {
            // Es un "supervisor general" que agrupa varios supervisores
            $supervisorsToQuery = $this->supervisor_general[$request->CodSupervisor];
        } else {
            // Es un supervisor individual
            $supervisorsToQuery = [$request->CodSupervisor];
        }
    }

    // Base del query
    $query = DB::table('w003_metas as m')
        ->join('w004_zona as z', 'z.CodVendedor', '=', 'm.vendedor')
        ->select(
            'z.CodVendedor',
            'm.nombre',
            'z.Sector',
            'm.VentasVert',
            'm.VentasIngco',
            'm.total_vendido',
            'm.global'
        );

    // Filtro opcional por zona si viene
    if ($request->zona) {
        $query->where('z.Sector', '=', $request->zona);
    }

    // Aplicar filtro de supervisor (whereIn si es grupo)
    if (!empty($supervisorsToQuery)) {
        $query->whereIn('z.CodSupervisor', $supervisorsToQuery);
    } elseif ($request->CodSupervisor) {
        // En caso de que llegue algo que no empieza con 'S' o no esté en el map,
        // mantenemos la comparación simple (comportamiento previo).
        $query->where('z.CodSupervisor', '=', $request->CodSupervisor);
    }

    $lista = $query->orderBy('z.Sector', 'DESC')->get();

    return response()->json($lista);
}

    public function ActualizarMetas(Request $request)
    {
        $metas = Metas::where('vendedor', $request->vendedor)
            ->update(['global' => $request->metaGlobal, 'vert' => ($request->metaGlobal / 2), 'ingco' => ($request->metaGlobal / 2), 'PeriodoInicio' => $request->PeriodoInicio, 'PeriodoFin' => $request->PeriodoFin]);

        return response()->json($metas);
    }

    // CONSULTAS PARA EL MODULO DEL SUPERVISOR
   public function getVentasZona(Request $request)
    {
        $supervisorsToQuery = [];
        // Solo necesitamos esta lógica si hay un CodSupervisor en la solicitud y empieza con 'S'
        // y NO es 'HABOULMOUNA' (ya que 'HABOULMOUNA' tiene su propia lógica)
        if ($request->CodSupervisor && substr($request->CodSupervisor, 0, 1) === 'S' && $request->CodSupervisor !== 'HABOULMOUNA') {
            if (isset($this->supervisor_general[$request->CodSupervisor])) {
                $supervisorsToQuery = $this->supervisor_general[$request->CodSupervisor];
            } else {
                $supervisorsToQuery = [$request->CodSupervisor];
            }
        }
        // La variable que contendrá el resultado final de la consulta
        $ventasxZona = null;

        if ($request->CodSupervisor === 'HABOULMOUNA') {
            if ($request->Zona) {
                $ventasxZona = DB::table('w003_metas as m')
                    ->join('w004_zona as z', 'z.CodVendedor', '=', 'm.vendedor')
                    ->select('z.Sector', DB::raw('sum(m.total_vendido) as total_vendido'), DB::raw('sum(m.global) as meta_zona'))
                    ->where('z.Sector', '=', $request->Zona)
                    ->whereRaw('m.vendedor NOT IN ("V1", "V2", "T1","CCS1")')
                    ->groupBy('z.Sector')
                    ->orderBy('total_vendido', 'DESC')
                    ->get();
            } else {
                $ventasxZona = DB::table('w003_metas as m')
                    ->join('w004_zona as z', 'z.CodVendedor', '=', 'm.vendedor')
                    ->select('z.Sector', DB::raw('sum(m.total_vendido) as total_vendido'), DB::raw('sum(m.global) as meta_zona'))
                    ->whereRaw('m.vendedor NOT IN ("V1", "V2", "T1","CCS1")')
                    ->groupBy('z.Sector')
                    ->orderBy('total_vendido', 'DESC')
                    ->get();
            }
        } else {
            // Este es el bloque donde aplicaremos la lógica de $supervisorsToQuery
            $query = DB::table('w003_metas as m')
                ->join('w004_zona as z', 'z.CodVendedor', '=', 'm.vendedor')
                ->select('z.Sector', DB::raw('sum(m.total_vendido) as total_vendido'), DB::raw('sum(m.global) as meta_zona'))
                ->whereRaw('m.vendedor NOT IN ("V1", "V2", "T1","CCS1")'); // Ya incluye el filtro de vendedores excluidos

            // Aplicamos el filtro de supervisor si $supervisorsToQuery no está vacío
            if (!empty($supervisorsToQuery)) {
                $query->whereIn('z.CodSupervisor', $supervisorsToQuery); // <-- ¡CAMBIO CLAVE AQUÍ!
            }
            $ventasxZona = $query->groupBy('z.Sector')
                ->orderBy('total_vendido', 'DESC')
                ->get();
        }

        return response()->json($ventasxZona);
    }

    public function getZonasSupervisor(Request $request)
    {
        if ($request->CodSupervisor === 'HABOULMOUNA' || $request->CodSupervisor === 'COBRANZASBG') {
            $zonasSupervisor = DB::table('w004_zona')
                ->select('Sector', 'CodSupervisor')
                ->groupBy('Sector')
                ->orderBy('Sector', 'ASC')
                ->get();
        } else {
            $zonasSupervisor = DB::table('w004_zona')
                ->select('Sector', 'CodSupervisor')
                ->where('CodSupervisor', '=', $request->CodSupervisor)
                ->groupBy('Sector')
                ->orderBy('Sector', 'ASC')
                ->get();
        }

        return response()->json($zonasSupervisor);
    }

     public function getTopVendedoresSupervisor(Request $request)
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

        $topVendedoresQuery = DB::table('w003_metas as m')
            ->join('w004_zona as z', 'z.CodVendedor', '=', 'm.vendedor')
            ->select('z.Sector', 'm.vendedor', 'm.total_vendido as ventas_vendedor', 'z.Nombre', 'm.global');

        // Apply the supervisor filter using whereIn
        if (!empty($supervisorsToQuery)) {
            $topVendedoresQuery->whereIn('z.CodSupervisor', $supervisorsToQuery); // <-- KEY CHANGE HERE!
        } else {
            // Fallback for cases where $supervisorsToQuery might be empty (e.g., $request->CodSupervisor was null)
            $topVendedoresQuery->where('z.CodSupervisor', '=', $requestCodSupervisor);
        }

        $topVendedores = $topVendedoresQuery
            ->whereRaw("z.CodVendedor NOT IN ('V1','V2','T1','CCS1')")
            ->where('z.Sector', '=', $request->Sector)
            ->orderBy('ventas_vendedor', 'DESC')
            ->get();

        return response()->json($topVendedores);
    }

    public function getCobranzasVendedorSupervisor(Request $request)
    {
        $infoPagado = DB::table('e100_FacturaEncabezado as fe')
            ->join('w004_zona as z', 'z.CodVendedor', '=', 'fe.CodigoVendedor')
            ->select('z.CodVendedor', DB::raw('(SUM(CASE WHEN fe.Estatus = 2 THEN fe.TotalFact ELSE 0 END) + SUM(CASE WHEN fe.Estatus = 1 THEN fe.Abonado ELSE 0 END)) as Pagado'))
            ->where('z.CodSupervisor', '=', $request->CodSupervisor)
            ->where('fe.CodigoVendedor', '=', $request->CodigoVendedor)
            ->whereBetween('fe.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->get();

        $infoPendiente = DB::table('e100_FacturaEncabezado as fe')
            ->join('w004_zona as z', 'z.CodVendedor', '=', 'fe.CodigoVendedor')
            ->select('z.CodVendedor', DB::raw('sum(fe.TotalPend) as Pendiente'))
            ->where('fe.Estatus', '=', '0')
            ->whereRaw('DATEDIFF(NOW(), fe.FechaVencimiento) > 0')
            ->where('z.CodSupervisor', '=', $request->CodSupervisor)
            ->where('fe.CodigoVendedor', '=', $request->CodigoVendedor)
            ->whereBetween('fe.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->get();

        return response()->json(['Pagado' => $infoPagado, 'Pendiente' => $infoPendiente]);
    }

    public function getCobranzasVendedorSupervisorGeneral(Request $request)
    {
        $infoPagado = DB::table('e100_FacturaEncabezado as fe')
            ->join('w004_zona as z', 'z.CodVendedor', '=', 'fe.CodigoVendedor')
            ->select('z.CodVendedor', DB::raw('ROUND((SUM(CASE WHEN fe.Estatus = 2 THEN fe.TotalFact ELSE 0 END) + SUM(CASE WHEN fe.Estatus = 1 THEN fe.Abonado ELSE 0 END))) as Pagado'))
            ->where('z.CodSupervisor', '=', $request->CodSupervisor)
            ->whereBetween('fe.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->get();

        $infoPendiente = DB::table('e100_FacturaEncabezado as fe')
            ->join('w004_zona as z', 'z.CodVendedor', '=', 'fe.CodigoVendedor')
            ->select('z.CodVendedor', DB::raw('ROUND(SUM(fe.TotalPend)) as Pendiente'))
            ->where('fe.Estatus', '=', '0')
            ->whereRaw('DATEDIFF(NOW(), fe.FechaVencimiento) > 0')
            ->where('z.CodSupervisor', '=', $request->CodSupervisor)
            ->whereBetween('fe.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->get();

        return response()->json(['Pagado' => $infoPagado, 'Pendiente' => $infoPendiente]);
    }

    public function getCobranzasSupervisor(Request $request)
    {
        // 1. Lógica para determinar el año a consultar (actual o anterior si no es el actual)
        $currentYear = Carbon::now()->year; // Obtiene el año actual (ej. 2025)

        // Determina el año objetivo: si el año de la request es el actual, usa el actual.
        // Si no, usa el año anterior.
        $targetYear = ($request->year == $currentYear) ? $currentYear : ($currentYear - 1);
        // Si siempre debe ser 2025 o 2024, puedes mantener la lógica original:
        // $targetYear = ($request->year == 2025) ? 2025 : 2024;


        // 2. Manejo de Fechas con Carbon para el año objetivo
        $startDate = Carbon::create($targetYear, 1, 1)->startOfDay()->toDateTimeString(); // Enero 1 del año objetivo
        $endDate = Carbon::create($targetYear, 12, 31)->endOfDay()->toDateTimeString(); // Diciembre 31 del año objetivo

        // 3. Lógica para determinar $supervisorsToQuery
        $supervisorsToQuery = [];
        if ($request->CodSupervisor && substr($request->CodSupervisor, 0, 1) === 'S') {
            if (isset($this->supervisor_general[$request->CodSupervisor])) {
                $supervisorsToQuery = $this->supervisor_general[$request->CodSupervisor];
            } else {
                $supervisorsToQuery = [$request->CodSupervisor];
            }
        }

        // Convertir el array de supervisores a una cadena para la cláusula IN de SQL
        $supervisorInClause = !empty($supervisorsToQuery)
            ? "'" . implode("','", $supervisorsToQuery) . "'"
            : "'NO_MATCHING_SUPERVISOR_CODE'"; // Valor seguro por si $supervisorsToQuery está vacío

        // 4. Construcción de las subconsultas con la condición de supervisor dinámica
        // y las fechas dinámicas.

        // Subconsulta para Pagado (AS p)
        $pagadoSubquery = '
            SELECT
                CASE
                    WHEN MONTH(fe.FechaDocumento) = 1 THEN "Enero"
                    WHEN MONTH(fe.FechaDocumento) = 2 THEN "Febrero"
                    WHEN MONTH(fe.FechaDocumento) = 3 THEN "Marzo"
                    WHEN MONTH(fe.FechaDocumento) = 4 THEN "Abril"
                    WHEN MONTH(fe.FechaDocumento) = 5 THEN "Mayo"
                    WHEN MONTH(fe.FechaDocumento) = 6 THEN "Junio"
                    WHEN MONTH(fe.FechaDocumento) = 7 THEN "Julio"
                    WHEN MONTH(fe.FechaDocumento) = 8 THEN "Agosto"
                    WHEN MONTH(fe.FechaDocumento) = 9 THEN "Septiembre"
                    WHEN MONTH(fe.FechaDocumento) = 10 THEN "Octubre"
                    WHEN MONTH(fe.FechaDocumento) = 11 THEN "Noviembre"
                    WHEN MONTH(fe.FechaDocumento) = 12 THEN "Diciembre"
                END AS Mes,
                YEAR(fe.FechaDocumento) AS Año,
                ROUND(SUM(CASE WHEN fe.estatus = 2 THEN fe.TotalFact ELSE 0 END
                          + CASE WHEN fe.Estatus = 1 THEN fe.Abonado ELSE 0 END)) AS Pagado
            FROM
                e100_FacturaEncabezado AS fe
            INNER JOIN
                w004_zona AS z ON z.CodVendedor = fe.CodigoVendedor
            WHERE
                z.CodSupervisor IN (' . $supervisorInClause . ')
                AND fe.FechaDocumento BETWEEN "' . $startDate . '" AND "' . $endDate . '"
            GROUP BY
                MONTH(fe.FechaDocumento)
        ';

        // Subconsulta para Pendiente (AS q)
        $pendienteSubquery = '
            SELECT
                CASE
                    WHEN MONTH(fe.FechaDocumento) = 1 THEN "Enero"
                    WHEN MONTH(fe.FechaDocumento) = 2 THEN "Febrero"
                    WHEN MONTH(fe.FechaDocumento) = 3 THEN "Marzo"
                    WHEN MONTH(fe.FechaDocumento) = 4 THEN "Abril"
                    WHEN MONTH(fe.FechaDocumento) = 5 THEN "Mayo"
                    WHEN MONTH(fe.FechaDocumento) = 6 THEN "Junio"
                    WHEN MONTH(fe.FechaDocumento) = 7 THEN "Julio"
                    WHEN MONTH(fe.FechaDocumento) = 8 THEN "Agosto"
                    WHEN MONTH(fe.FechaDocumento) = 9 THEN "Septiembre"
                    WHEN MONTH(fe.FechaDocumento) = 10 THEN "Octubre"
                    WHEN MONTH(fe.FechaDocumento) = 11 THEN "Noviembre"
                    WHEN MONTH(fe.FechaDocumento) = 12 THEN "Diciembre"
                END AS Mes,
                YEAR(fe.FechaDocumento) AS Año,
                ROUND(SUM(fe.TotalPend)) AS Pendiente
            FROM
                e100_FacturaEncabezado AS fe
            INNER JOIN
                w004_zona AS z ON z.CodVendedor = fe.CodigoVendedor
            WHERE
                fe.Estatus <> 2
                AND DATEDIFF(CURDATE(), fe.FechaVencimiento) > 0
                AND z.CodSupervisor IN (' . $supervisorInClause . ')
                AND fe.FechaDocumento BETWEEN "' . $startDate . '" AND "' . $endDate . '"
            GROUP BY
                MONTH(fe.FechaDocumento)
        ';

        // La consulta principal ahora usa las subconsultas construidas
        $query = DB::table(DB::raw('(' . $pagadoSubquery . ') AS p'))
            ->leftJoin(DB::raw('(' . $pendienteSubquery . ') AS q'), function ($join) {
                $join->on('p.Mes', '=', 'q.Mes')
                    ->on('p.Año', '=', 'q.Año');
            })
            ->selectRaw('COALESCE(p.Mes, q.Mes) AS Mes, COALESCE(p.Año, q.Año) AS Año, IFNULL(p.Pagado, 0) AS Pagado, IFNULL(q.Pendiente, 0) AS Pendiente')
            ->get();

        return response()->json($query);
    }

    public function getCobranzasSupervisor2(Request $request)
    {
        // 1. Determinar año objetivo (igual que en getCobranzasSupervisor)
        $currentYear = Carbon::now()->year;
        $targetYear  = ($request->year == $currentYear) ? $currentYear : ($currentYear - 1);

        // 2. Fechas de inicio y fin del año objetivo
        $startDate = Carbon::create($targetYear, 1, 1)->startOfDay()->toDateTimeString();
        $endDate   = Carbon::create($targetYear, 12, 31)->endOfDay()->toDateTimeString();

        // 3. Subconsulta de facturado (Pagado) por mes y supervisor
        $pagadoSubquery = "
            SELECT
                z.CodSupervisor as Supervisor,
                MONTH(fe.FechaDocumento) as MesNum,
                CASE
                    WHEN MONTH(fe.FechaDocumento) = 1 THEN 'Enero'
                    WHEN MONTH(fe.FechaDocumento) = 2 THEN 'Febrero'
                    WHEN MONTH(fe.FechaDocumento) = 3 THEN 'Marzo'
                    WHEN MONTH(fe.FechaDocumento) = 4 THEN 'Abril'
                    WHEN MONTH(fe.FechaDocumento) = 5 THEN 'Mayo'
                    WHEN MONTH(fe.FechaDocumento) = 6 THEN 'Junio'
                    WHEN MONTH(fe.FechaDocumento) = 7 THEN 'Julio'
                    WHEN MONTH(fe.FechaDocumento) = 8 THEN 'Agosto'
                    WHEN MONTH(fe.FechaDocumento) = 9 THEN 'Septiembre'
                    WHEN MONTH(fe.FechaDocumento) = 10 THEN 'Octubre'
                    WHEN MONTH(fe.FechaDocumento) = 11 THEN 'Noviembre'
                    WHEN MONTH(fe.FechaDocumento) = 12 THEN 'Diciembre'
                END AS Mes,
                ROUND(
                    SUM(CASE WHEN fe.Estatus = 2 THEN fe.TotalFact ELSE 0 END)
                    + SUM(CASE WHEN fe.Estatus = 1 THEN fe.Abonado ELSE 0 END)
                ) AS Pagado
            FROM e100_FacturaEncabezado fe
            JOIN w004_zona z ON fe.CodigoVendedor = z.CodVendedor
            WHERE fe.FechaDocumento BETWEEN '" . $startDate . "' AND '" . $endDate . "'
            GROUP BY z.CodSupervisor, MONTH(fe.FechaDocumento)
        ";

        // 4. Subconsulta de pendiente por mes y supervisor
        $pendienteSubquery = "
            SELECT
                z.CodSupervisor as Supervisor,
                MONTH(fe.FechaDocumento) as MesNum,
                CASE
                    WHEN MONTH(fe.FechaDocumento) = 1 THEN 'Enero'
                    WHEN MONTH(fe.FechaDocumento) = 2 THEN 'Febrero'
                    WHEN MONTH(fe.FechaDocumento) = 3 THEN 'Marzo'
                    WHEN MONTH(fe.FechaDocumento) = 4 THEN 'Abril'
                    WHEN MONTH(fe.FechaDocumento) = 5 THEN 'Mayo'
                    WHEN MONTH(fe.FechaDocumento) = 6 THEN 'Junio'
                    WHEN MONTH(fe.FechaDocumento) = 7 THEN 'Julio'
                    WHEN MONTH(fe.FechaDocumento) = 8 THEN 'Agosto'
                    WHEN MONTH(fe.FechaDocumento) = 9 THEN 'Septiembre'
                    WHEN MONTH(fe.FechaDocumento) = 10 THEN 'Octubre'
                    WHEN MONTH(fe.FechaDocumento) = 11 THEN 'Noviembre'
                    WHEN MONTH(fe.FechaDocumento) = 12 THEN 'Diciembre'
                END AS Mes,
                ROUND(SUM(fe.TotalPend)) AS Pendiente
            FROM e100_FacturaEncabezado fe
            JOIN w004_zona z ON fe.CodigoVendedor = z.CodVendedor
            WHERE fe.Estatus <> 2
              AND DATEDIFF(CURDATE(), fe.FechaVencimiento) > 0
              AND fe.FechaDocumento BETWEEN '" . $startDate . "' AND '" . $endDate . "'
            GROUP BY z.CodSupervisor, MONTH(fe.FechaDocumento)
        ";

        // 5. Unión de ambas subconsultas
        $query = DB::table(DB::raw('(' . $pagadoSubquery . ') as p'))
            ->join(DB::raw('(' . $pendienteSubquery . ') as q'), function ($join) {
                $join->on('p.Supervisor', '=', 'q.Supervisor')
                     ->on('p.MesNum', '=', 'q.MesNum');
            })
            ->selectRaw(
                'p.Supervisor, p.MesNum, p.Mes, '
                . 'COALESCE(p.Pagado,0) as Pagado, COALESCE(q.Pendiente,0) as Pendiente'
            )
            ->orderBy('p.Supervisor')
            ->orderBy('p.MesNum')
            ->get();

        return response()->json($query);
    }


    public function getDetalleCobranzasSupervisor(Request $request)
    {
        $query = DB::table('e100_FacturaEncabezado as ef')
            ->join('w004_zona as z', 'ef.CodigoVendedor', '=', 'z.CodVendedor')
            ->select(
                'z.Sector',
                'ef.documento',
                'ef.nombrecli',
                'z.CodVendedor',
                'z.Nombre',
                'ef.FechaDocumento',
                'ef.FechaVencimiento',
                DB::raw('DATEDIFF(CURDATE(), ef.FechaVencimiento) AS DiasVencidos'),
                'ef.DiasCredito',
                'ef.TotalFact',
                'ef.TotalPend',
                'ef.Estatus'
            )
            ->whereBetween('ef.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->whereRaw('DATEDIFF(CURDATE(), ef.FechaVencimiento) >= 0')
            ->where('ef.Estatus', '<>', 2)
            ->where('z.CodSupervisor', '=', $request->CodSupervisor)
            ->orderBy('z.Nombre')
            ->orderByDesc('ef.FechaDocumento')
            ->get();

        return response()->json($query);
    }

            public function getDetalleCobranzasSupervisor2(Request $request)
        {
            // 1) Construyo la lista de supervisores a consultar (padre + subs)
            $supervisorsToQuery = [];
            if ($request->CodSupervisor && substr($request->CodSupervisor, 0, 1) === 'S') {
                if (isset($this->supervisor_general[$request->CodSupervisor])) {
                    $supervisorsToQuery = $this->supervisor_general[$request->CodSupervisor];
                } else {
                    $supervisorsToQuery = [$request->CodSupervisor];
                }
            }

            // 2) Parto la consulta base
            $query = DB::table('e100_FacturaEncabezado as ef')
                ->join('w004_zona as z', 'ef.CodigoVendedor', '=', 'z.CodVendedor')
                ->select(
                    'z.Sector',
                    'ef.documento',
                    'ef.nombrecli',
                    'z.CodVendedor',
                    'z.Nombre',
                    'ef.FechaDocumento',
                    'ef.FechaVencimiento',
                    DB::raw('DATEDIFF(CURDATE(), ef.FechaVencimiento) AS DiasVencidos'),
                    'ef.DiasCredito',
                    'ef.TotalFact',
                    'ef.TotalPend',
                    'ef.Estatus'
                )
                ->whereBetween('ef.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
                ->whereRaw('DATEDIFF(CURDATE(), ef.FechaVencimiento) >= 0')
                ->where('ef.Estatus', '<>', 2);

            // 3) Aplico el filtro de supervisor (IN) si hay alguno, o no filtro para traer todos
            if (!empty($supervisorsToQuery)) {
                $query->whereIn('z.CodSupervisor', $supervisorsToQuery);
            }
            // else: si no se envía CodSupervisor válido, devuelve detalle de todas las zonas

            // 4) Orden y ejecución
            $result = $query
                ->orderBy('z.Nombre')
                ->orderByDesc('ef.FechaDocumento')
                ->get();

            return response()->json($result);
        }


    public function getVendedoresXSupervisor(Request $request)
    {
        $supervisorsToQuery = [];
        if ($request->CodSupervisor && substr($request->CodSupervisor, 0, 1) === 'S') {
            if (isset($this->supervisor_general[$request->CodSupervisor])) {
                $supervisorsToQuery = $this->supervisor_general[$request->CodSupervisor];
            } else {
                $supervisorsToQuery = [$request->CodSupervisor];
            }
        }

        $query = DB::table('b040_usuario')->select('*');

        if (!empty($supervisorsToQuery)) {
            $query->whereIn('CodSupervisor', $supervisorsToQuery);
        } else {
            $query->where('CodSupervisor', '=', $request->CodSupervisor);
        }

        $listaVendedores = $query->get();

        return response()->json($listaVendedores);
    }

    public function getCorteSemanalxZona(Request $request)
{
    // 1) Calcular supervisor padre + subs
    $codSup = $request->CodSupervisor;
    $supervisorsToQuery = [];
    if ($codSup && substr($codSup, 0, 1) === 'S') {
        if (isset($this->supervisor_general[$codSup])) {
            $supervisorsToQuery = $this->supervisor_general[$codSup];
        } else {
            $supervisorsToQuery = [$codSup];
        }
    }

    // 2) Armamos el whereRaw dinámico
    $excludes = "'V1','V2','T1','CCS1'";
    if ($codSup && substr($codSup, 0, 1) === 'S' && $request->otrasZonas !== 'si') {
        // Supervisor con sus subs
        $inList = implode("','", $supervisorsToQuery);
        $whereParam = "z.CodSupervisor IN ('{$inList}') AND z.CodVendedor NOT IN ({$excludes})";
    } else {
        // Todas las zonas
        $whereParam = "z.CodVendedor NOT IN ({$excludes})";
    }

    // 3) Tus tres queries quedan igual, usando whereRaw($whereParam)
    $beforeLastWeek = DB::table('w004_zona AS z')
        ->leftJoin(DB::raw('(SELECT fd.CodigoVendedor, ROUND(SUM(fd.Subtotal)) AS ventasSemanal
                             FROM e110_FacturaDetalle AS fd
                             WHERE fd.fechadoc BETWEEN
                               DATE_SUB(DATE(NOW()), INTERVAL WEEKDAY(NOW()) + 14 DAY)
                             AND DATE_SUB(DATE(NOW()), INTERVAL WEEKDAY(NOW()) + 8 DAY)
                             GROUP BY fd.CodigoVendedor) AS s'),
            fn($join) => $join->on('z.CodVendedor', '=', 's.CodigoVendedor')
        )
        ->select('z.Sector', DB::raw('IFNULL(SUM(s.ventasSemanal), 0) AS ventasSemanal'))
        ->whereRaw($whereParam)
        ->groupBy('z.Sector')
        ->orderBy('z.Sector', 'ASC')
        ->get();

    $lastWeek = DB::table('w004_zona AS z')
        ->leftJoin(DB::raw('(SELECT fd.CodigoVendedor, ROUND(SUM(fd.Subtotal)) AS ventasSemanal
                             FROM e110_FacturaDetalle AS fd
                             WHERE fd.fechadoc BETWEEN
                               DATE_SUB(DATE(NOW()), INTERVAL WEEKDAY(NOW()) + 7 DAY)
                             AND DATE_SUB(DATE(NOW()), INTERVAL WEEKDAY(NOW()) + 1 DAY)
                             GROUP BY fd.CodigoVendedor) AS s'),
            fn($join) => $join->on('z.CodVendedor', '=', 's.CodigoVendedor')
        )
        ->select('z.Sector', DB::raw('IFNULL(SUM(s.ventasSemanal), 0) AS ventasSemanal'))
        ->whereRaw($whereParam)
        ->groupBy('z.Sector')
        ->orderBy('z.Sector', 'ASC')
        ->get();

    $currentWeek = DB::table('w004_zona AS z')
        ->leftJoin(DB::raw('(SELECT fd.CodigoVendedor, ROUND(SUM(fd.Subtotal)) AS ventasSemanal
                             FROM e110_FacturaDetalle AS fd
                             WHERE fd.fechadoc BETWEEN
                               DATE_SUB(DATE(NOW()), INTERVAL WEEKDAY(NOW()) DAY)
                             AND DATE_ADD(DATE(NOW()), INTERVAL 6 - WEEKDAY(NOW()) DAY)
                             GROUP BY fd.CodigoVendedor) AS s'),
            fn($join) => $join->on('z.CodVendedor', '=', 's.CodigoVendedor')
        )
        ->select('z.Sector', DB::raw('IFNULL(SUM(s.ventasSemanal), 0) AS ventasSemanal'))
        ->whereRaw($whereParam)
        ->groupBy('z.Sector')
        ->orderBy('z.Sector', 'ASC')
        ->get();

    return response()->json([
        "semanaAntepasada" => $beforeLastWeek,
        "semanaPasada"    => $lastWeek,
        "semanaActual"    => $currentWeek,
    ]);
}


   public function getClientesAtendidosxZona(Request $request)
    {
        $now = Carbon::now();
        $startOfMonth = null;
        $endOfMonth = null;

        // Date Range Determination: Use Carbon for consistency if not already
        if (!empty($request->fechaInicio) && !empty($request->fechaFin)) {
            // Ensure these are proper date strings or Carbon objects if they come from frontend
            $startOfMonth = $request->fechaInicio;
            $endOfMonth = $request->fechaFin;
        } else {
            $startOfMonth = $now->copy()->startOfMonth();
            $endOfMonth = $now->copy()->endOfMonth();
        }

        // --- START: Logic to determine $supervisorsToQuery ---
        $supervisorsToQuery = [];
        $requestCodSupervisor = $request->CodSupervisor;

        if ($requestCodSupervisor && isset($this->supervisor_general[$requestCodSupervisor])) {
            $supervisorsToQuery = $this->supervisor_general[$requestCodSupervisor];
        } else {
            $supervisorsToQuery = [$requestCodSupervisor];
        }
        // --- END: Logic to determine $supervisorsToQuery ---

        $facturados = null; // Initialize the query result variable

        // Base query components that are common to the main branches
        $baseQuery = DB::table('e100_FacturaEncabezado as fd')
            ->join('w004_zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->whereBetween('fd.FechaDocumento', [$startOfMonth, $endOfMonth]);

        // --- Main Conditional Logic ---
        if ($request->CodSupervisor === 'HABOULMOUNA') {
            $query = $baseQuery->clone(); // Clone the base query

            // Exclude specific vendors for HABOULMOUNA
            $query->whereNotIn('z.CodVendedor', $this->excludedVendors); // Use array from property

            if ($request->Zona) {
                // HABOULMOUNA with specific Zone: group by Vendedor
                $facturados = $query->select('z.Sector', 'z.CodVendedor', 'z.Nombre', DB::raw('COUNT(DISTINCT fd.codcliente) as uniqueClientes'))
                                    ->where('z.Sector', '=', $request->Zona)
                                    ->groupBy('z.Sector', 'z.CodVendedor', 'z.Nombre') // Group by all selected non-aggregated columns
                                    ->orderBy('z.Sector', 'ASC')
                                    ->get();
            } else {
                // HABOULMOUNA without specific Zone: group by Sector
                $facturados = $query->select('z.Sector', DB::raw('COUNT(DISTINCT fd.codcliente) as uniqueClientes'))
                                    ->groupBy('z.Sector')
                                    ->orderBy('z.Sector', 'ASC')
                                    ->get();
            }
        } else {
            // This block handles specific supervisors (individual or group)
            $query = $baseQuery->clone(); // Clone the base query

            // Apply the supervisor group filter
            if (!empty($supervisorsToQuery)) {
                $query->whereIn('z.CodSupervisor', $supervisorsToQuery); // <-- KEY CHANGE HERE!
            } else {
                // Fallback for cases where $supervisorsToQuery might be empty or direct match
                $query->where('z.CodSupervisor', '=', $requestCodSupervisor);
            }

            // Exclude specific vendors (if applicable to this path, based on previous functions)
            // If excludedVendors should only apply to 'HABOULMOUNA', then remove this line.
            // Based on your original 'listaClientes' example, this NOT IN applies to general supervisors too.
            $query->whereNotIn('z.CodVendedor', $this->excludedVendors);

            // In this specific 'else' branch, you only had one select/grouping path:
            // Group by z.Sector and count unique clients
            $facturados = $query->select('z.Sector', DB::raw('COUNT(DISTINCT fd.codcliente) as uniqueClientes'))
                                ->groupBy('z.Sector')
                                ->orderBy('z.Sector', 'ASC')
                                ->get();
        }

        return response()->json($facturados);
    }


     public function getTotalClientesxSupervisor(Request $request)
    {
        // --- START: Logic to determine $supervisorsToQuery ---
        $supervisorsToQuery = [];
        $requestCodSupervisor = $request->CodSupervisor; // Get the requested supervisor code

        // Check if the requested supervisor is a key in our general supervisor map
        // This is only needed for the 'else' block, but it's efficient to calculate once.
        if ($requestCodSupervisor && isset($this->supervisor_general[$requestCodSupervisor])) {
            $supervisorsToQuery = $this->supervisor_general[$requestCodSupervisor];
        } else {
            $supervisorsToQuery = [$requestCodSupervisor];
        }
        // --- END: Logic to determine $supervisorsToQuery ---

        $totalClientes = null; // Initialize to null

        if ($request->CodSupervisor === 'HABOULMOUNA') {
            // This is a special user who can view all clients, potentially filtered by Zone.
            $query = DB::table('a010_clientes as aa')
                ->join('w004_zona as z', 'aa.Vendedor', '=', 'z.CodVendedor')
                ->selectRaw('z.Sector, COUNT(aa.codigo) as totalClientes');

            if ($request->Zona) {
                $query->where('z.Sector', '=', $request->Zona);
            }

            $totalClientes = $query->groupBy('z.Sector')
                                 ->orderBy('z.Sector', 'ASC')
                                 ->get();
        } else {
            // This block handles specific supervisors, including individual 'S' codes and 'S' group codes.
            $query = DB::table('a010_clientes as aa')
                ->join('w004_zona as z', 'aa.Vendedor', '=', 'z.CodVendedor')
                ->selectRaw('z.Sector, COUNT(aa.codigo) as totalClientes');

            // Apply the supervisor filter using whereIn for general supervisors
            if (!empty($supervisorsToQuery)) {
                $query->whereIn('z.CodSupervisor', $supervisorsToQuery); // <-- KEY CHANGE HERE!
            } else {
                // Fallback for cases where $supervisorsToQuery might be empty or direct match
                $query->where('z.CodSupervisor', '=', $requestCodSupervisor);
            }

            $totalClientes = $query->groupBy('z.Sector')
                                 ->orderBy('z.Sector', 'ASC')
                                 ->get();
        }

        return response()->json($totalClientes);
    }

    // PARA OBTENER LA CANTIDAD DE PRODUCTOS Y SU VENTA TOTAL PARA MEDIR SU VOLUMEN Y VENTA X GERENTE
    public function getCantidadVentasxGerente(Request $request)
{
    $now = Carbon::now();

    // 0) Preparo el array de supervisores a consultar
    $codSup = $request->CodSupervisor;
    $supervisorsToQuery = [];
    if ($codSup && substr($codSup, 0, 1) === 'S') {
        if (isset($this->supervisor_general[$codSup])) {
            // Supervisor general: padre + subs
            $supervisorsToQuery = $this->supervisor_general[$codSup];
        } else {
            // Supervisor normal
            $supervisorsToQuery = [$codSup];
        }
    }

    // 1) Fechas: startOfMonth/endOfMonth según request o ahora
    if ($request->fechaInicio !== 'undefined' && $request->fechaFin !== 'undefined') {
        $startOfMonth = $request->fechaInicio;
        $endOfMonth   = $request->fechaFin;
    } else {
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth   = $now->copy()->endOfMonth();
    }

    // 2) HABOULMOUNA (sin filtro de supervisor) vs el resto
    if ($codSup === 'HABOULMOUNA') {
        if ($request->fechaInicio !== 'undefined' && $request->fechaFin !== 'undefined') {
            // Histórico, sin filtrar supervisores
            $queryGeneral = DB::table('w007_VentasCantidadProductosHistorico')
                ->select('*')
                ->whereBetween('FechaInicio', [$startOfMonth, $endOfMonth])
                ->get();
        } else {
            // Actual, sin filtrar supervisores
            $queryGeneral = DB::table('w007_VentasCantidadProductos')
                ->select('*')
                ->get();
        }
    } else {
        if ($request->fechaInicio !== 'undefined' && $request->fechaFin !== 'undefined') {
            // Histórico, **filtrando** por padre + subs
            $queryGeneral = DB::table('w007_VentasCantidadProductosHistorico')
                ->select('*')
                ->whereBetween('FechaInicio', [$startOfMonth, $endOfMonth]);

            if (!empty($supervisorsToQuery)) {
                $queryGeneral->whereIn('CodSupervisor', $supervisorsToQuery);
            }

            $queryGeneral = $queryGeneral->get();
        } else {
            // Actual, **filtrando** por padre + subs
            $queryGeneral = DB::table('w007_VentasCantidadProductos')
                ->select('*');

            if (!empty($supervisorsToQuery)) {
                $queryGeneral->whereIn('CodSupervisor', $supervisorsToQuery);
            }

            $queryGeneral = $queryGeneral->get();
        }
    }

    return response()->json($queryGeneral);
}


    public function getCantidadVentasxVendedor(Request $request){
        $now = Carbon::now();

        // --- Lógica para determinar el rango de fechas ---
        if ($request->fechaInicio !== 'undefined' && $request->fechaFin !== 'undefined') {
            $startOfMonth = $request->fechaInicio;
            $endOfMonth = $request->fechaFin;
        } else {
            $startOfMonth = $now->copy()->startOfMonth();
            $endOfMonth = $now->copy()->endOfMonth();
        }

        // --- INICIO: Lógica para determinar $supervisorsToQuery ---
        $supervisorsToQuery = [];
        // Si CodSupervisor es proporcionado y comienza con 'S'
        if ($request->CodSupervisor && substr($request->CodSupervisor, 0, 1) === 'S') {
            if (isset($this->supervisor_general[$request->CodSupervisor])) {
                // Si es un grupo, usamos el array de supervisores asociados
                $supervisorsToQuery = $this->supervisor_general[$request->CodSupervisor];
            } else {
                // Si no es un grupo, lo ponemos en un array de un solo elemento
                $supervisorsToQuery = [$request->CodSupervisor];
            }
        }
        // --- FIN: Lógica para determinar $supervisorsToQuery ---

        // La consulta solo se ejecuta si no se especifican fechas, según tu lógica original.
        // Aquí es donde aplicaremos el filtro de supervisor.
        if ($request->fechaInicio === 'undefined' && $request->fechaFin === 'undefined') {
            $queryGeneral = DB::table('w007_VentasCantidadProductosHistorico_Vendedor')
                ->select('*')
                ->whereBetween('FechaInicio', [$startOfMonth, $endOfMonth]);

            // Aplicamos el filtro de supervisor si $supervisorsToQuery no está vacío
            if (!empty($supervisorsToQuery)) {
                // Este es el cambio clave: usar whereIn para filtrar por el grupo de supervisores
                $queryGeneral->whereIn('CodSupervisor', $supervisorsToQuery);
            } else {
                // Si $supervisorsToQuery está vacío, revertimos a la lógica original:
                // filtrar por el CodSupervisor exacto proporcionado en la solicitud.
                // Esto maneja supervisores individuales que no forman parte de un grupo definido.
                $queryGeneral->where('CodSupervisor', '=', $request->CodSupervisor);
            }

            $queryGeneral = $queryGeneral->get();

        } else {
            // Si se especifican fechas, tu lógica original no devuelve nada en este bloque.
            // Considera si necesitas una consulta diferente o si esto es intencional.
            $queryGeneral = collect(); // Devuelve una colección vacía si no se cumplen las condiciones del if de arriba
        }

        return response()->json($queryGeneral);
    }

    public function getCantidadVentasxVendedor2(Request $request)
    {
        $now = Carbon::now();

        // --- Logic to determine the date range ---
        if ($request->fechaInicio !== 'undefined' && $request->fechaFin !== 'undefined') {
            $startOfMonth = $request->fechaInicio;
            $endOfMonth = $request->fechaFin;
        } else {
            $startOfMonth = $now->copy()->startOfMonth();
            $endOfMonth = $now->copy()->endOfMonth();
        }

        // --- START: Logic to determine $supervisorsToQuery ---
        $supervisorsToQuery = [];
        // If CodSupervisor is provided and starts with 'S'
        if ($request->CodSupervisor && substr($request->CodSupervisor, 0, 1) === 'S') {
            if (isset($this->supervisor_general[$request->CodSupervisor])) {
                // If it's a group, use the array of associated supervisors
                $supervisorsToQuery = $this->supervisor_general[$request->CodSupervisor];
            } else {
                // If it's not a group, put it in a single-element array
                $supervisorsToQuery = [$request->CodSupervisor];
            }
        }
        // --- END: Logic to determine $supervisorsToQuery ---

        $queryGeneral = collect(); // Initialize as an empty collection

        // Branch 1: If dates are undefined
        if ($request->fechaInicio === 'undefined' && $request->fechaFin === 'undefined') {

            $queryBuilder = DB::table('w007_VentasCantidadProductosHistorico_Vendedor')
                ->join('w004_zona', 'w007_VentasCantidadProductosHistorico_Vendedor.CodVendedor', '=', 'w004_zona.CodVendedor')
                ->select('w007_VentasCantidadProductosHistorico_Vendedor.*', 'w004_zona.Nombre')
                ->whereBetween('w007_VentasCantidadProductosHistorico_Vendedor.FechaInicio', [$startOfMonth, $endOfMonth]);

            // Apply the supervisor filter if $supervisorsToQuery is not empty
            if (!empty($supervisorsToQuery)) {
                $queryBuilder->whereIn('w007_VentasCantidadProductosHistorico_Vendedor.CodSupervisor', $supervisorsToQuery);
            } else {
                $queryBuilder->where('w007_VentasCantidadProductosHistorico_Vendedor.CodSupervisor', $request->CodSupervisor);
            }

            $queryGeneral = $queryBuilder->get();

        // Branch 2: If dates are defined
        } else if ($request->fechaInicio !== 'undefined' && $request->fechaFin !== 'undefined') {
            $queryBuilder = DB::table('w007_VentasCantidadProductosHistorico_Vendedor')
                ->join('w004_zona', 'w007_VentasCantidadProductosHistorico_Vendedor.CodVendedor', '=', 'w004_zona.CodVendedor')
                ->select('w007_VentasCantidadProductosHistorico_Vendedor.*', 'w004_zona.Nombre')
                ->whereBetween('w007_VentasCantidadProductosHistorico_Vendedor.FechaInicio', [$startOfMonth, $endOfMonth]);

            // Apply the supervisor filter if $supervisorsToQuery is not empty
            if (!empty($supervisorsToQuery)) {
                $queryBuilder->whereIn('w007_VentasCantidadProductosHistorico_Vendedor.CodSupervisor', $supervisorsToQuery);
            } else {
                $queryBuilder->where('w007_VentasCantidadProductosHistorico_Vendedor.CodSupervisor', $request->CodSupervisor);
            }

            $queryGeneral = $queryBuilder->get();
        }

        return response()->json($queryGeneral);
    }

    public function getHistoricoMetas(Request $request)
    {
       $excludedSupervisors = ['S01', 'S02', 'S03', 'S04', 'S05', 'S06', 'S07']; //agregar los supervisores para la condicional, verificando si es supervisor o vendedor
       $isSupervisorExcluded = !in_array($request->CodSupervisor, $excludedSupervisors);
       if ($isSupervisorExcluded) {
            if ($request->Zona) {
                if ($request->porVendedor === 'si') {
                    $groupByVend = "z.CodVendedor";
                    $selectVend = "z.Sector, z.CodVendedor, z.Nombre, SUM(fd.Subtotal) as totalVendido";
                } else {
                    $groupByVend = 'z.Sector';
                    $selectVend = "z.Sector, SUM(fd.Subtotal) as totalVendido";
                }
                $historicoMetas = DB::table('e110_FacturaDetalle as fd')
                    ->join('w004_zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
                    ->selectRaw($selectVend)
                    ->whereRaw('z.CodVendedor NOT IN ("V1", "V2", "T1", "CSS1")')
                    ->whereBetween('fd.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
                    ->where('z.Sector', '=', $request->Zona)
                    ->groupBy($groupByVend)
                    ->orderBy('z.Sector')
                    ->get();

                $totalMetas = DB::table('w003_metas as m')
                    ->join('w004_zona as z', 'm.vendedor', '=', 'z.CodVendedor')
                    ->selectRaw('SUM(m.global) as metaTotalGerente')
                    ->where('z.Sector', '=', $request->Zona)
                    ->groupBy('z.Sector')
                    ->get();
            } else {
                $historicoMetas = DB::table('e110_FacturaDetalle as fd')
                    ->join('w004_zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
                    ->selectRaw('z.Sector, z.CodVendedor, SUM(fd.Subtotal) as totalVendido')
                    ->whereRaw('z.CodVendedor NOT IN ("V1", "V2", "T1", "CSS1")')
                    ->whereBetween('fd.fechadoc', [$request->fechaInicio, $request->fechaFin])
                    ->groupBy('z.Sector')
                    ->orderBy('z.Sector')
                    ->get();

                $totalMetas = DB::table('w003_metas as m')
                    ->join('w004_zona as z', 'm.vendedor', '=', 'z.CodVendedor')
                    ->selectRaw('SUM(m.global) as metaTotalGerente')
                    ->groupBy('z.Sector')
                    ->get();
            }
            return response()->json(["historicoMetas" => $historicoMetas, "totalMetas" => $totalMetas]);
        } else {
            if ($request->porVendedor === 'si') {
                $historicoMetas = DB::table('e110_FacturaDetalle as fd')
                    ->join('w004_zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
                    ->join('w003_metas as m', 'z.CodVendedor', '=', 'm.vendedor')
                    ->selectRaw('z.Sector, z.CodVendedor, z.Nombre, SUM(fd.Subtotal) as totalVendido, m.global')
                    ->where('z.CodSupervisor', '=', $request->CodSupervisor)
                    ->whereRaw('z.CodVendedor NOT IN ("V1", "V2", "T1", "CSS1")')
                    ->whereBetween('fd.fechadoc', [$request->fechaInicio, $request->fechaFin])
                    ->groupBy('z.CodVendedor')
                    ->orderBy('z.Sector', 'ASC')
                    ->orderBy('totalVendido', 'DESC')
                    ->get();

                return response()->json($historicoMetas);
            } else {
                $historicoMetas = DB::table('e110_FacturaDetalle as fd')
                    ->join('w004_zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
                    ->selectRaw('z.Sector, z.CodVendedor, SUM(fd.Subtotal) as totalVendido')
                    ->where('z.CodSupervisor', '=', $request->CodSupervisor)
                    ->whereRaw('z.CodVendedor NOT IN ("V1", "V2", "T1", "CSS1")')
                    ->whereBetween('fd.fechadoc', [$request->fechaInicio, $request->fechaFin])
                    ->orderBy('z.Sector')
                    ->get();

                $totalMetas = DB::table('w003_metas as m')
                    ->join('w004_zona as z', 'm.vendedor', '=', 'z.CodVendedor')
                    ->selectRaw('SUM(m.global) as metaTotalGerente')
                    ->where('z.CodSupervisor', '=', $request->CodSupervisor)
                    ->get();

                return response()->json(["historicoMetas" => $historicoMetas, "totalMetas" => $totalMetas]);
            }
        }
    }

    public function getFacturadoAndCobradoHoyVsAyer(Request $request)
    {
        //Facturado
        $facturadoHoy = DB::table("e100_FacturaEncabezado")
            ->select(DB::raw("SUM(TotalNeto) as FacturadoHoy"))
            ->whereNot("CodigoVendedor", ["V1", "V2", "T1", "CSS1"])
            ->whereBetween("FechaDocumento", [DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-%d 00:00:00'), INTERVAL 1 DAY)"), DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-%d 23:59:59'), INTERVAL 1 DAY)")])
            ->first();

        $facturadoAyer = DB::table("e100_FacturaEncabezado")
            ->select(DB::raw("SUM(TotalNeto) as FacturadoAyer"))
            ->whereNot("CodigoVendedor", ["V1", "V2", "T1", "CSS1"])
            ->whereBetween("FechaDocumento", [DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-%d 00:00:00'), INTERVAL 2 DAY)"), DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-%d 23:59:59'), INTERVAL 2 DAY)")])
            ->first();

        $facturadoHoy30 = DB::table("e100_FacturaEncabezado")
            ->select(DB::raw("SUM(TotalNeto) as FacturadoHoy30"))
            ->whereNot("CodigoVendedor", ["V1", "V2", "T1", "CSS1"])
            ->whereBetween("FechaDocumento", [DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00'), INTERVAL 0 MONTH)"), DB::raw("DATE_SUB(DATE_ADD(DATE_FORMAT(NOW(), '%Y-%m-01 23:59:59'), INTERVAL 1 MONTH), INTERVAL 1 DAY)")])
            ->first();

        $facturadoAyer30 = DB::table("e100_FacturaEncabezado")
            ->select(DB::raw("SUM(TotalNeto) as FacturadoAyer30"))
            ->whereNot("CodigoVendedor", ["V1", "V2", "T1", "CSS1"])
            ->whereBetween("FechaDocumento", [DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00'), INTERVAL 1 MONTH)"), DB::raw("DATE_SUB(DATE_ADD(DATE_FORMAT(NOW(), '%Y-%m-01 23:59:59'), INTERVAL 0 MONTH), INTERVAL 1 DAY)")])
            ->first();

        //Cobrado
        $cobradoHoy = DB::table("e100_FacturaEncabezado")
            ->select(DB::raw("SUM(TotalNeto) as CobradoHoy"))
            ->whereNot("CodigoVendedor", ["V1", "V2", "T1", "CSS1"])
            ->whereBetween("FechaDocumento", [DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-%d 00:00:00'), INTERVAL 1 DAY)"), DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-%d 23:59:59'), INTERVAL 1 DAY)")])
            ->where("Estatus", "=", 2)
            ->first();

        $cobradoAyer = DB::table("e100_FacturaEncabezado")
            ->select(DB::raw("SUM(TotalNeto) as CobradoAyer"))
            ->whereNot("CodigoVendedor", ["V1", "V2", "T1", "CSS1"])
            ->whereBetween("FechaDocumento", [DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-%d 00:00:00'), INTERVAL 2 DAY)"), DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-%d 23:59:59'), INTERVAL 2 DAY)")])
            ->where("Estatus", "=", 2)
            ->first();

        $cobradoHoy30 = DB::table("e100_FacturaEncabezado")
            ->select(DB::raw("SUM(TotalNeto) as CobradoHoy30"))
            ->whereNot("CodigoVendedor", ["V1", "V2", "T1", "CSS1"])
            ->whereBetween("FechaDocumento", [DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00'), INTERVAL 0 MONTH)"), DB::raw("DATE_SUB(DATE_ADD(DATE_FORMAT(NOW(), '%Y-%m-01 23:59:59'), INTERVAL 1 MONTH), INTERVAL 1 DAY)")])
            ->where("Estatus", "=", 2)
            ->first();

        $cobradoAyer30 = DB::table("e100_FacturaEncabezado")
            ->select(DB::raw("SUM(TotalNeto) as CobradoAyer30"))
            ->whereNot("CodigoVendedor", ["V1", "V2", "T1", "CSS1"])
            ->whereBetween("FechaDocumento", [DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00'), INTERVAL 1 MONTH)"), DB::raw("DATE_SUB(DATE_ADD(DATE_FORMAT(NOW(), '%Y-%m-01 23:59:59'), INTERVAL 0 MONTH), INTERVAL 1 DAY)")])
            ->where("Estatus", "=", 2)
            ->first();

        return response()->json([$facturadoHoy, $facturadoAyer, $facturadoHoy30, $facturadoAyer30, $cobradoHoy, $cobradoAyer, $cobradoHoy30, $cobradoAyer30]);
    }

    public function getParetos(Request $request)
    {
        // --- START: Logic to determine $supervisorsToQuery ---
        $supervisorsToQuery = [];
        // Check if CodSupervisor is provided and starts with 'S'
        // We're explicitly checking for 'null' string from the request, so skip if that's the case.
        if ($request->CodSupervisor && $request->CodSupervisor !== 'null' && substr($request->CodSupervisor, 0, 1) === 'S') {
            if (isset($this->supervisor_general[$request->CodSupervisor])) {
                // If it's a group, use the array of associated supervisors
                $supervisorsToQuery = $this->supervisor_general[$request->CodSupervisor];
            } else {
                // If it's not a group, put it in a single-element array
                $supervisorsToQuery = [$request->CodSupervisor];
            }
        }
        // --- END: Logic to determine $supervisorsToQuery ---

        $paretos = DB::table('w060_paretos as p')
            ->leftJoin('e100_FacturaEncabezado as f', function ($join) {
                // Using Carbon for current month's start/end dates for clarity and robustness
                $join->on('p.CodigoCliente', '=', 'f.codcliente')
                     ->whereBetween('f.FechaDocumento', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
            })
            // Subquery for Maximo_Facturado: ensure 2024 is dynamic if needed
            ->leftJoin(DB::raw('(SELECT codcliente, MAX(Maximo_Facturado) AS Maximo_Facturado, Mes FROM (SELECT codcliente, DATE_FORMAT(FechaDocumento, "%Y-%m") AS Mes, SUM(TotalNeto) AS Maximo_Facturado FROM e100_FacturaEncabezado WHERE YEAR(FechaDocumento) = "2024" GROUP BY codcliente, DATE_FORMAT(FechaDocumento, "%Y-%m")) AS max_facturado_por_mes GROUP BY codcliente) AS maximo_facturado'), 'p.CodigoCliente', '=', 'maximo_facturado.codcliente')
            // Static month names, consider making dynamic or using a lookup if year changes
            ->leftJoin(DB::raw('(SELECT "2024-01" AS Mes, "Enero" AS NomMes
                UNION ALL SELECT "2024-02", "Febrero"
                UNION ALL SELECT "2024-03", "Marzo"
                UNION ALL SELECT "2024-04", "Abril"
                UNION ALL SELECT "2024-05", "Mayo"
                UNION ALL SELECT "2024-06", "Junio"
                UNION ALL SELECT "2024-07", "Julio"
                UNION ALL SELECT "2024-08", "Agosto"
                UNION ALL SELECT "2024-09", "Septiembre"
                UNION ALL SELECT "2024-10", "Octubre"
                UNION ALL SELECT "2024-11", "Noviembre"
                UNION ALL SELECT "2024-12", "Diciembre") AS Meses'), 'maximo_facturado.Mes', '=', 'Meses.Mes')
            ->select('p.*', DB::raw('IFNULL(SUM(f.TotalNeto), 0) AS Facturado'), DB::raw('IFNULL(maximo_facturado.Maximo_Facturado, 0) AS Maximo_Facturado'), DB::raw('COALESCE(Meses.NomMes, "N/A") AS Mes'))
            ->groupBy('p.CodigoCliente');

        // --- Focus on modifying this part ---
        if ($request->CodSupervisor && $request->CodSupervisor !== 'null') {
            if (!empty($supervisorsToQuery)) {
                // If we have a group or a specific supervisor, use whereIn
                $paretos = $paretos->whereIn('p.Supervisor', $supervisorsToQuery); // <-- KEY CHANGE
            } else {
                // If it's not a recognized group, revert to the original where for individual
                // or other non-'S' type supervisors
                $paretos = $paretos->where('p.Supervisor', $request->CodSupervisor);
            }
        }
        // --- End of modification ---

        if($request->orderBy === 'Facturado') {
            $paretos = $paretos->orderBy('Facturado', 'DESC');
        } else {
            $paretos = $paretos
            ->orderBy('p.CodigoVendedor')
            ->orderBy('Facturado', 'DESC');
        }

        if ($request->gen === 'xls') {
            $paretos = $paretos->get();
        } else {
            $paretos = $paretos->paginate(20);
        }

        return response()->json($paretos);
    }
}
