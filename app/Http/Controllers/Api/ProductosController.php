<?php

namespace App\Http\Controllers\Api;

// Controllers
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseController as BaseController;

// Http and support
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use function PHPUnit\Framework\isEmpty;

class ProductosController extends Controller
{
    //funcion para obtener los productos por zona
    public function getClientesAndZonaByCodigo(Request $request)
    {
        $ZonasVentas = $request->ZonasVenta;
        $empresas = $ZonasVentas === '001,003,004,CCS' ? ['001', '003', '004', 'CCS'] : ['001', '003', '004'];
        $productos = DB::table('a020_articulos')
            ->select('*')
            ->where('Codigo', 'LIKE', '%' . $request->CodigoProducto . '%')
            ->whereIn('Empresa', $empresas)
            ->groupBy('Codigo')
            ->orderBy('Subgrupo', 'ASC')
            ->orderBy('Grupo', 'ASC')
            ->orderBy('Nombre', 'ASC')
            ->paginate(50);

        return response()->json(["productos" => $productos]);
    }

    //funcion para obtener los productos por nombre
    public function getClientesAndZonaByNombre(Request $request)
    {
        // $productos = DB::table('e210_FacturaDetalle as fd')
        //     ->select('fd.Cliente', 'zc.NombreCliente', 'zc.DireccionDespacho', 'zc.SubZona', 'fd.Codigo as CodProducto', 'fd.Nombre as NomProducto', 'fd.Cantidad', 'fd.fechadoc', 'aa.RutaImagen')
        //     ->join('z031_zonasclientes as zc', 'fd.Cliente', '=', 'zc.CodigoCliente')
        //     ->join('a020_articulos as aa', 'fd.Codigo', '=', 'aa.Codigo')
        //     ->where('fd.Nombre', 'LIKE', '%' . $request->NombreProducto . '%')
        //     ->groupBy('fd.Cliente')
        //     ->orderBy('fd.fechadoc', 'DESC')
        //     ->orderBy('fd.Nombre', 'ASC')
        //     ->paginate(50);
        $ZonasVentas = $request->ZonasVenta;
        $empresas = $ZonasVentas === '001,003,004,CCS' ? ['001', '003', '004', 'CCS'] : ['001', '003', '004'];
        $productos = DB::table('a020_articulos')
            ->select('*')
            ->where('Nombre', 'LIKE', '%' . $request->NombreProducto . '%')
            ->whereIn('Empresa', $empresas)
            ->groupBy('Codigo')
            ->orderBy('Subgrupo', 'ASC')
            ->orderBy('Grupo', 'ASC')
            ->orderBy('Nombre', 'ASC')
            ->paginate(50);

        // $zonas = DB::table('e210_FacturaDetalle as fd')
        //     ->select('zc.Zona')
        //     ->join('z031_zonasclientes as zc', 'fd.Cliente', '=', 'zc.CodigoCliente')
        //     ->join('a020_articulos as aa', 'fd.Codigo', '=', 'aa.Codigo')
        //     ->where('fd.Nombre', 'LIKE', '%' . $request->NombreProducto . '%')
        //     ->groupBy('zc.Zona')
        //     ->orderBy('zc.Zona', 'ASC')
        //     ->paginate(50);

        return response()->json(["productos" => $productos]);
    }

    //funcion para obtener  productos y clientes por zona
    public function getClientesByZonaCodigoAndTerm(Request $request)
    {
        $ZonasVentas = $request->ZonasVenta;
        $empresas = $ZonasVentas === '001,003,004,CCS' ? ['001', '003', '004', 'CCS'] : ['001', '003', '004'];
        if ($request->CodUsuario === 'MARKETINGBELMENY') {
            if ($request->Zona === 'empty') {
                $productos = DB::table('e110_FacturaDetalle as fd')
                    ->select('fd.Cliente', 'zc.NombreCliente', 'zc.DireccionDespacho', 'zc.SubZona', 'fd.Codigo as CodProducto', 'fd.Nombre as NomProducto', 'fd.Cantidad', 'fd.fechadoc', 'aa.RutaImagen', 'aa.Existencia', 'aa.UnidEmpaque')
                    ->join('w004_zonas_clientes as zc', 'fd.Cliente', '=', 'zc.CodigoCliente')
                    ->leftJoin('a020_articulos as aa', 'fd.Codigo', '=', 'aa.Codigo')
                    ->where('aa.Codigo', 'LIKE', '%' . $request->CodigoProducto . '%')
                    ->whereIn('aa.Empresa', $empresas)
                    ->groupBy('fd.Cliente')
                    ->orderBy('fd.fechadoc', 'DESC')
                    ->paginate(50);
            } else {
                $productos = DB::table('e110_FacturaDetalle as fd')
                    ->select('fd.Cliente', 'zc.NombreCliente', 'zc.DireccionDespacho', 'zc.SubZona', 'fd.Codigo as CodProducto', 'fd.Nombre as NomProducto', 'fd.Cantidad', 'fd.fechadoc', 'aa.RutaImagen', 'aa.Existencia', 'aa.UnidEmpaque')
                    ->join('w004_zonas_clientes as zc', 'fd.Cliente', '=', 'zc.CodigoCliente')
                    ->leftJoin('a020_articulos as aa', 'fd.Codigo', '=', 'aa.Codigo')
                    ->where('zc.Zona', '=', $request->Zona)
                    ->where('aa.Codigo', 'LIKE', '%' . $request->CodigoProducto . '%')
                    ->whereIn('aa.Empresa', $empresas)
                    ->groupBy('fd.Cliente')
                    ->orderBy('fd.fechadoc', 'DESC')
                    ->paginate(50);
            }
        } else {
            $productos = DB::table('a020_articulos')
                ->select('*')
                ->where('Codigo', 'LIKE', '%' . $request->CodigoProducto . '%')
                ->whereIn('Empresa', $empresas)
                ->groupBy('Codigo')
                ->orderBy('Subgrupo', 'ASC')
                ->orderBy('Grupo', 'ASC')
                ->orderBy('Nombre', 'ASC')
                ->paginate(50);
        }

        return response()->json($productos);
    }

