<?php

namespace App\Http\Controllers\Api;

// Controllers
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseController as BaseController;

// Models
use App\Models\Articulos;
use App\Models\PresupuestosEncabezado;
use App\Models\PresupuestosDetalle;
use App\Models\TempPresupuestosEncabezado;
use App\Models\TempPresupuestosDetalle;
use App\Models\PedidoDetalle;
use App\Models\PedidoEncabezado;
use App\Models\AuditoriaPagina;

// Query filters
use App\Filters\V1\Vendedor\ArticulosFilter;
use App\Filters\V1\Vendedor\PresupuestoFilter;
use App\Filters\V1\Vendedor\ProdPresupuestosFilter;

// Resources
use App\Http\Resources\Vendedor\Articulos\ArticulosResource;
use App\Http\Resources\Vendedor\Articulos\ArticulosCollection;
use App\Http\Resources\Vendedor\Presupuestos\PresupuestosResource;
use App\Http\Resources\Vendedor\Presupuestos\PresupuestosCollection;
use App\Http\Resources\Vendedor\Presupuestos\ProdPresupuestosResource;
use App\Http\Resources\Vendedor\Presupuestos\ProdPresupuestosCollection;
use Carbon\Carbon;
// Http and support
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresupuestosController extends Controller
{
    public function getPresupuestos(Request $request) //obtener presupuesto
    {
        $filter = new PresupuestoFilter();
        $filterItems = $filter->transform($request);

        $presupuestos = PresupuestosEncabezado::where($filterItems)->orderByDesc('FechaPresupuesto');
        return new PresupuestosCollection($presupuestos->paginate(15)->appends($request->query()));
    }

    public function getPresupuestosPorDocumento(Request $request) //obtener presupuesto por documento
    {
        $presupuesto = DB::table('w010_PresupuestoEncabezado')
            ->where('Documento', 'LIKE', '%' . $request->Documento . '%')
            ->where('Vendedor', '=', $request->Vendedor)
            ->orderBy('FechaPresupuesto', 'desc')
            ->paginate(15);

        return response()->json($presupuesto);
    }

    public function getFormaPago(Request $request)
    {
        $formapago = DB::table('a027_FormaPago')
            ->get();
        return response()->json($formapago);
    }

    public function getFormaPago2(Request $request)
    {
        $formasPago = DB::table('a027_FormaPago')
            ->select('Tipo', 'FormaPago') // Selecciona ambas columnas
            ->get(); // Ejecuta la consulta y obtiene los resultados
        return response()->json($formasPago);
    }

    public function getPresupuestoPorFecha(Request $request) //obtener presupuesto por fecha
    {
        $presupuesto = DB::table('w010_PresupuestoEncabezado')
            ->where('Vendedor', '=', $request->Vendedor)
            ->whereBetween('FechaPresupuesto', [$request->fechaInicio, $request->fechaFin])
            ->orderBy('FechaPresupuesto', 'desc')
            ->paginate(15);

        return response()->json($presupuesto);
    }

    public function getPresupuestoPorCliente(Request $request) //funcion para obtener presupuesto por cliente
    {
        $presupuesto = DB::table('w010_PresupuestoEncabezado')
            ->where('NombreCliente', 'LIKE', '%' . $request->NombreCliente . '%')
            ->where('Vendedor', '=', $request->Vendedor)
            ->orderBy('FechaPresupuesto', 'desc')
            ->paginate(15);

        return response()->json($presupuesto);
    }

    public function getProductosPresupuestos(Request $request) //funcion para obtener el detalle del presupuesto
    {
        $totalRegistros = PresupuestosDetalle::where('Documento', $request->Documento)->count();

        $productos = PresupuestosDetalle::from('w020_PresupuestoDetalle as pd')
            ->leftJoin('a020_articulos as aa', function ($join) {
                $join->on('pd.Codigo', '=', 'aa.Codigo')
                    ->on('pd.Agencia', '=', 'aa.Empresa');
            })
            ->select(
                'pd.Documento',
                'pd.Agencia',
                'pd.CodigoCliente',
                'pd.Codigo',
                DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(pd.Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"),
                'pd.ListaPrecio',
                'pd.PrecioUnit',
                'pd.Cantidad',
                'pd.Subtotal',
                'pd.Convertido',
                'aa.VentaMinima',
                'aa.UnidEmpaque',
                'aa.Grupo',
                'aa.Subgrupo',
                'aa.RutaImagen',
                'aa.Empresa'
            )
            ->where('pd.Documento', $request->Documento)
            ->orderBy('aa.orden', 'ASC')
            ->orderBy('aa.Nombre', 'ASC')
            ->paginate($totalRegistros);

        return response()->json($productos);
    }

    public function getProductosPresupuestos2(Request $request) //funcion para obtener los presupuesto y determinar si son de lubricantes o no
    {
        $presupuesto = DB::table('w010_PresupuestoEncabezado')
            ->select('Lubricante')
            ->where('Documento', $request->Documento) // Elimina el '='
            ->get();

        return response()->json($presupuesto);
    }

    public function getSubtotalPresupuesto(Request $request)
    {
        // Validar que el 'Documento' esté presente en la solicitud
        if (!$request->has('Documento')) {
            return response()->json(['error' => 'El parámetro "Documento" es requerido.'], 400);
        }

        $documento = $request->Documento;

        // Paso 1: Calcular el Subtotal Total
        $subtotalCalculado = DB::table('w020_PresupuestoDetalle as pd')
            ->where('pd.Documento', '=', $documento)
            ->sum('pd.Subtotal');

        // Paso 2: Contar la Cantidad de Productos Únicos
        $cantidadProductosUnicos = DB::table('w020_PresupuestoDetalle as pd')
            ->where('pd.Documento', '=', $documento)
            ->select(DB::raw("COUNT(DISTINCT CONCAT(pd.Codigo, '-', pd.Agencia)) as CantProductos"))
            ->value('CantProductos');

        // Preparar el objeto con los resultados
        $subtotal = [
            'Subtotal' => $subtotalCalculado ?? 0,
            'CantProductos' => $cantidadProductosUnicos ?? 0,
        ];

        // Empaquetar el objeto dentro de un array para la respuesta esperada
        return response()->json([$subtotal]); // ¡Este es el cambio clave!
    }

    public function getProductosPresupuestosPDF(Request $request)
    {
        $productos = DB::table('w020_PresupuestoDetalle as pd')
            ->leftJoin("w050_inventario_codBarras_nueva as b", 'pd.codigo', '=', 'b.codigo')
            ->select('pd.Documento', 'pd.Agencia', 'pd.CodigoCliente', 'pd.Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(pd.Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"), 'pd.ListaPrecio', 'pd.PrecioUnit', 'pd.Cantidad', 'pd.Subtotal', 'pd.Convertido', DB::raw("GROUP_CONCAT(b.codalternativo) as `codbarra`"))
            ->where('pd.Documento', '=', $request->Documento)
            ->groupBy('pd.Codigo', 'pd.Agencia', 'pd.Documento', 'pd.CodigoCliente', 'pd.Nombre', 'pd.ListaPrecio', 'pd.PrecioUnit', 'pd.Cantidad', 'pd.Subtotal', 'pd.Convertido')
            ->orderBy('pd.Nombre', 'ASC')
            ->paginate(1000);

        return response()->json($productos);
    }

    public function getProductoByCodigo(Request $request)
    {
        // Determinar empresas según zona de venta
        $empresas = $request->ZonasVenta === '001,003,004,CCS'
            ? ['001', '003', '004', 'CCS']
            : ['001', '003', '004'];

        // Cuántos resultados por página
        $vista   = $request->vistaFlag;
        $busqueda = $vista !== '1' ? 102 : 60;

        // Construir el query builder
        $query = DB::table('a020_articulos')
            ->select(
                'Codigo',
                DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"),
                'Precio1',
                'Precio2',
                'Precio3',
                'Precio4',
                'Precio5',
                'Existencia',
                'RutaImagen',
                'VentaMinima',
                'UnidEmpaque',
                'Grupo',
                'Subgrupo',
                'Empresa'
            )
            ->whereIn('Empresa', $empresas)
            ->where('Codigo', 'LIKE', '%' . $request->Codigo . '%');

        // Filtrar por lubricante (032)
        if ($request->lubricante === 'SI') {
            $query->where('Grupo', '=', '032');
        } elseif ($request->lubricante === 'NO') {
            $query->where('Grupo', '!=', '032');
        }

        // Ejecutar la paginación
        $productos = $query
            ->orderBy('orden', 'ASC')
            ->paginate($busqueda);

        // Recorrer los resultados y añadir la última compra de cada producto
        foreach ($productos as $producto) {
            $ultimaCompra = DB::table('e110_FacturaDetalle')
                ->select('Cantidad', 'fechadoc as FechaHora')
                ->where('Codigo', $producto->Codigo)
                ->where('CodCliente', $request->rif)
                ->orderBy('FechaHora', 'desc')
                ->first();

            $producto->Cantidad  = $ultimaCompra->Cantidad  ?? null;
            $producto->FechaHora = $ultimaCompra->FechaHora ?? null;
        }

        return response()->json($productos);
    }


    public function getProductoByNombre(Request $request) //obtener los productos por nombre
    {
        $ZonasVentas = $request->ZonasVenta;
        $empresas = $ZonasVentas === '001,003,004,CCS' ? ['001', '003', '004', 'CCS'] : ['001', '003', '004'];
        $lubricante = $request->lubricante;
        $vista = $request->vistaFlag;
        $busqueda = $vista !== '1' ? 102 : 60;

        $productos = DB::table('a020_articulos')
            ->select(
                'Codigo',
                DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"),
                'Precio1',
                'Precio2',
                'Precio3',
                'Precio4',
                'Precio5',
                'Existencia',
                'RutaImagen',
                'VentaMinima',
                'UnidEmpaque',
                'Grupo',
                'Subgrupo',
                'Empresa'
            )
            ->whereIn('Empresa', $empresas)
            ->where('Nombre', 'LIKE', '%' . $request->Nombre . '%');

        if ($lubricante === 'SI') {
            $productos->where('Grupo', '=', '032');
        } elseif ($lubricante === 'NO') {
            $productos->where('Grupo', '!=', '032');
        }

        $productos = $productos->orderBy('orden', 'ASC')->paginate($busqueda);


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

    public function convertPresupuesto(Request $request) //convierte presupuesto a pedido y valida que no existan duplicados
    {
        set_time_limit(120);
        $presupuestoEncabezado = PresupuestosEncabezado::where('Documento', $request->Documento)->first();
        if ($presupuestoEncabezado->Convertido === 1) {
            return response()->json(["status" => 201, "message" => "El presupuesto ya fue convertido a pedido."], 200);
        }

        $tipoDocumento = $request->formaFiscal;
        $docSuffix     = $tipoDocumento === "NotaEntrega" ? "P0N" : "P0F";
        $doc           = str_replace('PR0', $docSuffix, $request->Documento);
        $tipoDoc       = $tipoDocumento === "NotaEntrega" ? "NOTA DE ENTREGA" : "FACTURA";

        DB::beginTransaction();
        try {
            // Obtengo todos los detalles del presupuesto
            $presupuestoDetalles = PresupuestosDetalle::where('Documento', $request->Documento)->get();

            // ——— Aquí empieza el cambio SOLO EN ENCABEZADOS ———
            // Mapear todas las agencias: CCS se queda CCS, el resto pasa a MAR, y luego unicidad
            $agencias = $presupuestoDetalles
                ->pluck('Agencia')
                ->map(function ($a) {
                    return $a === 'CCS' ? 'CCS' : 'MAR';
                })
                ->unique();

            $pedidoEncabezadosInsert = [];
            foreach ($agencias as $agencia) {
                // Contar líneas según el mapeo
                $lineasCount = $presupuestoDetalles
                    ->filter(function ($d) use ($agencia) {
                        return ($d->Agencia === 'CCS' ? 'CCS' : 'MAR') === $agencia;
                    })
                    ->count();


                $pedidoEncabezadoData = [
                    'Documento'            => $doc,
                    'Codcliente'           => $presupuestoEncabezado->Codcliente,
                    'NombreCliente'        => $presupuestoEncabezado->NombreCliente,
                    'Vendedor'             => $presupuestoEncabezado->Vendedor,
                    'TipoPedido'           => $tipoDoc,
                    'FormaPago'            => $presupuestoEncabezado->FormaPago,
                    'fechayhora'           => now(),
                    'NumeroOrden'          => '-',
                    'Responsable'          => $request->NombreVendedor,
                    'Comentarios'          => $request->Comentario,
                    'Descargado'           => 0,
                    'Monto'                => $presupuestoEncabezado->Monto,
                    'AplicaDescuento'      => $presupuestoEncabezado->FormaPago === 'PREPAGO' ? 1 : 0,
                    'Equipo'               => '-',
                    'Version'              => '-',
                    'Descuento'            => $presupuestoEncabezado->Descuento,
                    'DiasPromocion'        => $presupuestoEncabezado->DiasPromocion,
                    'Agencia'              => $agencia,                  // ya mapeada a CCS o MAR
                    'Lineas'               => $lineasCount,
                    'FormaPagoDescuento'   => $request->FormaPagoDescuento,
                ];
                $pedidoEncabezadosInsert[] = $pedidoEncabezadoData;
            }
            // ——— Fin del cambio en encabezados ———

            // Preparar datos para bulk insert de detalles (SIN CAMBIOS)
            $pedidoDetallesInsert = [];
            $now = now();
            foreach ($presupuestoDetalles as $detalle) {
                $pedidoDetallesInsert[] = [
                    'Documento'    => $doc,
                    'Agencia'      => $detalle->Agencia,
                    'CodigoCliente' => $detalle->CodigoCliente,
                    'Codigo'       => $detalle->Codigo,
                    'Nombre'       => $detalle->Nombre,
                    'ListaPrecio'  => $detalle->ListaPrecio,
                    'PrecioUnit'   => $detalle->PrecioUnit,
                    'Cantidad'     => $detalle->Cantidad,
                    'Subtotal'     => $detalle->Subtotal,
                    'FechaHora'    => $now,
                    'Descargado'   => 0,
                ];
            }

            // Bulk insert
            PedidoDetalle::insert($pedidoDetallesInsert);
            PedidoEncabezado::insert($pedidoEncabezadosInsert);

            // Actualizar Presupuesto como Convertido
            PresupuestosEncabezado::where('Documento', $request->Documento)
                ->update(['Convertido' => 1]);
            PresupuestosDetalle::where('Documento', $request->Documento)
                ->update(['Convertido' => 1]);

            DB::commit();

            return response()->json([
                "message"   => "Presupuesto convertido a pedido exitosamente.",
                "Documento" => $doc,
                "status"    => 200,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => "Error al convertir el presupuesto a pedido: " . $e->getMessage(),
                "status"  => 500,
            ], 500);
        }
    }


    public function postPresupuestoEncabezado(Request $request) //inserta el encabezado del presupuesto
    {
        DB::beginTransaction();
        if ($request->Documento) {
            $documento = $request->Documento;
        } else {
            $lastID = DB::table('a001_correlativos')->value('Presupuestos') + 1;
            $prefijo = 'PR';
            $documento = $prefijo . str_pad($lastID, 6, '0', STR_PAD_LEFT);
        }

        try {
            $encabezado = new PresupuestosEncabezado();
            $encabezado->Documento = $documento;
            $encabezado->Codcliente = $request->Codcliente;
            $encabezado->NombreCliente = $request->NombreCliente;
            $encabezado->Vendedor = $request->Vendedor;
            $encabezado->FormaPago = $request->FormaPago;
            $encabezado->FechaPresupuesto = $request->FechaPresupuesto;
            $encabezado->Monto = $request->Monto;
            $encabezado->Convertido = $request->Convertido;
            $encabezado->Descuento = $request->Descuento;
            $encabezado->DiasPromocion = $request->DiasPromocion;
            $encabezado->TipoPromocion = $request->TipoPromocion;
            $encabezado->MontoPromocion = $request->MontoPromocion;
            $encabezado->Lubricante = $request->Lubricante;
            $encabezado->save();

            DB::table('a001_correlativos')->increment('Presupuestos');

            DB::commit();
            return response()->json($encabezado);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error al procesar el encabezado del presupuesto', 'error' => $e->getMessage()], 500);
        }
    }

    public function postPresupuestoDetalle(Request $request) //inserta el detalle del presupuesto
    {
        $detalle = $request->input('detalle');

        if (!is_array($detalle)) {
            return response()->json(['message' => 'Error: Detalle no es un array válido'], 400);
        }

        $detalleInsertar = [];
        foreach ($detalle as $item) {
            $detalleInsertar[] = [
                'Documento' => $request->input('Documento'),
                'Agencia' => $item['Agencia'],
                'CodigoCliente' => $request->input('CodigoCliente'),
                'Codigo' => $item['Codigo'],
                'Nombre' => $item['Nombre'],
                'ListaPrecio' => $item['PrecioLista'],
                'PrecioUnit' => $item['Precio'],
                'Cantidad' => $item['Unidades'],
                'Subtotal' => $item['SubTotal'],
                'Convertido' => 0,
                'Grupo' => $item['Grupo'],
                'Subgrupo' => $item['Subgrupo'],
            ];
        }

        PresupuestosDetalle::insert($detalleInsertar);

        return response()->json(['message' => 'Detalle del presupuesto insertado correctamente']);
    }

    public function deletePresupuestoEncabezadoDetalle(Request $request)
    {
        $deleteEncabezado = DB::table('w010_PresupuestoEncabezado')
            ->where('Documento', '=', $request->Documento)
            ->where('Codcliente', '=', $request->Codcliente)
            ->delete();

        $deleteDetalle = DB::table('w020_PresupuestoDetalle')
            ->where('Documento', '=', $request->Documento)
            ->where('CodigoCliente', '=', $request->Codcliente)
            ->delete();

        return response()->json(["Encabezado" => $deleteEncabezado, "Detalle" => $deleteDetalle]);
    }

    public function updatePrices(Request $request) //actualiza los precios y valida que no existan duplicados
    {
        $getListaPrecio = DB::table('a010_clientes')
            ->select('ListaPrecios')
            ->where('Codigo', '=', $request->rif)
            ->get();

        $Precio = '';

        switch ($getListaPrecio[0]->ListaPrecios) {
            case 1:
                $Precio = 'aa.precio1';
                break;

            case 2:
                $Precio = 'aa.precio2';
                break;

            case 3:
                $Precio = 'aa.precio3';
                break;

            case 4:
                $Precio = 'aa.precio4';
                break;

            case 5:
                $Precio = 'aa.precio5';
                break;

            default:
                $Precio = 'aa.precio1';
                break;
        }

        $actualizarPrecios = DB::statement("UPDATE w020_PresupuestoDetalle AS pd JOIN a020_articulos AS aa ON pd.Codigo = aa.Codigo SET pd.PrecioUnit = " . $Precio . ", pd.Subtotal = (" . $Precio . " * pd.Cantidad) WHERE pd.Documento = '" . $request->Documento . "'");
        $actualizarGrupos = DB::statement("UPDATE w020_PresupuestoDetalle AS pd JOIN a020_articulos AS aa ON pd.Codigo = aa.Codigo SET pd.Grupo = aa.Grupo, pd.Subgrupo = aa.Subgrupo WHERE pd.Documento = '" . $request->Documento . "'");

        $sumaDetalle = DB::table('w020_PresupuestoDetalle')
            ->select(DB::raw('SUM(Subtotal) as Subtotal'))
            ->where('Documento', '=', $request->Documento)
            ->get();

        $consultaEncabezado = DB::statement("UPDATE w010_PresupuestoEncabezado AS pe JOIN w020_PresupuestoDetalle AS pd ON pe.Documento = pd.Documento SET pe.Monto = IF(pe.FormaPago = 'PREPAGO', (" . $sumaDetalle[0]->Subtotal . " - (" . $sumaDetalle[0]->Subtotal . " * 0.1)), " . $sumaDetalle[0]->Subtotal . ") WHERE pd.Documento = '" . $request->Documento . "'");

        // Get duplicate documents
        $duplicateDocuments = PresupuestosEncabezado::select('Documento', DB::raw('COUNT(*) as count'))
            ->groupBy('Documento')
            ->having('count', '>', 1)
            ->pluck('Documento');

        // Delete duplicate records
        foreach ($duplicateDocuments as $document) {
            PresupuestosEncabezado::where('Documento', $document)
                ->limit(1)
                ->delete();
        }

        // Identify duplicate records based on multiple fields
        $duplicateRecords = PresupuestosDetalle::select(
            'Documento',
            'Agencia',
            'CodigoCliente',
            'Codigo',
            'Nombre',
            'ListaPrecio',
            'PrecioUnit',
            'Cantidad',
            'Subtotal',
            'Convertido',
            'Grupo',
            'Subgrupo',
            DB::raw('COUNT(*) as count')
        )
            ->groupBy('Documento', 'Agencia', 'CodigoCliente', 'Codigo', 'Nombre', 'ListaPrecio', 'PrecioUnit', 'Cantidad', 'Subtotal', 'Convertido', 'Grupo', 'Subgrupo')
            ->having('count', '>', 1)
            ->get();

        // Delete duplicate records
        foreach ($duplicateRecords as $duplicate) {
            PresupuestosDetalle::where('Documento', $duplicate->Documento)
                ->where('Agencia', $duplicate->Agencia)
                ->where('CodigoCliente', $duplicate->CodigoCliente)
                ->where('Codigo', $duplicate->Codigo)
                ->where('Nombre', $duplicate->Nombre)
                ->where('ListaPrecio', $duplicate->ListaPrecio)
                ->where('PrecioUnit', $duplicate->PrecioUnit)
                ->where('Cantidad', $duplicate->Cantidad)
                ->where('Subtotal', $duplicate->Subtotal)
                ->where('Convertido', $duplicate->Convertido)
                ->where('Grupo', $duplicate->Grupo)
                ->where('Subgrupo', $duplicate->Subgrupo)
                ->limit($duplicate->count - 1)
                ->delete();
        }


        return response()->json(["message" => "Precios actualizados correctamente a Lista de Precio " . $getListaPrecio[0]->ListaPrecios, "estado" => 200, "value" => $actualizarPrecios], 200);
    }

    public function copyPresupuestosToTemp(Request $request) //copiar presupuesto a temporal
    {
        $encabezado = DB::statement("INSERT INTO temp_w010_PresupuestoEncabezado SELECT * FROM w010_PresupuestoEncabezado as pe WHERE pe.Documento = '" . $request->Documento . "'");
        $detalle = DB::statement("INSERT INTO temp_w020_PresupuestoDetalle SELECT * FROM w020_PresupuestoDetalle as pd WHERE pd.Documento = '" . $request->Documento . "'");

        return response()->json(["Encabezado" => $encabezado, "Detalle" => $detalle]);
    }
}
