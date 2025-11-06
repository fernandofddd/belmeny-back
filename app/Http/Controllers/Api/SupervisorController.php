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
use App\Models\CustomUser;

// Query filters
use App\Filters\V1\MetasFilter;

// Resources
use App\Http\Resources\MetasResource;
use App\Http\Resources\MetasCollection;
use App\Models\VentasClientes;

// Http and supports
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupervisorController extends Controller
{
    // Declara y asigna directamente la propiedad protegida
    protected $excludedVendors = ['V1', 'V2', 'T1', 'CCS1'];

    //Variable para que un supervisor pueda ver la informacion general de toda una zona y tengo sub-supervisores
    /*Aca dejo una variable arreglo para que se puedan añadir los que se necesiten y luego simplemente se llaman
    no hago otro perfil por falta de tiempo
    pero con esto se puede acceder mas facilmente. 
    Uso el S001 de ejemplo, no es un perfil de supervisor real, 
    sino lo uso para excluir los perfiles que son de maracaibo pero no son de algun supervisor 
     */
    protected $supervisor_general = [
        'S02' => ['S02', 'S06', 'S07'],
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

    public function getMetasSemanalVendedores(Request $request)
    {
        $resultado = $request->CodSupervisor;
        if ($request->CodSupervisor == "S02") {
            $resultado = $this->supervisor_map['S02'] ?? [];
        }
        $firstWeekIngco = DB::table('e210_FacturaDetalle as fd')
            ->join('Zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->select('z.CodVendedor', 'z.Nombre', DB::raw('SUM(fd.Subtotal) as ventasIngco'))
            ->where('z.CodSupervisor', $request->CodSupervisor)
            ->where('fd.Grupo', '=', '021')
            ->whereNotIn('z.CodVendedor', $this->excludedVendors)
            ->whereBetween('fd.fechadoc', ['2023-07-01', '2023-07-02'])
            ->groupBy('z.CodVendedor')
            ->orderBy('z.CodVendedor')
            ->get();

        $secondWeekIngco = DB::table('e210_FacturaDetalle as fd')
            ->join('Zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->select('z.Sector', 'z.CodVendedor', 'z.Nombre', DB::raw('SUM(fd.Subtotal) as ventasIngco'))
            ->where('z.CodSupervisor', $request->CodSupervisor)
            ->where('fd.Grupo', '=', '021')
            ->whereNotIn('z.CodVendedor', $this->excludedVendors)
            ->whereBetween('fd.fechadoc', ['2023-07-03', '2023-07-09'])
            ->groupBy('z.CodVendedor', 'z.Sector')
            ->orderBy('z.CodVendedor')
            ->get();

        $thirdWeekIngco = DB::table('e210_FacturaDetalle as fd')
            ->join('Zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->select('z.CodVendedor', 'z.Nombre', DB::raw('SUM(fd.Subtotal) as ventasIngco'))
            ->where('z.CodSupervisor', $request->CodSupervisor)
            ->where('fd.Grupo', '=', '021')
            ->whereNotIn('z.CodVendedor', $this->excludedVendors)
            ->whereBetween('fd.fechadoc', ['2023-07-10', '2023-07-16'])
            ->groupBy('z.CodVendedor')
            ->orderBy('z.CodVendedor')
            ->get();

        $fourthWeekIngco = DB::table('e210_FacturaDetalle as fd')
            ->join('Zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->select('z.CodVendedor', 'z.Nombre', DB::raw('SUM(fd.Subtotal) as ventasIngco'))
            ->where('z.CodSupervisor', $request->CodSupervisor)
            ->where('fd.Grupo', '=', '021')
            ->whereNotIn('z.CodVendedor', $this->excludedVendors)
            ->whereBetween('fd.fechadoc', ['2023-07-17', '2023-07-23'])
            ->groupBy('z.CodVendedor')
            ->orderBy('z.CodVendedor')
            ->get();

        $fifthWeekIngco = DB::table('e210_FacturaDetalle as fd')
            ->join('Zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->select('z.CodVendedor', 'z.Nombre', DB::raw('SUM(fd.Subtotal) as ventasIngco'))
            ->where('z.CodSupervisor', $request->CodSupervisor)
            ->where('fd.Grupo', '=', '021')
            ->whereNotIn('z.CodVendedor', $this->excludedVendors)
            ->whereBetween('fd.fechadoc', ['2023-07-24', '2023-07-30'])
            ->groupBy('z.CodVendedor')
            ->orderBy('z.CodVendedor')
            ->get();

        $sixthWeekIngco = DB::table('e210_FacturaDetalle as fd')
            ->join('Zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->select('z.CodVendedor', 'z.Nombre', DB::raw('SUM(fd.Subtotal) as ventasIngco'))
            ->where('z.CodSupervisor', $request->CodSupervisor)
            ->where('fd.Grupo', '=', '021')
            ->whereNotIn('z.CodVendedor', $this->excludedVendors)
            ->where('fd.fechadoc', '=', '2023-07-31')
            // ->whereBetween('fd.fechadoc', ['2023-07-01', '2023-07-02'])
            ->groupBy('z.CodVendedor')
            ->orderBy('z.CodVendedor')
            ->get();


        // SEMANAS VERT
        $firstWeekVert = DB::table('e210_FacturaDetalle as fd')
            ->join('Zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->select('z.CodVendedor', 'z.Nombre', DB::raw('SUM(fd.Subtotal) as ventasIngco'))
            ->where('z.CodSupervisor', $request->CodSupervisor)
            ->where('fd.Grupo', '=', '021')
            ->whereNotIn('z.CodVendedor', $this->excludedVendors)
            ->whereBetween('fd.fechadoc', ['2023-07-01', '2023-07-02'])
            ->groupBy('z.CodVendedor')
            ->orderBy('z.CodVendedor')
            ->get();

        $secondWeekVert = DB::table('e210_FacturaDetalle as fd')
            ->join('Zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->select('z.Sector', 'z.CodVendedor', 'z.Nombre', DB::raw('SUM(fd.Subtotal) as ventasIngco'))
            ->where('z.CodSupervisor', $request->CodSupervisor)
            ->where('fd.Grupo', '=', '021')
            ->whereNotIn('z.CodVendedor', $this->excludedVendors)
            ->whereBetween('fd.fechadoc', ['2023-07-03', '2023-07-09'])
            ->groupBy('z.CodVendedor', 'z.Sector')
            ->orderBy('z.CodVendedor')
            ->get();

        $thirdWeekVert = DB::table('e210_FacturaDetalle as fd')
            ->join('Zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->select('z.CodVendedor', 'z.Nombre', DB::raw('SUM(fd.Subtotal) as ventasIngco'))
            ->where('z.CodSupervisor', $request->CodSupervisor)
            ->where('fd.Grupo', '=', '021')
            ->whereNotIn('z.CodVendedor', $this->excludedVendors)
            ->whereBetween('fd.fechadoc', ['2023-07-10', '2023-07-16'])
            ->groupBy('z.CodVendedor')
            ->orderBy('z.CodVendedor')
            ->get();

        $fourthWeekVert = DB::table('e210_FacturaDetalle as fd')
            ->join('Zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->select('z.CodVendedor', 'z.Nombre', DB::raw('SUM(fd.Subtotal) as ventasIngco'))
            ->where('z.CodSupervisor', $request->CodSupervisor)
            ->where('fd.Grupo', '=', '021')
            ->whereNotIn('z.CodVendedor', $this->excludedVendors)
            ->whereBetween('fd.fechadoc', ['2023-07-17', '2023-07-23'])
            ->groupBy('z.CodVendedor')
            ->orderBy('z.CodVendedor')
            ->get();

        $fifthWeekVert = DB::table('e210_FacturaDetalle as fd')
            ->join('Zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->select('z.CodVendedor', 'z.Nombre', DB::raw('SUM(fd.Subtotal) as ventasIngco'))
            ->where('z.CodSupervisor', $request->CodSupervisor)
            ->where('fd.Grupo', '=', '021')
            ->whereNotIn('z.CodVendedor', $this->excludedVendors)
            ->whereBetween('fd.fechadoc', ['2023-07-24', '2023-07-30'])
            ->groupBy('z.CodVendedor')
            ->orderBy('z.CodVendedor')
            ->get();

        $sixthWeekVert = DB::table('e210_FacturaDetalle as fd')
            ->join('Zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->select('z.CodVendedor', 'z.Nombre', DB::raw('SUM(fd.Subtotal) as ventasIngco'))
            ->where('z.CodSupervisor', $request->CodSupervisor)
            ->where('fd.Grupo', '=', '021')
            ->whereNotIn('z.CodVendedor', $this->excludedVendors)
            ->where('fd.fechadoc', '=', '2023-07-31')
            // ->whereBetween('fd.fechadoc', ['2023-07-01', '2023-07-31'])
            ->groupBy('z.CodVendedor')
            ->orderBy('z.CodVendedor')
            ->get();

        return response()->json([
            "Ingco" => [
                "firstWeekIngco" => $firstWeekIngco,
                "secondWeekIngco" => $secondWeekIngco,
                "thirdWeekIngco" => $thirdWeekIngco,
                "fourthWeekIngco" => $fourthWeekIngco,
                "fifthWeekIngco" => $fifthWeekIngco,
                "sixthWeekIngco" => $sixthWeekIngco
            ],
            "Vert" => [
                "firstWeekVert" => $firstWeekVert,
                "secondWeekVert" => $secondWeekVert,
                "thirdWeekVert" => $thirdWeekVert,
                "fourthWeekVert" => $fourthWeekVert,
                "fifthWeekVert" => $fifthWeekVert,
                "sixthWeekVert" => $sixthWeekVert
            ]
        ]);
    }

    public function getTop10Vendedores(Request $request)
    {
        $top10 = DB::table('e110_FacturaDetalle as fd')
            ->join('w004_zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->select('z.Sector', 'fd.CodigoVendedor', 'z.Nombre', DB::raw('SUM(fd.Subtotal) as Subtotal'))
            ->whereRaw('fd.fechadoc BETWEEN DATE_SUB(LAST_DAY(NOW()), INTERVAL DAYOFMONTH(LAST_DAY(NOW())) -1 day) AND LAST_DAY(NOW())')
            ->whereNotIn('z.CodVendedor', $this->excludedVendors)
            ->groupBy('fd.CodigoVendedor')
            ->orderBy('Subtotal', 'DESC')
            ->take(10)
            ->get();

        return response()->json($top10);
    }

    public function getFPAPByZona(Request $request)
    {
        $fpap = DB::table('e100_FacturaEncabezado AS fe')
            ->join('w004_zona AS z', 'fe.CodigoVendedor', '=', 'z.CodVendedor')
            ->select(
                'z.Sector',
                DB::raw('ROUND(SUM(CASE WHEN fe.Estatus >= 0 THEN fe.TotalNeto ELSE 0 END)) as Facturado'),
                DB::raw('ROUND((SUM(CASE WHEN fe.Estatus = 2 THEN fe.TotalNeto ELSE 0 END)) + ROUND(SUM(CASE WHEN fe.Estatus = 1 THEN fe.Abonado ELSE 0 END))) as CanceladoMasAbonado'),
                DB::raw('ROUND((SUM(CASE WHEN fe.Estatus >= 0 THEN fe.TotalNeto ELSE 0 END)) - 
            ROUND((SUM(CASE WHEN fe.Estatus = 2 THEN fe.TotalNeto ELSE 0 END)) + 
            ROUND(SUM(CASE WHEN fe.Estatus = 1 THEN fe.Abonado ELSE 0 END)))) as Pendiente')
            )
            ->whereBetween('fe.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->groupBy('z.Sector')
            ->orderBy('Facturado', 'DESC')
            ->get();

        return response()->json($fpap);
    }

    public function getTotalFPC(Request $request)
    {
        $totalfpc = DB::table('e100_FacturaEncabezado as fe')
            ->select(
                DB::raw('ROUND(SUM(CASE WHEN fe.Estatus >= 0 THEN fe.TotalNeto ELSE 0 END)) as Facturado'),
                DB::raw('ROUND((SUM(CASE WHEN fe.Estatus = 2 THEN fe.TotalNeto ELSE 0 END)) + ROUND(SUM(CASE WHEN fe.Estatus = 1 THEN fe.Abonado ELSE 0 END))) as CanceladoMasAbonado'),
                DB::raw('ROUND((SUM(CASE WHEN fe.Estatus >= 0 THEN fe.TotalNeto ELSE 0 END)) - 
            ROUND((SUM(CASE WHEN fe.Estatus = 2 THEN fe.TotalNeto ELSE 0 END)) + 
            ROUND(SUM(CASE WHEN fe.Estatus = 1 THEN fe.Abonado ELSE 0 END)))) as Pendiente')
            )
            ->whereBetween('fe.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->first();

        return response()->json($totalfpc);
    }

    public function getEstimaciones(Request $request)
    {
        $fpap = DB::table('e100_FacturaEncabezado AS fe')
            ->join('w004_zona AS z', 'fe.CodigoVendedor', '=', 'z.CodVendedor')
            ->select(
                'z.CodVendedor',
                'z.Nombre',
                DB::raw('ROUND(SUM(CASE WHEN fe.Estatus >= 0 THEN fe.TotalNeto ELSE 0 END)) as Facturado'),
                DB::raw('ROUND((SUM(CASE WHEN fe.Estatus = 2 THEN fe.TotalNeto ELSE 0 END)) + ROUND(SUM(CASE WHEN fe.Estatus = 1 THEN fe.Abonado ELSE 0 END))) as CanceladoMasAbonado'),
                DB::raw('ROUND((SUM(CASE WHEN fe.Estatus >= 0 THEN fe.TotalNeto ELSE 0 END)) - 
            ROUND((SUM(CASE WHEN fe.Estatus = 2 THEN fe.TotalNeto ELSE 0 END)) + 
            ROUND(SUM(CASE WHEN fe.Estatus = 1 THEN fe.Abonado ELSE 0 END)))) as Pendiente')
            )
            ->whereBetween('fe.FechaDocumento', [$request->fechaInicio, $request->fechaFin])
            ->where('z.Sector', '=', $request->zona)
            ->whereNotIn('z.CodVendedor', $this->excludedVendors)
            ->groupBy('z.CodVendedor')
            ->orderBy('Facturado', 'DESC')
            ->get();

        return response()->json($fpap);
    }

    public function obtenerFPAPByZona(Request $request)
    {
        // Get the start and end of the previous month
        $startDate1 = Carbon::now()->subMonth()->startOfMonth();
        $endDate1 = Carbon::now()->subMonth()->endOfMonth();

        // Get the start and end of two months ago
        $startDate2 = Carbon::now()->subMonths(2)->startOfMonth();
        $endDate2 = Carbon::now()->subMonths(2)->endOfMonth();

        $salesData1 = DB::table('e100_FacturaEncabezado as f')
            ->select(
                'z.Sector',
                'z.CodVendedor',
                'z.CodSupervisor',
                DB::raw('SUM(CASE WHEN f.Estatus >= 0 THEN f.BaseImponible ELSE 0 END) as Facturado'),
                DB::raw('SUM(CASE WHEN f.Estatus = 2 THEN f.BaseImponible ELSE 0 END) as Pagado'),
                DB::raw('SUM(CASE WHEN f.Estatus = 1 THEN f.Abonado ELSE 0 END) as Abonado'),
                DB::raw('(SUM(CASE WHEN f.Estatus >= 0 THEN f.BaseImponible ELSE 0 END) - 
                (SUM(CASE WHEN f.Estatus = 2 THEN f.BaseImponible ELSE 0 END) + 
                SUM(CASE WHEN f.Estatus = 1 THEN f.Abonado ELSE 0 END))) as Pendiente'),
                DB::raw("CONCAT(UPPER(LEFT(DATE_FORMAT('" . $startDate1 . "', '%M'), 1)), LOWER(SUBSTRING(DATE_FORMAT('" . $endDate1 . "', '%M'), 2))) as Mes")
            )
            ->join('w004_zona as z', 'f.CodigoVendedor', '=', 'z.CodVendedor')
            ->whereBetween('f.FechaDocumento', [$startDate1, $endDate1])
            ->where('z.Sector', '=', $request->Zona)
            ->groupBy('z.CodVendedor')
            ->orderBy('z.Sector', 'ASC')
            ->get();

        $salesData2 = DB::table('e100_FacturaEncabezado as f')
            ->select(
                'z.Sector',
                'z.CodVendedor',
                'z.CodSupervisor',
                DB::raw('SUM(CASE WHEN f.Estatus >= 0 THEN f.BaseImponible ELSE 0 END) as Facturado'),
                DB::raw('SUM(CASE WHEN f.Estatus = 2 THEN f.BaseImponible ELSE 0 END) as Pagado'),
                DB::raw('SUM(CASE WHEN f.Estatus = 1 THEN f.Abonado ELSE 0 END) as Abonado'),
                DB::raw('(SUM(CASE WHEN f.Estatus >= 0 THEN f.BaseImponible ELSE 0 END) - 
                (SUM(CASE WHEN f.Estatus = 2 THEN f.BaseImponible ELSE 0 END) + 
                SUM(CASE WHEN f.Estatus = 1 THEN f.Abonado ELSE 0 END))) as Pendiente'),
                DB::raw("CONCAT(UPPER(LEFT(DATE_FORMAT('" . $startDate2 . "', '%M'), 1)), LOWER(SUBSTRING(DATE_FORMAT('" . $endDate2 . "', '%M'), 2))) as Mes")
            )
            ->join('w004_zona as z', 'f.CodigoVendedor', '=', 'z.CodVendedor')
            ->whereBetween('f.FechaDocumento', [$startDate2, $endDate2])
            ->where('z.Sector', '=', $request->Zona)
            ->groupBy('z.CodVendedor')
            ->orderBy('z.Sector', 'ASC')
            ->get();

        return response()->json(["1mesPasado" => $salesData1, "2mesPasado" => $salesData2]);
    }

    public function statusPedidos(Request $request)
    {
        $pedidosBandeja = DB::table('w030_ControlDespacho')
            ->select(DB::raw('COUNT(Documento) as Bandeja'), DB::raw("DATE_FORMAT(FLlegada,'%Y-%m-%d') as Fecha"), DB::raw("CASE
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Monday' Then 'Lunes'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Tuesday' Then 'Martes'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Wednesday' Then 'Miercoles'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Thursday' Then 'Jueves'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Friday' Then 'Viernes'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Saturday' Then 'Sabado'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Sunday' Then 'Domingo'
        ELSE DATE_FORMAT(FLlegada,'%W')
    END AS DiaSemana"))
            ->whereBetween('FLlegada', ["DATE_ADD(DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00'),INTERVAL 1 WEEK),INTERVAL 1 DAY)", "DATE_FORMAT(NOW(),'%Y-%m-%d 23:59:59')"])
            ->whereNull('CodDepositario')
            ->groupBy('Fecha')
            ->orderBy('Fecha', 'ASC')
            ->get();

        $pedidosImpresos = DB::table('w030_ControlDespacho')
            ->select(DB::raw('COUNT(Documento) as Impreso'), DB::raw("DATE_FORMAT(FLlegada,'%Y-%m-%d') as Fecha"), DB::raw("CASE
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Monday' Then 'Lunes'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Tuesday' Then 'Martes'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Wednesday' Then 'Miercoles'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Thursday' Then 'Jueves'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Friday' Then 'Viernes'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Saturday' Then 'Sabado'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Sunday' Then 'Domingo'
        ELSE DATE_FORMAT(FLlegada,'%W')
    END AS DiaSemana"))
            ->whereBetween('FLlegada', ["DATE_ADD(DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00'),INTERVAL 1 WEEK),INTERVAL 1 DAY)", "DATE_FORMAT(NOW(),'%Y-%m-%d 23:59:59')"])
            ->whereNotNull('CodDepositario')
            ->groupBy('Fecha')
            ->orderBy('Fecha', 'ASC')
            ->get();

        $pedidosFacturados = DB::table('w030_ControlDespacho')
            ->select(DB::raw('COUNT(Factura) as Facturado'), DB::raw("DATE_FORMAT(FLlegada,'%Y-%m-%d') as Fecha"), DB::raw("CASE
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Monday' Then 'Lunes'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Tuesday' Then 'Martes'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Wednesday' Then 'Miercoles'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Thursday' Then 'Jueves'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Friday' Then 'Viernes'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Saturday' Then 'Sabado'
        WHEN DATE_FORMAT(FLlegada,'%W') = 'Sunday' Then 'Domingo'
        ELSE DATE_FORMAT(FLlegada,'%W')
    END AS DiaSemana"))
            ->whereBetween('FLlegada', ["DATE_ADD(DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00'),INTERVAL 1 WEEK),INTERVAL 1 DAY)", "DATE_FORMAT(NOW(),'%Y-%m-%d 23:59:59')"])
            ->whereNotNull('CodDepositario')
            ->whereNotNull('Factura')
            ->groupBy('Fecha')
            ->orderBy('Fecha', 'ASC')
            ->get();

        return response()->json(["bandeja" => $pedidosBandeja, "impresos" => $pedidosImpresos, "facturados" => $pedidosFacturados]);
    }

    public function getPedidosPorVendedor(Request $request)
{
    $startDate = $request->fechaInicio;
    $endDate   = $request->fechaFin;
    $sector    = $request->zona;
    $codSup    = $request->CodSupervisor;

    // 1) Preparamos el array de supervisores a consultar
    $supervisorsToQuery = [];
    if ($codSup && substr($codSup, 0, 1) === 'S') {
        if (isset($this->supervisor_general[$codSup])) {
            // supervisor general: padre + subs
            $supervisorsToQuery = $this->supervisor_general[$codSup];
        } else {
            // supervisor “normal”
            $supervisorsToQuery = [$codSup];
        }
    }

    // 2) CASE para nombres
    $supervisorNames = [
        'S01' => 'Lissete Nuñez',
        'S02' => 'Luis Sastre',
        'S03' => 'Javier Villasmil',
        'S04' => 'Adel Codallo',
        'S05' => 'ARELYS COLMENARES',
        'S06' => 'Antonio Perez',
        'S07' => 'Carlos Valiente',
    ];
    $cases = [];
    foreach ($supervisorNames as $code => $name) {
        $cases[] = "WHEN '$code' THEN '$name'";
    }
    $supervisorCase = "CASE z.CodSupervisor " . implode(' ', $cases) . " ELSE '' END AS Supervisor";

    // 3) Armamos los tres queries, inyectando whereIn si corresponde
    // 3.1 PedidosPorVendedor
    $qb = DB::table('e010_PedidoEncabezado as pe')
        ->select(
            DB::raw('IFNULL(COUNT(pe.documento), 0) as Pedidos'),
            'z.CodVendedor',
            'z.Nombre',
            DB::raw($supervisorCase),
            'z.Sector',
            DB::raw("DATE_FORMAT(pe.fechayhora, '%Y-%m-%d') AS Fecha")
        )
        ->join('w004_zona as z', 'pe.Vendedor', '=', 'z.CodVendedor')
        ->whereBetween('pe.fechayhora', [$startDate, $endDate])
        ->whereNotIn('z.CodVendedor', $this->excludedVendors)
        ->groupBy('z.CodVendedor', DB::raw("DATE_FORMAT(pe.fechayhora, '%Y-%m-%d')"))
        ->orderBy('z.CodVendedor');

    if (!empty($supervisorsToQuery)) {
        $qb->whereIn('z.CodSupervisor', $supervisorsToQuery);
    }

    $pedidosPorVendedor = $qb->get();

    // 3.2 Conteo de pedidos por vendedor
    $qb2 = DB::table('e010_PedidoEncabezado as p')
        ->join('w004_zona as z', 'p.Vendedor', '=', 'z.CodVendedor')
        ->select(
            'p.Vendedor',
            DB::raw('COUNT(DISTINCT p.documento) as nroPedidos'),
            DB::raw('SUM(p.Monto) as TotalPedido')
        )
        ->whereBetween('p.fechayhora', [$startDate, $endDate])
        ->groupBy('p.Vendedor');

    if (!empty($supervisorsToQuery)) {
        $qb2->whereIn('z.CodSupervisor', $supervisorsToQuery);
    }

    $pedidos = $qb2->get();

    // 3.3 Facturación por vendedor
    $qb3 = DB::table('e100_FacturaEncabezado as f')
        ->join('w004_zona as z', 'f.CodigoVendedor', '=', 'z.CodVendedor')
        ->select(
            'f.CodigoVendedor',
            DB::raw('SUM(f.TotalNeto) as TotalFacturado')
        )
        ->whereBetween('f.FechaDocumento', [$startDate, $endDate])
        ->groupBy('f.CodigoVendedor');

    if (!empty($supervisorsToQuery)) {
        $qb3->whereIn('z.CodSupervisor', $supervisorsToQuery);
    }

    $facturas = $qb3->get();

    // 4) Devolvemos el JSON
    return response()->json([
        "pedidosPorVendedor" => $pedidosPorVendedor,
        "nroPedidos"         => $pedidos,
        "nroFacturas"        => $facturas
    ]);
}


    public function getPedidosAyer(Request $request)
{
    $startDate = now(config('app.timezone'))->subDay()->startOfDay()->toDateTimeString();
    $endDate   = now(config('app.timezone'))->subDay()->endOfDay()->toDateTimeString();

    // 1) Consulta original
    $original = DB::table('e010_PedidoEncabezado as p')
        ->join('w004_zona as z', 'p.Vendedor', '=', 'z.CodVendedor')
        ->select(
            'z.CodSupervisor',
            DB::raw('COUNT(DISTINCT p.documento) as nroPedidos'),
            DB::raw('SUM(p.Monto)             as TotalPedido')
        )
        ->whereBetween('p.fechayhora', [$startDate, $endDate])
        ->groupBy('z.CodSupervisor')
        ->get();

    // 2) Preparamos colecciones
    $all        = collect($original->toArray());
    $aggregates = collect();

    // 3) Para cada supervisor general, sumamos propios + subsupervisores
    foreach ($this->supervisor_general as $padre => $subs) {
        // Agrupamos en $grupo todas las filas cuyo CodSupervisor sea el padre o alguno de sus subs
        $grupo = $all->filter(fn($item) =>
            $item->CodSupervisor === $padre
         || in_array($item->CodSupervisor, $subs)
        );

        if ($grupo->isNotEmpty()) {
            // Construimos la fila agregada
            $aggregates->push((object)[
                'CodSupervisor' => $padre,
                'nroPedidos'    => $grupo->sum('nroPedidos'),
                'TotalPedido'   => $grupo->sum('TotalPedido'),
            ]);

            // Eliminamos la fila original del padre (si existía)
            $all = $all->reject(fn($item) =>
                $item->CodSupervisor === $padre
            );
        }
    }

    // 4) Unimos: originales (quintares de padres ya eliminados) + agregados
    $resultado = $all
        ->merge($aggregates)
        ->values();

    return response()->json($resultado);
}



    public function getCobrosAnualesEnCurso(Request $request)
    {

        switch ($request->year) {
            case 2023:
                $startDate = Carbon::parse("2023-01-01 00:00:00");
                $endDate = Carbon::parse("2023-12-31 23:59:59");
                break;

            case 2024:
                $startDate = Carbon::parse("2024-01-01 00:00:00");
                $endDate = Carbon::parse("2024-12-31 23:59:59");
                break;

            case 2025:
                $startDate = Carbon::parse("2025-01-01 00:00:00");
                $endDate = Carbon::parse("2025-12-31 23:59:59");
                break;

            default:
                $startDate = Carbon::parse("2025-01-01 00:00:00");
                $endDate = Carbon::parse("2025-12-31 23:59:59");
                break;
        }

        $results = DB::table('e100_FacturaEncabezado as f')
            ->select(
                DB::raw('SUM(CASE WHEN MONTH(FechaDocumento) = 1 AND Estatus = 2 THEN TotalNeto ELSE 0 END) + SUM(CASE WHEN MONTH(FechaDocumento) = 1 AND Estatus = 1 THEN Abonado ELSE 0 END) as Enero,
                SUM(CASE WHEN MONTH(FechaDocumento) = 2 AND Estatus = 2 THEN TotalNeto ELSE 0 END) + SUM(CASE WHEN MONTH(FechaDocumento) = 2 AND Estatus = 1 THEN Abonado ELSE 0 END) as Febrero,
                SUM(CASE WHEN MONTH(FechaDocumento) = 3 AND Estatus = 2 THEN TotalNeto ELSE 0 END) + SUM(CASE WHEN MONTH(FechaDocumento) = 3 AND Estatus = 1 THEN Abonado ELSE 0 END) as Marzo,
                SUM(CASE WHEN MONTH(FechaDocumento) = 4 AND Estatus = 2 THEN TotalNeto ELSE 0 END) + SUM(CASE WHEN MONTH(FechaDocumento) = 4 AND Estatus = 1 THEN Abonado ELSE 0 END) as Abril,
                SUM(CASE WHEN MONTH(FechaDocumento) = 5 AND Estatus = 2 THEN TotalNeto ELSE 0 END) + SUM(CASE WHEN MONTH(FechaDocumento) = 5 AND Estatus = 1 THEN Abonado ELSE 0 END) as Mayo,
                SUM(CASE WHEN MONTH(FechaDocumento) = 6 AND Estatus = 2 THEN TotalNeto ELSE 0 END) + SUM(CASE WHEN MONTH(FechaDocumento) = 6 AND Estatus = 1 THEN Abonado ELSE 0 END) as Junio,
                SUM(CASE WHEN MONTH(FechaDocumento) = 7 AND Estatus = 2 THEN TotalNeto ELSE 0 END) + SUM(CASE WHEN MONTH(FechaDocumento) = 7 AND Estatus = 1 THEN Abonado ELSE 0 END) as Julio,
                SUM(CASE WHEN MONTH(FechaDocumento) = 8 AND Estatus = 2 THEN TotalNeto ELSE 0 END) + SUM(CASE WHEN MONTH(FechaDocumento) = 8 AND Estatus = 1 THEN Abonado ELSE 0 END) as Agosto,
                SUM(CASE WHEN MONTH(FechaDocumento) = 9 AND Estatus = 2 THEN TotalNeto ELSE 0 END) + SUM(CASE WHEN MONTH(FechaDocumento) = 9 AND Estatus = 1 THEN Abonado ELSE 0 END) as Septiembre,
                SUM(CASE WHEN MONTH(FechaDocumento) = 10 AND Estatus = 2 THEN TotalNeto ELSE 0 END) + SUM(CASE WHEN MONTH(FechaDocumento) = 10 AND Estatus = 1 THEN Abonado ELSE 0 END) as Octubre,
                SUM(CASE WHEN MONTH(FechaDocumento) = 11 AND Estatus = 2 THEN TotalNeto ELSE 0 END) + SUM(CASE WHEN MONTH(FechaDocumento) = 11 AND Estatus = 1 THEN Abonado ELSE 0 END) as Noviembre,
                SUM(CASE WHEN MONTH(FechaDocumento) = 12 AND Estatus = 2 THEN TotalNeto ELSE 0 END) + SUM(CASE WHEN MONTH(FechaDocumento) = 12 AND Estatus = 1 THEN Abonado ELSE 0 END) as Diciembre')
            )->whereBetween('FechaDocumento', [$startDate, $endDate])
            ->whereNotIn('CodigoVendedor', $this->excludedVendors)
            ->get();

        return response()->json($results->first());
    }

    public function getFacturasVencidas(Request $request)
    {
        $query = DB::table('e100_FacturaEncabezado as fe')
            ->select(
                DB::raw('SUM(CASE
                            WHEN DATEDIFF(CURDATE(), fe.FechaVencimiento) BETWEEN 1 AND 15 THEN 1
                            ELSE 0
                        END) AS Vencidas_1_15_Dias'),
                DB::raw('SUM(CASE
                            WHEN DATEDIFF(CURDATE(), fe.FechaVencimiento) BETWEEN 16 AND 29 THEN 1
                            ELSE 0
                        END) AS Vencidas_15_29_Dias'),
                DB::raw('SUM(CASE
                            WHEN DATEDIFF(CURDATE(), fe.FechaVencimiento) >= 30 THEN 1
                            ELSE 0
                        END) AS Vencidas_30_Dias'),
                DB::raw('SUM(CASE
                            WHEN DATEDIFF(CURDATE(), fe.FechaVencimiento) BETWEEN 1 AND 15 THEN fe.TotalNeto
                            ELSE 0
                        END) AS Vencidas_1_15_Dias_TotalNeto'),
                DB::raw('SUM(CASE
                            WHEN DATEDIFF(CURDATE(), fe.FechaVencimiento) BETWEEN 16 AND 29 THEN fe.TotalNeto
                            ELSE 0
                        END) AS Vencidas_15_29_Dias_TotalNeto'),
                DB::raw('SUM(CASE
                            WHEN DATEDIFF(CURDATE(), fe.FechaVencimiento) >= 30 THEN fe.TotalNeto
                            ELSE 0
                        END) AS Vencidas_30_Dias_TotalNeto')
            )
            ->whereYear('fe.FechaDocumento', '=', Carbon::now()->year)
            ->where('fe.Estatus', '<', 2)
            // Using Carbon::now()->toDateString() for CURDATE() is more Laravelesque
            ->where('fe.FechaVencimiento', '<', Carbon::now()->toDateString());

        // Lógica para determinar los supervisores a buscar si se proporciona CodSupervisor
        if ($request->CodSupervisor && substr($request->CodSupervisor, 0, 1) === 'S') {
            $supervisorsToQuery = [];
            if (isset($this->supervisor_general[$request->CodSupervisor])) {
                // Si el CodSupervisor solicitado es un grupo (ej. S02), usa todos los códigos del grupo
                $supervisorsToQuery = $this->supervisor_general[$request->CodSupervisor];
            } else {
                // Si no es un grupo, lo tratamos como un supervisor individual,
                // pero lo ponemos en un array para que 'whereIn' siempre funcione.
                $supervisorsToQuery = [$request->CodSupervisor];
            }
            // Unir con la tabla 'w004_zona' y aplicar la condición del supervisor
            $query->join("w004_zona as z", "fe.CodigoVendedor", "=", "z.CodVendedor")
                ->whereIn('z.CodSupervisor', $supervisorsToQuery); // <-- ¡CAMBIO CLAVE AQUÍ!
        }
        $facturasVencidas = $query->first();

        return response()->json($facturasVencidas);
    }

    public function getFacturasEmitidas(Request $request)
    {
        $query = DB::table("e100_FacturaEncabezado as fe")
            ->select(DB::raw("COUNT(fe.Documento) as FacturasEmitidas"))
            ->whereBetween('fe.FechaDocumento', [
                Carbon::now()->startOfMonth(), // Usando Carbon para la fecha de inicio del mes
                Carbon::now()->endOfMonth()    // Usando Carbon para la fecha de fin del mes
            ])
            ->whereNotIn("fe.CodigoVendedor", $this->excludedVendors);

        // Lógica para determinar los supervisores a buscar
        if ($request->CodSupervisor && substr($request->CodSupervisor, 0, 1) === 'S') {
            $supervisorsToQuery = [];

            if (isset($this->supervisor_general[$request->CodSupervisor])) {
                // Si el CodSupervisor solicitado es un grupo (ej. S02), usa todos los códigos del grupo
                $supervisorsToQuery = $this->supervisor_general[$request->CodSupervisor];
            } else {
                // Si no es un grupo, lo tratamos como un supervisor individual,
                // pero lo ponemos en un array para que 'whereIn' siempre funcione.
                $supervisorsToQuery = [$request->CodSupervisor];
            }

            // Unir con la tabla 'w004_zona' y aplicar la condición del supervisor
            $query->join("w004_zona as z", "fe.CodigoVendedor", "=", "z.CodVendedor")
                ->whereIn('z.CodSupervisor', $supervisorsToQuery); // <-- ¡CAMBIO CLAVE AQUÍ!
        }

        $facturasEmitidas = $query->first();

        return response()->json($facturasEmitidas);
    }

    public function getClientesCaptados(Request $request)
    {
        $query = DB::table("a010_clientes as ac")
            ->join("w004_zona as z", "ac.Vendedor", "=", "z.CodVendedor")
            ->select(DB::raw("COUNT(*) as ClientesCaptados"))
            // Usamos Carbon para el rango de fechas del mes actual, que es más legible y seguro
            ->whereBetween('ac.FechaIngreso', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ]);
        // Lógica para determinar los supervisores a buscar si se proporciona CodSupervisor
        if ($request->CodSupervisor && substr($request->CodSupervisor, 0, 1) === 'S') {
            $supervisorsToQuery = [];
            if (isset($this->supervisor_general[$request->CodSupervisor])) {
                // Si el CodSupervisor solicitado es un grupo (ej. S02), usa todos los códigos del grupo
                $supervisorsToQuery = $this->supervisor_general[$request->CodSupervisor];
            } else {
                // Si no es un grupo, lo tratamos como un supervisor individual,
                // pero lo ponemos en un array para que 'whereIn' siempre funcione.
                $supervisorsToQuery = [$request->CodSupervisor];
            }
            // Aplicamos el filtro de supervisor usando whereIn
            $query->whereIn('z.CodSupervisor', $supervisorsToQuery); // <-- ¡CAMBIO CLAVE AQUÍ!
        }

        $clientesCaptados = $query->first();

        return response()->json($clientesCaptados);
    }

    public function getTopClientes(Request $request)
    {
        // --- INICIO: Lógica para determinar $supervisorsToQuery ---
        $supervisorsToQuery = []; // Inicializamos

        // Si se proporciona CodSupervisor y comienza con 'S'
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


        // --- Modificación de la cláusula whereRaw existente ---
        $whereRaw = "f.CodigoVendedor NOT IN ('" . implode("','", $this->excludedVendors) . "')";

        // Si hay un CodSupervisor válido (individual o de grupo), añadimos la condición
        // Ahora usamos $supervisorsToQuery para construir la parte de CodSupervisor
        if (!empty($supervisorsToQuery)) { // Verificamos si hay supervisores para filtrar
            $whereRaw .= " AND z.CodSupervisor IN ('" . implode("','", $supervisorsToQuery) . "')";
        }
        // --- Fin de modificación de whereRaw ---


        $maxFacturadoSubquery = DB::table(DB::raw("(SELECT codcliente, SUM(TotalNeto) AS Facturado, DATE_FORMAT(FechaDocumento, '%Y-%m') AS Mes FROM e100_FacturaEncabezado WHERE YEAR(FechaDocumento) = 2024 GROUP BY codcliente, DATE_FORMAT(FechaDocumento, '%Y-%m')) as subquery"))
            ->select('codcliente', 'Facturado', 'Mes')
            ->whereIn(DB::raw('(codcliente, Facturado)'), function ($query) {
                $query->select(DB::raw('codcliente, MAX(Facturado)'))
                    ->from(DB::raw("(SELECT codcliente, SUM(TotalNeto) AS Facturado, DATE_FORMAT(FechaDocumento, '%Y-%m') AS Mes FROM e100_FacturaEncabezado WHERE YEAR(FechaDocumento) = 2024 GROUP BY codcliente, DATE_FORMAT(FechaDocumento, '%Y-%m')) as inner_query"))
                    ->groupBy('codcliente');
            });

        $query = DB::table('e100_FacturaEncabezado as f')
            ->join('w004_zona as z', 'f.CodigoVendedor', '=', 'z.CodVendedor')
            ->leftJoinSub($maxFacturadoSubquery, 'maximo_facturado', function ($join) {
                $join->on('f.codcliente', '=', 'maximo_facturado.codcliente');
            })
            ->leftJoin(DB::raw('
            (SELECT "2024-01" AS Mes, "Enero" AS NomMes
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
            UNION ALL SELECT "2024-12", "Diciembre") as Meses'), 'maximo_facturado.Mes', '=', 'Meses.Mes')
            ->whereBetween('f.FechaDocumento', [
                Carbon::now()->startOfMonth(), // Mejorado con Carbon
                Carbon::now()->endOfMonth()    // Mejorado con Carbon
            ])
            ->whereRaw($whereRaw) // Aquí usamos la cadena $whereRaw construida dinámicamente
            ->select(
                'f.codcliente as CodigoCliente',
                'f.nombrecli as NombreCliente',
                'z.CodVendedor as CodigoVendedor',
                'z.Nombre as NombreVendedor',
                'z.Sector',
                'z.CodSupervisor',
                DB::raw('IFNULL(SUM(f.TotalNeto), 0) AS Facturado'),
                DB::raw('IFNULL(maximo_facturado.Facturado, 0) AS Maximo_Facturado'),
                DB::raw('COALESCE(Meses.NomMes, "N/A") AS Mes')
            )
            ->groupBy('f.codcliente', 'f.nombrecli', 'z.CodVendedor', 'z.Nombre', 'z.Sector', 'z.CodSupervisor', 'maximo_facturado.Facturado', 'Meses.NomMes') // Agrupar por todas las columnas seleccionadas que no son agregadas
            ->orderBy('z.CodVendedor')
            ->orderBy('Facturado', 'DESC');

        if ($request->gen === 'xls') {
            $query = $query->get();
        } else {
            $query = $query->paginate(20);
        }

        return response()->json($query);
    }

    public function getVendedoresConZonaPorSupervisor(Request $request)
{
    $codSup = $request->CodSupervisor;

    // 1) Armo el array de supervisores a consultar
    if ($codSup && substr($codSup, 0, 1) === 'S') {
        // Si hay hijos definidos para este supervisor, incluyo todos
        $supervisorsToQuery = $this->supervisor_general[$codSup] 
            ?? [$codSup];
    } else {
        // Si no es supervisor válido, devolvemos vacío o todos según necesidad
        $supervisorsToQuery = [];
    }

    // 2) Construyo la consulta con whereIn si corresponde
    $qb = DB::table('w004_zona')
        ->select('*')
        ->where('CodVendedor', '!=', 'CONSTRU-FE')
        ->orderBy('Sector', 'ASC')
        ->orderBy('Nombre', 'ASC');

    if (!empty($supervisorsToQuery)) {
        $qb->whereIn('CodSupervisor', $supervisorsToQuery);
    } else {
        // Si quisieras que un no-supervisor viera todos:
        // $qb->whereNotNull('CodSupervisor');
        // O bien, devolver vacío:
        // return response()->json([]);
    }

    $vendedores = $qb->get();

    return response()->json($vendedores);
}



    public function getVxCVendedor(Request $request)
    {
        // VERT
        $vert = DB::table('e110_FacturaDetalle as fd')
            ->join('w004_zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->where('z.CodVendedor', '=', $request->CodVendedor)
            ->whereBetween('fd.fechadoc', [
                DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00'), INTERVAL 0 MONTH)"),
                DB::raw("DATE_SUB(DATE_ADD(DATE_FORMAT(NOW(), '%Y-%m-01 23:59:59'), INTERVAL 1 MONTH), INTERVAL 1 DAY)"),
            ])
            ->whereNotIn('fd.Grupo', ['001', '002', '008', '021', '022', '024', '026', '027', 'GRANEL'])
            ->selectRaw('IFNULL(SUM(fd.Subtotal), 0) as TotalVendidoVert, COUNT(codigo) as TotalProductosVert')
            ->first();

        // INGCO
        $ingco = DB::table('e110_FacturaDetalle as fd')
            ->join('w004_zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->where('z.CodVendedor', '=', $request->CodVendedor)
            ->whereBetween('fd.fechadoc', [
                DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00'), INTERVAL 0 MONTH)"),
                DB::raw("DATE_SUB(DATE_ADD(DATE_FORMAT(NOW(), '%Y-%m-01 23:59:59'), INTERVAL 1 MONTH), INTERVAL 1 DAY)"),
            ])
            ->where('fd.Grupo', '021')
            ->selectRaw('IFNULL(SUM(fd.Subtotal), 0) as TotalVendidoIngco, COUNT(codigo) as TotalProductosIngco')
            ->first();

        // FLEXIMATIC
        $fleximatic = DB::table('e110_FacturaDetalle as fd')
            ->join('w004_zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->where('z.CodVendedor', '=', $request->CodVendedor)
            ->whereBetween('fd.fechadoc', [
                DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00'), INTERVAL 0 MONTH)"),
                DB::raw("DATE_SUB(DATE_ADD(DATE_FORMAT(NOW(), '%Y-%m-01 23:59:59'), INTERVAL 1 MONTH), INTERVAL 1 DAY)"),
            ])
            ->whereIn('fd.Grupo', ['008', '022'])
            ->selectRaw('IFNULL(SUM(fd.Subtotal), 0) as TotalVendidoFleximatic, COUNT(codigo) as TotalProductosFleximatic')
            ->first();

        // QUILOSA
        $quilosa = DB::table('e110_FacturaDetalle as fd')
            ->join('w004_zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->where('z.CodVendedor', '=', $request->CodVendedor)
            ->whereBetween('fd.fechadoc', [
                DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00'), INTERVAL 0 MONTH)"),
                DB::raw("DATE_SUB(DATE_ADD(DATE_FORMAT(NOW(), '%Y-%m-01 23:59:59'), INTERVAL 1 MONTH), INTERVAL 1 DAY)"),
            ])
            ->whereIn('fd.Grupo', ['002', '024'])
            ->selectRaw('IFNULL(SUM(fd.Subtotal), 0) as TotalVendidoQuilosa, COUNT(codigo) as TotalProductosQuilosa')
            ->first();

        // CORONA
        $corona = DB::table('e110_FacturaDetalle as fd')
            ->join('w004_zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->where('z.CodVendedor', '=', $request->CodVendedor)
            ->whereBetween('fd.fechadoc', [
                DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00'), INTERVAL 0 MONTH)"),
                DB::raw("DATE_SUB(DATE_ADD(DATE_FORMAT(NOW(), '%Y-%m-01 23:59:59'), INTERVAL 1 MONTH), INTERVAL 1 DAY)"),
            ])
            ->where('fd.Grupo', '026')
            ->selectRaw('IFNULL(SUM(fd.Subtotal), 0) as TotalVendidoCorona, COUNT(codigo) as TotalProductosCorona')
            ->first();

        // WADFOW
        $wadfow = DB::table('e110_FacturaDetalle as fd')
            ->join('w004_zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->where('z.CodVendedor', '=', $request->CodVendedor)
            ->whereBetween('fd.fechadoc', [
                DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00'), INTERVAL 0 MONTH)"),
                DB::raw("DATE_SUB(DATE_ADD(DATE_FORMAT(NOW(), '%Y-%m-01 23:59:59'), INTERVAL 1 MONTH), INTERVAL 1 DAY)"),
            ])
            ->where('fd.Grupo', '027')
            ->selectRaw('IFNULL(SUM(fd.Subtotal), 0) as TotalVendidoWadfow, COUNT(codigo) as TotalProductosWadfow')
            ->first();

        // IMOU
        $imou = DB::table('e110_FacturaDetalle as fd')
            ->join('w004_zona as z', 'fd.CodigoVendedor', '=', 'z.CodVendedor')
            ->where('z.CodVendedor', '=', $request->CodVendedor)
            ->whereBetween('fd.fechadoc', [
                DB::raw("DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00'), INTERVAL 0 MONTH)"),
                DB::raw("DATE_SUB(DATE_ADD(DATE_FORMAT(NOW(), '%Y-%m-01 23:59:59'), INTERVAL 1 MONTH), INTERVAL 1 DAY)"),
            ])
            ->where('fd.Grupo', '001')
            ->selectRaw('IFNULL(SUM(fd.Subtotal), 0) as TotalVendidoImou, COUNT(codigo) as TotalProductosImou')
            ->first();

        return response()->json(["vert" => $vert, "ingco" => $ingco, "fleximatic" => $fleximatic, "quilosa" => $quilosa, "corona" => $corona, "wadfow" => $wadfow, "imou" => $imou]);
    }
}