    public function getClientesByZonaNombreAndTerm(Request $request)
    {
        $ZonasVentas = $request->ZonasVenta;
        $empresas = $ZonasVentas === '001,003,004,CCS' ? ['001', '003', '004', 'CCS'] : ['001', '003', '004'];
        if ($request->CodUsuario === 'MARKETINGBELMENY') {
            if ($request->Zona === 'empty') {
                $productos = DB::table('e110_FacturaDetalle as fd')
                    ->select('fd.Cliente', 'zc.NombreCliente', 'zc.DireccionDespacho', 'zc.SubZona', 'fd.Codigo as CodProducto', 'fd.Nombre as NomProducto', 'fd.Cantidad', 'fd.fechadoc', 'aa.RutaImagen', 'aa.Existencia', 'aa.UnidEmpaque')
                    ->join('w004_zonas_clientes as zc', 'fd.Cliente', '=', 'zc.CodigoCliente')
                    ->leftJoin('a020_articulos as aa', 'fd.Codigo', '=', 'aa.Codigo')
                    ->where('aa.Nombre', 'LIKE', '%' . $request->NombreProducto . '%')
                    ->whereIn('aa.Empresa', $empresas)
                    ->groupBy('fd.Cliente')
                    ->orderBy('fd.fechadoc', 'DESC')
                    ->paginate(50);
            } else {
                $productos = DB::table('e110_FacturaDetalle as fd')
                    ->select('fd.Cliente', 'zc.NombreCliente', 'zc.DireccionDespacho', 'zc.SubZona', 'fd.Codigo as CodProducto', 'fd.Nombre as NomProducto', 'fd.Cantidad', 'fd.fechadoc', 'aa.RutaImagen', 'aa.Existencia', 'aa.UnidEmpaque')
                    ->join('w004_zonas_clientes as zc', 'fd.Cliente', '=', 'zc.CodigoCliente')
                    ->leftJoin('a020_articulos as aa', 'fd.Codigo', '=', 'aa.Codigo')
                    ->where('zc.Zona', '=', $request->Zona)
                    ->where('aa.Nombre', 'LIKE', '%' . $request->NombreProducto . '%')
                    ->whereIn('aa.Empresa', $empresas)
                    ->groupBy('fd.Cliente')
                    ->orderBy('fd.fechadoc', 'DESC')
                    ->paginate(50);
            }
        } else {
            $productos = DB::table('a020_articulos')
                ->select('*')
                ->where('Nombre', 'LIKE', '%' . $request->NombreProducto . '%')
                ->whereIn('Empresa', $empresas)
                ->groupBy('Codigo')
                ->orderBy('Subgrupo', 'ASC')
                ->orderBy('Grupo', 'ASC')
                ->orderBy('Nombre', 'ASC')
                ->paginate(50);
        }

        return response()->json($productos);
    }
    //obtener los productos y sus fotos
    public function getArticulosAndFotos()
    {
        $articulos = DB::table('a020_articulos as aa')
            ->select('aa.Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(aa.Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre_nuevo"), 'aa.RutaImagen')
            ->orderBy('aa.Subgrupo', 'ASC')
            ->orderBy('aa.Nombre', 'ASC')
            ->get();

        return response()->json($articulos);
    }
    //obtener los productos en cada una de sus categorias, o los vendidos a un cliente
    public function getTopProductos(Request $request)
    {
        $ZonasVentas = $request->ZonasVenta;
        $empresas = $ZonasVentas === '001,003,004,CCS' ? ['001', '003', '004', 'CCS'] : ['001', '003', '004'];
        $whereParam = '';
        $condicion = '=';
        $vista = $request->vistaFlag;
        $busqueda = $vista !== '1' ? 300 : 102;

        if (str_contains($request->marca, 'top')) {
            switch ($request->marca) {

                /*-----------------VERT-----------------*/
                // ILUMINACIÓN
                case 'topBombillos':
                    $whereParam = "(Subgrupo between '03-001' AND '03-004') OR (Subgrupo = '03-008')";
                    break;

                case 'topCintasLED':
                    $whereParam = "(Subgrupo = '03-005') OR (Subgrupo = '03-029')";
                    break;

                case 'topLamparas':
                    $whereParam = "(Subgrupo = '03-009') OR (Subgrupo = '03-006') OR (Subgrupo BETWEEN '03-016' and '03-020') OR (Subgrupo = '03-025')";
                    break;

                case 'topPaneles':
                    $whereParam = "(Subgrupo BETWEEN '03-010' and '03-015')";
                    break;

                case 'topTubos':
                    $whereParam = "(Subgrupo BETWEEN '03-021' and '03-024')";
                    break;

                case 'topAlumbrados':
                    $whereParam = "(Subgrupo = '03-026')";
                    break;

                case 'topReflectores':
                    $whereParam = "(Subgrupo = '03-027')";
                    break;

                case 'topLinternas':
                    $whereParam = "(Subgrupo = '03-028')";
                    break;

                //HOGAR
                case 'topHogar':
                    $whereParam = "(Subgrupo BETWEEN '11-001' and '11-007')";
                    break;

                case 'topBanos':
                    $whereParam = "(Subgrupo BETWEEN '10-001' and '10-004')";
                    break;

                case 'topJardineria':
                    $whereParam = "(Subgrupo BETWEEN '12-001' and '12-005')";
                    break;

                case 'topPlomeria':
                    $whereParam = "(Subgrupo BETWEEN '07-001' and '07-009') OR (Subgrupo BETWEEN '09-001' AND '09-002')";
                    break;

                case 'topCerraduras':
                    $whereParam = "(Subgrupo = '06-001')";
                    break;

                //FERRETERIA EN GENERAL
                case 'topFerreteriagral':
                    $whereParam = "(Subgrupo BETWEEN '05-001' and '05-009')";
                    break;

                //ELECTRICIDAD
                case 'topElectricidad':
                    $whereParam = "(Subgrupo BETWEEN '04-001' and '04-006')";
                    break;

                //AUTOMOTRIZ
                case 'topAutomotriz':
                    $whereParam = "(Subgrupo BETWEEN '13-001' and '13-005')";
                    break;

                //CONSTRUCCION
                case 'topConstruccion':
                    $whereParam = "(Subgrupo BETWEEN '16-001' and '16-006')";
                    break;

                //MISCELANEO
                case 'topMiscelaneo':
                    $whereParam = "(Subgrupo BETWEEN '14-001' and '15-003')";
                    break;

                /*-----------------IMOU-----------------*/
                case 'topImou':
                    $whereParam = "(Subgrupo BETWEEN '01-001' and '01-003')";
                    break;

                /*-----------------FLEXIMATIC-----------------*/
                case 'topFleximatic':
                    $whereParam = "(Subgrupo BETWEEN '08-001' and '08-005')";
                    break;

                /*-----------------QUILOSA-----------------*/
                case 'topQuilosa':
                    $whereParam = "(Subgrupo BETWEEN '02-001' and '02-005')";
                    break;

                /*-----------------INGCO-----------------*/
                //HERRAMIENTAS ELECTRICAS
                case 'topElectricas':
                    $whereParam = "(Subgrupo BETWEEN '021-01' and '021-02')";
                    break;

                //BOMBAS DE AGUA
                case 'topBombas-agua':
                    $whereParam = "(Subgrupo = '021-03')";
                    break;

                //MECHAS
                case 'topMechas':
                    $whereParam = "(Subgrupo = '021-04')";
                    break;

                //CONSUMIBLES
                case 'topConsumibles':
                    $whereParam = "(Subgrupo = '021-05')";
                    break;

                //HERRAMIENTAS AISLADAS
                case 'topAisladas':
                    $whereParam = "(Subgrupo = '021-06')";
                    break;

                //HERRAMIENTAS MANUALES
                case 'topManuales':
                    $whereParam = "(Subgrupo BETWEEN '021-07' and '021-08')";
                    break;

                //HERRAMIENTAS DE MEDICION
                case 'topMedicion':
                    $whereParam = "(Subgrupo = '021-09')";
                    break;

                //HERRAMIENTAS NEUMATICAS
                case 'topNeumaticas':
                    $whereParam = "(Subgrupo = '021-10')";
                    break;

                //GATOS HIDRAULICOS
                case 'topHidraulicos':
                    $whereParam = "(Subgrupo = '021-11')";
                    break;

                //ACCESORIOS
                case 'topAccesorios':
                    $whereParam = "(Subgrupo = '021-12')";
                    break;

                //JARDINERIA
                case 'topJardineria-ingco':
                    $whereParam = "(Subgrupo = '021-13')";
                    break;

                //ACCESORIOS PARA PINTAR
                case 'topPintura':
                    $whereParam = "(Subgrupo = '021-14')";
                    break;

                //BOLSO DE HERRAMIENTAS
                case 'topBolsos':
                    $whereParam = "(Subgrupo = '021-15')";
                    break;

                //SEGURIDAD INDUSTRIAL
                case 'topSeguridad':
                    $whereParam = "(Subgrupo = '021-16')";
                    break;

                //BOTAS DE SEGURIDAD
                case 'topBotas':
                    $whereParam = "(Subgrupo = '021-17')";
                    break;

                //CANDADOS
                case 'topCandados':
                    $whereParam = "(Subgrupo = '021-18')";
                    break;

                //LINTERNAS
                case 'topLinternas-ingco':
                    $whereParam = "(Subgrupo = '021-19')";
                    break;
            }

            if ($request->catalogo === 'general') {
                $productos = DB::table('a020_articulos as aa')
                    ->join('e110_FacturaDetalle as fd', 'fd.codigo', '=', 'aa.codigo')
                    ->select('aa.Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(aa.Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"), DB::raw('SUM(fd.cantidad) as Cantidad'), 'aa.Existencia', 'fd.PrecioUnitario', 'aa.UnidEmpaque', 'aa.VentaMinima', 'aa.Grupo', 'aa.Subgrupo', 'aa.Empresa', 'aa.Grupo', 'aa.Subgrupo')
                    ->whereRaw('fd.fechadoc BETWEEN "' . $request->fechaInicio . '" AND "' . $request->fechaFin . '"')
                    ->groupBy('aa.codigo')
                    ->orderBy('Cantidad', 'DESC')
                    ->paginate($busqueda);
            } else {
                $productos = DB::table('a020_articulos as aa')
                    ->join('e110_FacturaDetalle as fd', 'fd.codigo', '=', 'aa.codigo')
                    ->select('aa.Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(aa.Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"), DB::raw('SUM(fd.cantidad) as CantidadTotal'), 'aa.Precio1', 'aa.Precio2', 'aa.Precio3', 'aa.Precio4', 'aa.Precio5', 'aa.Existencia', 'aa.UnidEmpaque', 'aa.VentaMinima', 'aa.Grupo', 'aa.Subgrupo', 'aa.Empresa', 'aa.Grupo', 'aa.Subgrupo')
                    ->groupBy('aa.codigo')
                    ->whereRaw($whereParam)
                    ->whereIn('aa.Empresa', $empresas)
                    ->whereRaw('fd.fechadoc BETWEEN "' . $request->fechaInicio . '" AND "' . $request->fechaFin . '"')
                    ->orderBy('CantidadTotal', 'DESC')
                    ->paginate($busqueda);

                foreach ($productos as $producto) {
                    $compra = DB::table('e110_FacturaDetalle')
                        ->select('Cantidad', 'fechadoc as FechaHora')
                        ->where('Codigo', $producto->Codigo)
                        ->where('CodCliente', $request->rif)
                        ->orderBy('FechaHora', 'desc')
                        ->first();

                    if ($compra) {
                        $producto->Cantidad = $compra->Cantidad;
                        $producto->FechaHora = $compra->FechaHora;
                    } else {
                        // Si no se encontró una compra, establecer los valores a null o algún valor predeterminado
                        $producto->Cantidad = null;
                        $producto->FechaHora = null;
                    }
                }
            }

            return response()->json($productos);
        } else {
            if ($request->marca == 'INGCO') {
                $condicion = '=';
            } else if ($request->marca == 'VERT') {
                $condicion = '<>';
            }

            // $productos = DB::table('a020_articulos as aa')
            //     ->select('aa.Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(aa.Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"), DB::raw('SUM(fd.Cantidad) as Cantidad'), 'aa.Existencia', 'fd.PrecioUnitario', 'aa.UnidEmpaque', 'aa.VentaMinima', 'aa.Grupo', 'aa.Subgrupo', 'aa.Empresa')
            //     ->whereIn('Empresa', $empresas)
            //     ->join('e110_FacturaDetalle as fd', 'fd.Codigo', '=', 'aa.Codigo')
            //     ->where('aa.grupo', $condicion, '021')
            //     ->whereRaw('fd.fechadoc BETWEEN date_add(date_add(LAST_DAY(NOW()), INTERVAL 1 DAY), INTERVAL -2 MONTH) and NOW()')
            //     ->groupBy('aa.Codigo',DB::raw('SUM(fd.Cantidad) as CantidadTotal'))
            //     ->orderBy('Cantidad', 'DESC')
            //     ->paginate($busqueda);

            $productos = DB::table('e110_FacturaDetalle')
                ->select('Codigo', DB::raw('SUM(Cantidad) as CantidadTotal'))
                ->whereIn('Agencia', $empresas)
                ->where('Grupo', $condicion, '021')
                ->whereRaw('fechadoc BETWEEN date_add(LAST_DAY(NOW()), INTERVAL 1 DAY)+ INTERVAL -2 MONTH and NOW()')
                ->groupBy('Codigo')
                ->orderBy('CantidadTotal', 'desc')
                ->paginate($busqueda);

            foreach ($productos as $producto) {
                $articulo = DB::table('a020_articulos')
                    ->select('Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"), 'Precio1', 'Precio2', 'Precio3', 'Precio4', 'Precio5', 'Existencia', 'RutaImagen', 'VentaMinima', 'UnidEmpaque', 'Grupo', 'Subgrupo', 'Empresa')
                    ->where('Codigo', $producto->Codigo)
                    ->whereIn('Empresa', $empresas)
                    ->first();

                $compra = DB::table('e110_FacturaDetalle')
                    ->select('Cantidad', 'fechadoc as FechaHora')
                    ->where('Codigo', $producto->Codigo)
                    ->where('CodCliente', $request->rif)
                    ->orderBy('FechaHora', 'desc')
                    ->first();
                if ($articulo) {
                    if ($compra) {
                        $producto->Nombre = $articulo->Nombre;
                        $producto->Precio1 = $articulo->Precio1;
                        $producto->Precio2 = $articulo->Precio2;
                        $producto->Precio3 = $articulo->Precio3;
                        $producto->Precio4 = $articulo->Precio4;
                        $producto->Precio5 = $articulo->Precio5;
                        $producto->Existencia = $articulo->Existencia;
                        $producto->RutaImagen = $articulo->RutaImagen;
                        $producto->VentaMinima = $articulo->VentaMinima;
                        $producto->UnidEmpaque = $articulo->UnidEmpaque;
                        $producto->Grupo = $articulo->Grupo;
                        $producto->Subgrupo = $articulo->Subgrupo;
                        $producto->Empresa = $articulo->Empresa;
                        $producto->Cantidad = $compra->Cantidad;
                        $producto->FechaHora = $compra->FechaHora;
                    } else {
                        $producto->Nombre = $articulo->Nombre;
                        $producto->Precio1 = $articulo->Precio1;
                        $producto->Precio2 = $articulo->Precio2;
                        $producto->Precio3 = $articulo->Precio3;
                        $producto->Precio4 = $articulo->Precio4;
                        $producto->Precio5 = $articulo->Precio5;
                        $producto->Existencia = $articulo->Existencia;
                        $producto->RutaImagen = $articulo->RutaImagen;
                        $producto->VentaMinima = $articulo->VentaMinima;
                        $producto->UnidEmpaque = $articulo->UnidEmpaque;
                        $producto->Grupo = $articulo->Grupo;
                        $producto->Subgrupo = $articulo->Subgrupo;
                        $producto->Empresa = $articulo->Empresa;
                        $producto->Cantidad = null;
                        $producto->FechaHora = null;
                    }
                } else {
                    // Si no se encontró una compra, establecer los valores a null o algún valor predeterminado
                    $producto->Nombre = null;
                    $producto->FechaHora = null;
                }
            }
            return response()->json($productos);
        }
    }

    //funcion para obtener los top productos recomendados
    public function getTopProductos2(Request $request)
    {
        $ZonasVentas = $request->ZonasVenta;
        $empresas = $ZonasVentas === '001,003,004,CCS' ? ['001', '003', '004', 'CCS'] : ['001', '003', '004'];
        $condicion = '=';
        $marca = Str::of($request->marca)->contains('INGCO');
        $nocomprado = Str::of($request->marca)->contains('nocomprado');
        $comprado = Str::of($request->marca)->contains('Comprado');
        $vista = $request->vistaFlag;
        $busqueda = $vista !== '1' ? 300 : 102;

        if ($marca == true) {
            $condicion = '=';
        } else {
            $condicion = '<>';
        }
        if ($comprado == true) {
            $productos = DB::table('a021_toparticulos as a')
                ->join('e110_FacturaDetalle as b', 'b.Codigo', '=', 'a.Codigo')
                ->join('a020_articulos as c', 'c.Codigo', '=', 'a.Codigo')
                ->select('c.Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(c.Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"), 'c.Precio1', 'c.Precio2', 'c.Precio3', 'c.Precio4', 'c.Existencia', 'c.UnidEmpaque', 'c.VentaMinima', 'c.Promocion', 'c.RutaImagen', 'c.Empresa')
                ->where('b.CodCliente', $request->rif)
                ->whereIn('c.Empresa', $empresas)
                ->where('c.Grupo', $condicion, '021')
                ->groupBy('c.codigo', 'c.empresa')
                ->orderBy('c.orden', 'ASC')
                ->orderBy('c.Nombre', 'ASC')
                ->paginate($busqueda);

            foreach ($productos as $producto) {
                $compra = DB::table('e110_FacturaDetalle')
                    ->select('Cantidad', 'fechadoc as FechaHora')
                    ->where('Codigo', $producto->Codigo)
                    ->where('CodCliente', $request->rif)
                    ->orderBy('FechaHora', 'desc')
                    ->first();

                if ($compra) {
                    $producto->Cantidad = $compra->Cantidad;
                    $producto->FechaHora = $compra->FechaHora;
                } else {
                    // Si no se encontró una compra, establecer los valores a null o algún valor predeterminado
                    $producto->Cantidad = null;
                    $producto->FechaHora = null;
                }
            }
            return response()->json($productos);
        } else if ($nocomprado == true) {
            $rif = $request->rif;
            $compra = DB::table('a021_toparticulos as a')
                ->leftJoin('e110_FacturaDetalle as b', function ($join) use ($rif) {
                    $join->on('a.codigo', '=', 'b.codigo')
                        ->where('b.CodCliente', $rif);
                })
                ->whereNull('b.codigo')
                ->where('a.Grupo', $condicion, '021')
                ->select('a.codigo')
                ->get()
                ->pluck("codigo");

            $productos = DB::table('a020_articulos as aa')
                ->select('aa.Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(aa.Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"), 'aa.Precio1', 'aa.Precio2', 'aa.Precio3', 'aa.Precio4', 'aa.Existencia', 'aa.UnidEmpaque', 'aa.VentaMinima', 'aa.Promocion', 'aa.RutaImagen', 'aa.Empresa')
                ->whereIn("Codigo", $compra)
                ->whereIn('aa.Empresa', $empresas)
                ->orderBy('aa.orden', 'ASC')
                ->orderBy('aa.Nombre', 'ASC')
                ->paginate($busqueda);

            return response()->json($productos);
        } else {
            $productos = DB::table('a020_articulos as aa')
                ->select('aa.Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(aa.Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"), 'aa.Precio1', 'aa.Precio2', 'aa.Precio3', 'aa.Precio4', 'aa.Existencia', 'aa.UnidEmpaque', 'aa.VentaMinima', 'aa.Promocion', 'aa.RutaImagen', 'aa.Empresa', 'aa.Grupo', 'aa.Subgrupo')
                ->join('a021_toparticulos as b', 'b.Codigo', '=', 'aa.Codigo')
                ->whereIn('aa.Empresa', $empresas)
                ->where('aa.Grupo', $condicion, '021')
                ->orderBy('aa.orden', 'ASC')
                ->orderBy('aa.Nombre', 'ASC')
                ->paginate($busqueda);

            foreach ($productos as $producto) {
                $compra = DB::table('e110_FacturaDetalle')
                    ->select('Cantidad', 'fechadoc as FechaHora')
                    ->where('Codigo', $producto->Codigo)
                    ->where('CodCliente', $request->rif)
                    ->orderBy('FechaHora', 'desc')
                    ->first();

                if ($compra) {
                    $producto->Cantidad = $compra->Cantidad;
                    $producto->FechaHora = $compra->FechaHora;
                } else {
                    // Si no se encontró una compra, establecer los valores a null o algún valor predeterminado
                    $producto->Cantidad = null;
                    $producto->FechaHora = null;
                }
            }
        }
        return response()->json($productos);
    }

    //funcion para obtener los top productos recomendados para generar el excel
    public function getTopProductos2XLS(Request $request)
    {
        ini_set('max_execution_time', 180);
        $ZonasVentas = $request->ZonasVenta;
        $empresas = $ZonasVentas === '001,003,004,CCS' ? ['001', '003', '004', 'CCS'] : ['001', '003', '004'];
        $condicion = '=';
        $marca = Str::of($request->marca)->contains('INGCO');
        $nocomprado = Str::of($request->marca)->contains('nocomprado');
        $comprado = Str::of($request->marca)->contains('Comprado');
        if ($marca == true) {
            $condicion = '=';
        } else {
            $condicion = '<>';
        }
        if ($comprado == true) {
            $productos = DB::table('a021_toparticulos as a')
                ->join('e110_FacturaDetalle as b', 'b.Codigo', '=', 'a.Codigo')
                ->join('a020_articulos as c', 'c.Codigo', '=', 'a.Codigo')
                ->select('c.Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(c.Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"), 'c.Precio1', 'c.Precio2', 'c.Precio3', 'c.Precio4', 'c.Existencia', 'c.UnidEmpaque', 'c.VentaMinima', 'c.Promocion', 'c.RutaImagen', 'c.Empresa', 'c.Grupo', 'c.Subgrupo')
                ->where('b.CodCliente', $request->rif)
                ->where('c.Grupo', $condicion, '021')
                ->whereIn('c.Empresa', $empresas)
                ->groupBy('c.codigo', 'c.empresa')
                ->orderBy('c.orden', 'ASC')
                ->orderBy('c.Nombre', 'ASC')
                ->get();

            foreach ($productos as $producto) {
                $compra = DB::table('e110_FacturaDetalle')
                    ->select('Cantidad', 'fechadoc as FechaHora')
                    ->where('Codigo', $producto->Codigo)
                    ->where('CodCliente', $request->rif)
                    ->orderBy('FechaHora', 'desc')
                    ->first();

                if ($compra) {
                    $producto->Cantidad = $compra->Cantidad;
                    $producto->FechaHora = $compra->FechaHora;
                } else {
                    // Si no se encontró una compra, establecer los valores a null o algún valor predeterminado
                    $producto->Cantidad = null;
                    $producto->FechaHora = null;
                }
            }
            return response()->json($productos);
        } else if ($nocomprado == true) {
            $rif = $request->rif;
            $compra = DB::table('a021_toparticulos as a')
                ->leftJoin('e110_FacturaDetalle as b', function ($join) use ($rif) {
                    $join->on('a.codigo', '=', 'b.codigo')
                        ->where('b.CodCliente', $rif);
                })
                ->whereNull('b.codigo')
                ->where('a.Grupo', $condicion, '021')
                ->select('a.codigo')
                ->get()
                ->pluck("codigo");

            $productos = DB::table('a020_articulos as aa')
                ->select('aa.Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(aa.Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"), 'aa.Precio1', 'aa.Precio2', 'aa.Precio3', 'aa.Precio4', 'aa.Existencia', 'aa.UnidEmpaque', 'aa.VentaMinima', 'aa.Promocion', 'aa.RutaImagen', 'aa.Empresa', 'aa.Grupo', 'aa.Subgrupo')
                ->whereIn("Codigo", $compra)
                ->whereIn('aa.Empresa', $empresas)
                ->orderBy('aa.orden', 'ASC')
                ->orderBy('aa.Nombre', 'ASC')
                ->get();

            return response()->json($productos);
        } else {
            $productos = DB::table('a021_toparticulos as b')
                ->select('aa.Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(aa.Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"), 'aa.Precio1', 'aa.Precio2', 'aa.Precio3', 'aa.Precio4', 'aa.Existencia', 'aa.UnidEmpaque', 'aa.VentaMinima', 'aa.Promocion', 'aa.RutaImagen', 'aa.Empresa', 'aa.Grupo', 'aa.Subgrupo')
                ->join('a020_articulos as aa', 'b.Codigo', '=', 'aa.Codigo')
                ->whereIn('aa.Empresa', $empresas)
                ->where('aa.Grupo', $condicion, '021')
                ->orderBy('aa.orden', 'ASC')
                ->orderBy('aa.Nombre', 'ASC')
                ->get();

            foreach ($productos as $producto) {
                $compra = DB::table('e110_FacturaDetalle')
                    ->select('Cantidad', 'fechadoc as FechaHora')
                    ->where('Codigo', $producto->Codigo)
                    ->where('CodCliente', $request->rif)
                    ->orderBy('FechaHora', 'desc')
                    ->first();

                if ($compra) {
                    $producto->Cantidad = $compra->Cantidad;
                    $producto->FechaHora = $compra->FechaHora;
                } else {
                    // Si no se encontró una compra, establecer los valores a null o algún valor predeterminado
                    $producto->Cantidad = null;
                    $producto->FechaHora = null;
                }
            }
        }
        return response()->json($productos);
    }

    public function getProductosEnPromocion(Request $request)
    {
        $ZonasVentas = $request->ZonasVenta;
        $empresas = $ZonasVentas === '001,003,004,CCS' ? ['001', '003', '004', 'CCS'] : ['001', '003', '004'];
        $productos = DB::table('a020_articulos as aa')
            ->select('aa.Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(aa.Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"), 'aa.Precio1', 'aa.Precio2', 'aa.Precio3', 'aa.Precio4', 'aa.Existencia', 'aa.UnidEmpaque', 'aa.VentaMinima', 'aa.Promocion', 'aa.RutaImagen', 'aa.Empresa')
            ->where('aa.Promocion', '=', 1)
            ->whereIn('aa.Empresa', $empresas)
            ->orderBy('aa.orden', 'ASC')
            ->orderBy('aa.Nombre', 'ASC')
            ->paginate(25);

        return response()->json($productos);
    }

    public function getGeneralProductos(Request $request)
    {
        $ZonasVentas = $request->ZonasVenta;
        $empresas = $ZonasVentas === '001,003,004,CCS' ? ['001', '003', '004', 'CCS'] : ['001', '003', '004'];
        $whereParam = '';
        $vista = $request->vistaFlag;
        $busqueda = $vista !== '1' ? 300 : 102;
        switch ($request->catalogo) {

            /*-----------------VERT-----------------*/
            // ILUMINACIÓN
            case 'generalVert':
                $whereParam = "(Grupo!='021') AND (Grupo!='027') AND (Grupo!='024') AND (Grupo!='022') AND (Grupo!='023') AND (Grupo!='032')";
                break;

            case 'corona':
                $whereParam = "Grupo='026'";
                break;

            case 'iluminacion':
                $whereParam = "(Grupo='008') OR (Grupo='0008')";
                break;

            case 'apagadores':
                $whereParam = "Grupo='035'";
                break;

            case 'electricidad':
                $whereParam = "Grupo='007'";
                break;

            case 'ferreteriagral':
                $whereParam = "Grupo='002'";
                break;

            case 'plomeria':
                $whereParam = "Grupo='005'";
                break;

            case 'griferia':
                $whereParam = "Grupo='006'";
                break;

            //HOGAR
            case 'hogar':
                $whereParam = "Grupo='013'";
                break;

            case 'banos':
                $whereParam = "Grupo='011'";
                break;

            case 'foami':
                $whereParam = "Grupo='028'";
                break;

            case 'jardineria':
                $whereParam = "Grupo='009'";
                break;

            //MISCELANEO
            case 'miscelaneos':
                $whereParam = "Grupo='018'";
                break;

            case 'tecnologia':
                $whereParam = "Grupo='017'";
                break;

            //AUTOMOTRIZ
            case 'automotriz':
                $whereParam = "Grupo='014'";
                break;
            case 'tecnologia':
                $whereParam = "Grupo='017'";
                break;


            //CONSTRUCCION
            case 'construccion':
                $whereParam = "Grupo='016'";
                break;
            /*-----------------IMOU-----------------*/
            case 'imou':
                $whereParam = "Grupo='023'";
                break;
            /*-----------------FLEXIMATIC-----------------*/
            case 'fleximatic':
                $whereParam = "Grupo='022'";
                break;

            /*-----------------QUILOSA-----------------*/
            case 'quilosa':
                $whereParam = "Grupo='024'";
                break;

            /*-----------------INGCO-----------------*/
            //HERRAMIENTAS ELECTRICAS
            case 'generalIngco':
                $whereParam = "Grupo='021'";
                break;

            case 'herramientas':
                $whereParam = "Grupo='021' AND (Subgrupo='21-11' OR Subgrupo='021-01' OR Subgrupo='021-04' OR Subgrupo='021-12' OR Subgrupo='021-05' OR Subgrupo='021-06' OR Subgrupo='021-11' OR Subgrupo='21-01')";
                break;

            //SEGURIDAD INDUSTRIAL
            case 'seguridad':
                $whereParam = "Grupo='021' AND (Subgrupo='021-19' OR Subgrupo='021-13' OR Subgrupo='021-14')";
                break;

            //BOMBAS DE AGUA
            case 'bombas':
                $whereParam = "Grupo='021' AND Subgrupo='021-02'";
                break;

            //GATOS HIDRAULICOS
            case 'hidraulicos':
                $whereParam = "Grupo='021' AND Subgrupo='021-07'";
                break;

            case 'jardineria-ingco':
                $whereParam = "Grupo='021' AND Subgrupo='021-09'";
                break;

            case 'linternas-ingco':
                $whereParam = "Grupo='021' AND Subgrupo='021-15'";
                break;

            //MECHAS
            case 'mechas':
                $whereParam = "Grupo='021' AND Subgrupo='21-031'";
                break;

            //CONSUMIBLES
            case 'consumibles':
                $whereParam = "Grupo='021' AND Subgrupo='021-03'";
                break;

            case 'accesorios':
                $whereParam = "Grupo='021' AND Subgrupo='021-08'";
                break;

            case 'pintura':
                $whereParam = "Grupo='021' AND Subgrupo='021-10'";
                break;
        }

        if ($request->catalogo === 'general') {
            $productos = DB::table('a020_articulos')
                ->select('*', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"), 'Empresa')
                ->whereIn('Empresa', $empresas)
                ->where('Grupo', '!=', '032')
                ->orderBy('orden', 'ASC')
                ->orderBy('Nombre', 'ASC')
                ->paginate($busqueda);
        } else {
            $productos = DB::table('a020_articulos')
                ->select('*', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"), 'Empresa')
                ->whereIn('Empresa', $empresas)
                ->whereRaw($whereParam)
                ->orderBy('orden', 'ASC')
                ->orderBy('Nombre', 'ASC')
                ->paginate($busqueda);
        }

        foreach ($productos as $producto) {
            $compra = DB::table('e110_FacturaDetalle')
                ->select('Cantidad', 'fechadoc as FechaHora')
                ->where('Codigo', $producto->Codigo)
                ->where('CodCliente', $request->rif)
                ->orderBy('FechaHora', 'desc')
                ->first();

            if ($compra) {
                $producto->Cantidad = $compra->Cantidad;
                $producto->FechaHora = $compra->FechaHora;
            } else {
                // Si no se encontró una compra, establecer los valores a null o algún valor predeterminado
                $producto->Cantidad = null;
                $producto->FechaHora = null;
            }
        }
        return response()->json($productos);
    }

    public function getListaPrecioCadenas(Request $request)
    {
        $empresas = $request->ZonasVenta === '001,003,004,CCS' ? ['001', '003', '004', 'CCS'] : ['001', '003', '004'];

        switch ($request->Marca) {
            case 'VERT':
                $marca = "aa.grupo <> '021' AND aa.grupo <> '027' AND aa.grupo <> '032'";
                break;

            case 'INGCO':
                $marca = "aa.grupo = '021'";
                break;

            case 'WADFOW':
                $marca = "aa.grupo = '027'";
                break;

            case 'PDVSA':
                $marca = "aa.grupo = '032'";
                break;

            default:
                $marca = "aa.grupo <> '021' AND aa.grupo <> '027' AND aa.grupo <> '032'";
                break;
        }

        if ($request->Vendedor === 'V67') {
            $listaPrecio = 'aa.precio1 as Precio';
        } else {
            $listaPrecio = 'aa.precio2 as Precio';
        }

        $listaCadenas = DB::table('a020_articulos as aa')
            ->leftJoin('w050_inventario_codBarras_nueva as wic', 'aa.codigo', '=', 'wic.codigo')
            ->select('aa.RutaImagen', 'aa.codigo as Codigo', 'aa.Nombre as Nombre', 'aa.precio2 as Precio', 'aa.existencia as Existencia', 'aa.VentaMinima', 'wic.codalternativo as CodigoBarras', 'aa.Empresa', 'aa.Grupo', 'aa.Subgrupo')
            ->whereRaw($marca)
            ->whereIn('aa.Empresa', $empresas)
            //->groupBy('aa.codigo')
            ->groupBy(
            'aa.RutaImagen',
            'aa.codigo',
            'aa.Nombre',
            'aa.precio2', // Include the actual price column used in the group
            'aa.existencia',
            'aa.VentaMinima',
            'aa.Empresa',
            'aa.Grupo',
            'aa.Subgrupo'
        )
            ->orderBy('aa.orden', 'ASC')
            ->orderBy('Nombre', 'ASC')
            ->get();

        return response()->json($listaCadenas);
    }
}
