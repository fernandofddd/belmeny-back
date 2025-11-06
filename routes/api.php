<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomUserController;
use App\Http\Controllers\Api\PedidosController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SupervisorController;
use App\Http\Controllers\Api\FacturasController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\CobranzasController;
use App\Http\Controllers\Api\ManifiestoController;
use App\Http\Controllers\Api\ExhibidorController;
use App\Http\Controllers\Api\ProductosController;
use App\Http\Controllers\Api\PresupuestosController;
use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\Api\MiscController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\Api\ExportListaPrecioCadenasController;
use App\Models\Image;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Auth
Route::post('registerOrUpdateSession', [AuthController::class, 'registerOrUpdateSession']);

//Pedidos
Route::get('getPedidos', [PedidosController::class, 'index']); // PARA OBTENER PEDIDOS EN GENERAL
Route::get('getPedidosxGerente', [PedidosController::class, 'getPedidosxGerente']); // PARA OBTENER LOS PEDIDOS X GERENTE
Route::get('getPedidosxCobranzasGerencia', [PedidosController::class, 'getPedidosxCobranzasGerencia']); // PARA OBTENER LOS PEDIDOS X GERENCIA
Route::get('getPedidosPorBusqueda', [PedidosController::class, 'getPedidosPorBusqueda']); // PARA OBTENER LOS PEDIDOS DEPENDIENDO DE LA BUSQUEDA REALIZADA
Route::get('getProductosPedidos', [PedidosController::class, 'getProductosPedido']); // PARA OBTENER LOS PRODUCTOS DE UN PEDIDO
Route::get('getProductosPedidosPDF', [PedidosController::class, 'getProductosPedidoPDF']); // PARA OBTENER LOS PRODUCTOS DE UN PEDIDO Y SACARLOS A PDF
Route::get('getMontoProductosPedido', [PedidosController::class, 'getMontoProductosPedido']); // PARA OBTENER EL MONTO DE LOS PRODUCTOS DE UN PEDIDO

// Pedidos Supervisor
Route::get('getPedidosPorSupervisor', [PedidosController::class, 'getPedidosPorSupervisor']); // PARA OBTENER LOS PEDIDOS X SUPERVISOR

//Facturas
Route::get('getFacturas', [FacturasController::class, 'index']); // PARA OBTENER LAS FACTURAS EN GENERAL
Route::get('getFacturasxGerente', [FacturasController::class, 'getFacturasxGerente']); // PARA OBTENER LAS FACTURAS X GERENTE
Route::get('getFacturasxCobranzasGerencia', [FacturasController::class, 'getFacturasxCobranzasGerencia']); // PARA OBTENER LAS FACTURAS X COBRANZAS
Route::get('getFacturasPorBusqueda', [FacturasController::class, 'getFacturasPorBusqueda']); // PARA OBTENER LAS FACTURAS DEPENDIENDO DE LA BUSQUEDA REALIZADA
Route::get('getProductosFactura', [FacturasController::class, 'getProductosFactura']);// PARA OBTENER LOS PRODUCTOS DE UNA FACTURA
Route::get('getCantidadProductos', [FacturasController::class, 'getCantidadProductos']); // PARA OBTENER LA CANTIDAD DE PRODUCTOS DE UNA FACTURA
Route::get('MontoTotalFacturas', [FacturasController::class, 'MontoTotalFacturas']); // PARA OBTENER EL MONTO DE LOS PRODUCTOS DE FACTURAS
Route::get('getProductosFacturaPDF', [FacturasController::class, 'getProductosFacturaPDF']); // PARA OBTENER LOS PRODUCTOS DE UNA FACTURA Y SACARLOS A PDF

// Facturas Supervisor
Route::get('getFacturasPorSupervisor', [FacturasController::class, 'getFacturasPorSupervisor']); // PARA OBTENER LAS FACTURAS X SUPERVISOR

// Clientes
Route::get('getClientes', [ClienteController::class, 'getClientes']); // PARA OBTENER LOS CLIENTES EN GENERAL
Route::get('getClientesXLSX', [ClienteController::class, 'getClientesXLSX']); // PARA SACAR REPORTE DE CLIENTES EN EXCEL
Route::get('getClientesRif', [ClienteController::class, 'getClientesRif']); // PARA OBTENER LOS CLIENTES POR RIF
Route::get('getClientesByVendedorAndRif', [ClienteController::class, 'getClientesByVendedorAndRif']); // PARA OBTENER LOS CLIENTES POR VENDEDOR Y RIF
Route::get('getClientesVendedor', [ClienteController::class, 'getClientesVendedor']); // PARA OBTENER LOS CLIENTES POR VENDEDOR
Route::get('getClientesVendedorFecha', [ClienteController::class, 'getClientesVendedorFecha']); // PARA OBTENER LOS CLIENTES POR VENDEDOR desde 2022
Route::get('getClientesVendedorFecha2', [ClienteController::class, 'getClientesVendedorFecha2']); // PARA OBTENER LOS CLIENTES POR VENDEDOR desde 2023
Route::get('getClientesVendedorFecha3', [ClienteController::class, 'getClientesVendedorFecha3']); // PARA OBTENER LOS CLIENTES POR VENDEDOR desde 2024
Route::get('getClientesVendedorFecha4', [ClienteController::class, 'getClientesVendedorFecha4']); // PARA OBTENER LOS CLIENTES POR VENDEDOR desde 2025
Route::get('getClientePorNombre', [ClienteController::class, 'getClientePorNombre']); // PARA OBTENER LOS CLIENTES POR NOMBRE
Route::get('getClientePorRif', [ClienteController::class, 'getClientePorRif']); // PARA OBTENER LOS CLIENTES POR NOMBRE
Route::get('getClientePorRif2', [ClienteController::class, 'getClientePorRif2']); // PARA OBTENER LOS CLIENTES POR NOMBRE GERENCIA
Route::get('getDeudasCliente', [ClienteController::class, 'getDeudasCliente']); // PARA OBTENER LAS DEUDAS DE LOS CLIENTES
Route::get('getVentasClientes', [ClienteController::class, 'getVentasClientes']); // PARA OBTENER LAS VENTASDE POR AÑO DE CLIENTE
Route::get('getVentasClienteIngco', [ClienteController::class, 'getVentasClienteIngco']); // PARA OBTENER LAS VENTASDE POR AÑO DE CLIENTE
Route::get('getVentasClienteVert', [ClienteController::class, 'getVentasClienteVert']); // PARA OBTENER LAS VENTAS DE POR AÑO DE CLIENTE
Route::get('getVentasVendedorIngco', [ClienteController::class, 'getVentasVendedorIngco']); // PARA OBTENER LAS VENTASDE POR AÑO DE CLIENTE
Route::get('getVentasVendedorVert', [ClienteController::class, 'getVentasVendedorVert']); // PARA OBTENER LAS VENTAS DE POR AÑO DE CLIENTE
Route::get('getVentasSupervisorIngco', [ClienteController::class, 'getVentasSupervisorIngco']); // PARA OBTENER LAS VENTASDE POR AÑO DE CLIENTE
Route::get('getVentasSupervisorVert', [ClienteController::class, 'getVentasSupervisorVert']); // PARA OBTENER LAS VENTAS DE POR AÑO DE CLIENTE
Route::get('getClientesAtendidosByVendedor', [ClienteController::class, 'getClientesAtendidosByVendedor']); // PARA OBTENER LOS CLIENTES ATENDIDOS POR VENDEDOR
Route::get('getClientesDesatendidosByVendedor', [ClienteController::class, 'getClientesDesatendidosByVendedor']); // PARA OBTENER LOS CLIENTES DESATENDIDOS O CON DEUDA POR VENDEDOR
Route::get('getClientesDeudoresXLSX', [ClienteController::class, 'getClientesDeudoresXLSX']); // PARA OBTENER LOS CLIENTES DESATENDIDOS O CON DEUDA POR VENDEDOR
Route::get('searchClientesbyRif', [ClienteController::class, 'searchClientesbyRif']); // PARA HACER LA BUSQUEDA DE CLIENTES POR RIF
Route::get('searchClientesbyNombre', [ClienteController::class, 'searchClientesbyNombre']); // PARA HACER LA BUSQUEDA DE CLIENTES POR NOMBRE
Route::get('getClientesxSupervisor', [ClienteController::class, 'getClientesxSupervisor']); // PARA OBTENER LOS CLIENTES X SUPERVISOR
Route::get('getDetalleClientesAtendidosxVendedor', [ClienteController::class, 'getDetalleClientesAtendidosxVendedor']); // PARA OBTENER EL DETALLE DE LOS CLIENTES ATENDIDOS X VENDEDOR
Route::get('getZonasClientes', [ClienteController::class, 'getZonasClientes']); // PARA OBTENER LAS ZONAS DE LOS CLIENTES
Route::get('getDetalleClientesPorCodigo', [ClienteController::class, 'getDetalleClientesPorCodigo']); // PARA OBTENER CLIENTES ATENDIDOS Y DESATENDIDOS GERENCIA

//Vendedor
Route::get('getUsuario', [DashboardController::class, 'getUsuario']); // Información del vendedor
Route::get('getInfoCobranza', [DashboardController::class, 'getInfoCobranza']); // Información de Cobranzas
Route::get('getMetaVendedor', [DashboardController::class, 'getMetasPorVendedor']); // Metas y progresos
Route::get('getClientesAtendidos', [DashboardController::class, 'getClientesAtendidos']); // Obtener clientes atendidos
Route::get('getPorcentajeVendedor', [DashboardController::class, 'getPorcentajeVendedor']); // Para obtener los porcentajes de las compras de los top 100 productos del vendedor

//Cobranzas x Cliente del Vendedor
Route::get('getCobranzasxCliente', [CobranzasController::class, 'getCobranzasxCliente']); // Informacion de todas las cobranzas de clientes por vendedor
Route::get('getCobranzasVendedor', [CobranzasController::class, 'getCobranzasVendedor']); // Informacion de todas las cobranzas de clientes por vendedor
Route::get('getCobranzasSupervisor2', [DashboardController::class, 'getCobranzasSupervisor2']);
Route::get('/getCobranzasxCliente2', [CobranzasController::class, 'getCobranzasxCliente2']);
Route::get('/getAllCobranzas2', [CobranzasController::class, 'getAllCobranzas2']);
Route::get('/getDetallesCobranzas2', [CobranzasController::class, 'getDetallesCobranzas2']);
Route::get('/getCobranzasPorBusqueda2', [CobranzasController::class, 'getCobranzasPorBusqueda2']);

//Supervisor
Route::get('getTopVendedoresZona', [DashboardController::class, 'getTopVendedoresZona']); // Tops por zona
Route::get('getTopVendedoresNacional', [DashboardController::class, 'getTopVendedoresNacional']); // Top nacional

// Exportar lista de precios cadenas con imágenes en Excel
Route::get('getListaPrecioCadenasXLSX', [ExportListaPrecioCadenasController::class, 'export']);


// Dashboard del supervisor
Route::get('getVentasZona', [DashboardController::class, 'getVentasZona']); // Para obtener las ventas por sector
Route::get('getTopVendedoresSupervisor', [DashboardController::class, 'getTopVendedoresSupervisor']); // Para obtener el top de ventas de vendedores por sector y supervisor
Route::get('getCobranzasVendedorSupervisor', [DashboardController::class, 'getCobranzasVendedorSupervisor']); // Para obtener las cobranzas pendientes y pagadas de los vendedores
Route::get('getCobranzasVendedorSupervisorGeneral', [DashboardController::class, 'getCobranzasVendedorSupervisorGeneral']); // Para obtener las cobranzas pendientes y pagadas de los vendedores
Route::get('getVendedoresXSupervisor', [DashboardController::class, 'getVendedoresXSupervisor']); // Para obtener la lista de vendedores x supervisor
Route::get('getZonasSupervisor', [DashboardController::class, 'getZonasSupervisor']); // Para obtener la lista de zonas x supervisor
Route::get('getCorteSemanalxZona', [DashboardController::class, 'getCorteSemanalxZona']); // Para obtener el corte semanal x zona x supervisor
Route::get('getClientesAtendidosxZona', [DashboardController::class, 'getClientesAtendidosxZona']); // Para obtener el corte semanal x zona x supervisor
Route::get('getTotalClientesxSupervisor', [DashboardController::class, 'getTotalClientesxSupervisor']); // Para obtener el corte semanal x zona x supervisor
Route::get('getCantidadVentasxGerente', [DashboardController::class, 'getCantidadVentasxGerente']); // Para obtener la cantidad de items vendidos y sus ventas x gerente
Route::get('getCantidadVentasxVendedor', [DashboardController::class, 'getCantidadVentasxVendedor']); // Para obtener la cantidad de items vendidos y sus ventas x Vendedor
Route::get('getCantidadVentasxVendedor2', [DashboardController::class, 'getCantidadVentasxVendedor2']); // Para obtener la cantidad de items vendidos y sus ventas x Vendedor
Route::get('getHistoricoMetas', [DashboardController::class, 'getHistoricoMetas']); // Para obtener la cantidad de items vendidos y sus ventas x gerente
Route::get('statusPedidos', [DashboardController::class, 'statusPedidos']); // Para obtener la cantidad de items vendidos y sus ventas x gerente
Route::get('getFacturadoAndCobradoHoyVsAyer', [DashboardController::class, 'getFacturadoAndCobradoHoyVsAyer']); // Para obtener la cantidad de items vendidos y sus ventas x gerente
Route::get('getCobranzasSupervisor', [DashboardController::class, 'getCobranzasSupervisor']); // Para obtener la cantidad de items vendidos y sus ventas x gerente
Route::get('getDetalleCobranzasSupervisor', [DashboardController::class, 'getDetalleCobranzasSupervisor']); // Para obtener la cantidad de items vendidos y sus ventas x gerente
Route::get('getDetalleCobranzasSupervisor2', [DashboardController::class, 'getDetalleCobranzasSupervisor2']); // Para obtener la cantidad de items vendidos y sus ventas x General y Cobranzas
Route::get('getParetos', [DashboardController::class, 'getParetos']); // Para obtener la cantidad de items vendidos y sus ventas x gerente
Route::get('getPorcentajeVendedores', [DashboardController::class, 'getPorcentajeVendedores']); // Para obtener los porcentajes de las compras de los top 100 productos por vendedor para un supervisor
Route::get('getPorcentajeSupervisor', [DashboardController::class, 'getPorcentajeSupervisor']); // Para obtener los porcentajes de las compras de los top 100 productos del supervisor
Route::get('getPorcentajeSupervisores', [DashboardController::class, 'getPorcentajeSupervisores']); // Para obtener los porcentajes de las compras de los top 100 productos por supervisor para un gerente
Route::get('getPorcentajeGerente', [DashboardController::class, 'getPorcentajeGerente']); // Para obtener los porcentajes de las compras de los top 100 productos del supervisor

// Rutas Supervisor
Route::get('getMetasSemanalVendedores', [SupervisorController::class, 'getMetasSemanalVendedores']); // Para obtener la cantidad de items vendidos y sus ventas x gerente
Route::get('getTop10Vendedores', [SupervisorController::class, 'getTop10Vendedores']); // Ranking top 10 vendedores
Route::get('obtenerFPAPByZona', [SupervisorController::class, 'obtenerFPAPByZona']); // Para obtener la cantidad de items vendidos y sus ventas x gerente
Route::get('getFPAPByZona', [SupervisorController::class, 'getFPAPByZona']); // Para obtener la cantidad de items vendidos y sus ventas x gerente
Route::get('getEstimaciones', [SupervisorController::class, 'getEstimaciones']); // Para obtener la cantidad de items vendidos y sus ventas x gerente
Route::get('getTotalFPC', [SupervisorController::class, 'getTotalFPC']); // Para obtener la cantidad de items vendidos y sus ventas x gerente
Route::get('getPedidosPorVendedor', [SupervisorController::class, 'getPedidosPorVendedor']); // Para obtener la cantidad de items vendidos y sus ventas x gerente
Route::get('getCobrosAnualesEnCurso', [SupervisorController::class, 'getCobrosAnualesEnCurso']); // Para obtener los cobros del año actual
Route::get('getFacturasVencidas', [SupervisorController::class, 'getFacturasVencidas']); // Para obtener los cobros del año actual
Route::get('getFacturasEmitidas', [SupervisorController::class, 'getFacturasEmitidas']); // Para obtener los cobros del año actual
Route::get('getClientesCaptados', [SupervisorController::class, 'getClientesCaptados']); // Para obtener los cobros del año actual
Route::get('getTopClientes', [SupervisorController::class, 'getTopClientes']); // Para obtener los cobros del año actual
Route::get('getVendedoresConZonaPorSupervisor', [SupervisorController::class, 'getVendedoresConZonaPorSupervisor']); // Para obtener los cobros del año actual
Route::get('getVxCVendedor', [SupervisorController::class, 'getVxCVendedor']); // Para obtener la cantidad de items vendidos y sus ventas x gerente
Route::get('getPedidosAyer', [SupervisorController::class, 'getPedidosAyer']); // Para obtener la cantidad de pedidos el dia anterior por gerente
// Ventas
Route::get('getVentasPasados', [DashboardController::class, 'getVentasPasados']); // Para obtener las ventas de año por vendedor
Route::get('getTopClientesVendedor', [DashboardController::class, 'getTopClientesVendedor']); // Para obtener los top clientes de un vendedor
Route::get('getPorcentajeClientes', [DashboardController::class, 'getPorcentajeClientes']); // Para obtener los porcentajes de las compras de los top 100 productos por clientes de un vendedor
Route::get('getPorcentajeClientesCompleto', [DashboardController::class, 'getPorcentajeClientesCompleto']); // Para obtener los porcentajes de las compras de los top 100 productos por clientes de un vendedor
Route::get('getVentasAnualesEnCurso', [DashboardController::class, 'getVentasAnualesEnCurso']); // Para obtener las ventas del año en curso
Route::get('getVentasAnualesEnCursoSupervisores', [DashboardController::class, 'getVentasAnualesEnCursoSupervisores']); // Para obtener las ventas del año en curso
Route::get('getVentasZonasPorYear', [DashboardController::class, 'getVentasZonasPorYear']); // Para obtener todas las ventas de los años seleccionados por zona
Route::get('getVentasZonasMensual', [DashboardController::class, 'getVentasZonasMensual']); // Para obtener las ventas anuales de la zona seleccionada
Route::get('getTopVendedoresPorYear', [DashboardController::class, 'getTopVendedoresPorYear']); // Para obtener el top nacional de vendedores
Route::get('getTopVendedoresYearZona', [DashboardController::class, 'getTopVendedoresYearZona']); //Y su zona
Route::get('getVentasNacionalesPorYear', [DashboardController::class, 'getVentasNacionalesPorYear']); // Para obtener las ventas nacionales por año
Route::get('getListaMetas', [DashboardController::class, 'getListaMetas']); // Lista de metas
Route::put('ActualizarMetas', [DashboardController::class, 'ActualizarMetas']); // Actualizar Metas
Route::get('getInfoCobranzaPorZona', [DashboardController::class, 'getInfoCobranzaPorZona']); // Cobranzas
Route::get('getVendedor', [ClienteController::class, 'getVendedor']); // Info Vendedor
Route::get('getClientesAtendidosByGerente', [ClienteController::class, 'getClientesAtendidosByGerente']); // Info Atendidos

// Tracking
Route::get('TrackPedidosVendedor', [TrackingController::class, 'TrackPedidosVendedor']); // Informacion de todos los tracking por vendedor
Route::get('getTrackingPorFecha', [TrackingController::class, 'getTrackingPorFecha']); // Informacion de todos los tracking por vendedor en el rango de fechas
Route::get('getTrackingDetalle', [TrackingController::class, 'getTrackingDetalle']); // Informacion de todos los tracking por vendedor en detalle
Route::get('getTrackingCliente', [TrackingController:: class, 'getTrackingCliente']); // Informacion de todos los tracking por vendedor por clientes
Route::get('getTrackingxZonaSupervisor', [TrackingController::class, 'getTrackingxZonaSupervisor']); // Informacion de todos los tracking general supervisor
Route::get('getTrackingxZonaSupervisorDocumento', [TrackingController::class, 'getTrackingxZonaSupervisorDocumento']); // Informacion de todos los tracking por vendedor por documento
Route::get('getTrackingxZonaSupervisorCliente', [TrackingController::class, 'getTrackingxZonaSupervisorCliente']); // Informacion de todos los tracking por vendedor por clientes
Route::get('getTrackingxZonaSupervisorFecha', [TrackingController::class, 'getTrackingxZonaSupervisorFecha']); // Informacion de todos los tracking por vendedor por clientes

//Cobranzas de cliente x vendedor
Route::get('getCobranzasxCliente', [CobranzasController::class, 'getCobranzasxCliente']); // Informacion de todas las cobranzas de clientes x vendedor
Route::get('getDetallesCobranzas', [CobranzasController::class, 'getDetallesCobranzas']); // Informacion del detalle de la cobranza solicitada
Route::get('getCobranzasPorBusqueda', [CobranzasController::class, 'getCobranzasPorBusqueda']); // Busqueda de la cobranza

// Master de cobranzas (Supervisor)
Route::get('getListaMasterCobranzas', [CobranzasController::class, 'getListaMasterCobranzas']); // Lista de vendedores y sus cobranzas
Route::get('getListaMasterCobranzasZona', [CobranzasController::class, 'getListaMasterCobranzasZona']); // Lista de vendedores y sus cobranzas POR ZONA
Route::get('getCobranzaVendedor', [CobranzasController::class, 'getCobranzaVendedor']); // Lista de vendedores y sus cobranzas POR ZONA
Route::get('getListaDocVencidos', [CobranzasController::class, 'getListaDocVencidos']); // Lista de vendedores y sus cobranzas POR ZONA
Route::get('getDellateDocVencido', [CobranzasController::class, 'getDellateDocVencido']); // Lista de vendedores y sus cobranzas POR ZONA
Route::get('getCobranzasVendedorXLSX', [CobranzasController::class, 'getCobranzasVendedorXLSX']); // Lista de cobranzas que faltan por cobrar para generar en excel

// Manifiesto
Route::get('getManifiestoVendedor', [ManifiestoController::class, 'getManifiestoVendedor']); // Información de los manifiestos
Route::get('searchManifiesto', [ManifiestoController::class, 'searchManifiesto']); // Información de los manifiestos
Route::get('getDetalleManifiesto', [ManifiestoController::class, 'getDetalleManifiesto']); // Información del detalle de los manifiestos
Route::get('getManifiestoxSupervisor', [ManifiestoController::class, 'getManifiestoxSupervisor']); // Información del detalle de los manifiestos.
Route::get('getDetalleManifiestoxSupervisor', [ManifiestoController::class, 'getDetalleManifiestoxSupervisor']); // Información del detalle de los manifiestos.
Route::get('searchManifiestoxSupervisor', [ManifiestoController::class, 'searchManifiestoxSupervisor']); // Información de los manifiestos

// Exhibidores
Route::post('postSolicitud', [ExhibidorController::class, 'postSolicitud']); // Registro de solicitudes para el buzón de exhibidores
Route::post('postPreSolicitud', [ExhibidorController::class, 'postPreSolicitud']); // Registro de solicitudes para el buzón de exhibidores
Route::get('getBuzon', [ExhibidorController::class, 'getBuzon']); // Información del buzón
Route::get('getBuzonPreSolicitud', [ExhibidorController::class, 'getBuzonPreSolicitud']); // Información del buzón
Route::get('getMessage', [ExhibidorController::class, 'getMessage']); // Información del buzón
Route::get('getMessagePreSolicitud', [ExhibidorController::class, 'getMessagePreSolicitud']); // Información del buzón

Route::put('actualizarLeidoVendedorPreSolicitud', [ExhibidorController::class, 'actualizarLeidoVendedorPreSolicitud']); // Actualizar información de leido de vendedores
Route::put('actualizarLeidoSupervisorPreSolicitud', [ExhibidorController::class, 'actualizarLeidoSupervisorPreSolicitud']); // Actualizar información de leido de supervisores
Route::put('actualizarLeidoGerenciaPreSolicitud', [ExhibidorController::class, 'actualizarLeidoGerenciaPreSolicitud']); // Actualizar información de leido de gerentes

Route::put('actualizarLeidoVendedor', [ExhibidorController::class, 'actualizarLeidoVendedor']); // Actualizar información de leido de vendedores
Route::put('actualizarLeidoSupervisor', [ExhibidorController::class, 'actualizarLeidoSupervisor']); // Actualizar información de leido de supervisores
Route::put('actualizarLeidoGerencia', [ExhibidorController::class, 'actualizarLeidoGerencia']); // Actualizar información de leido de gerentes

Route::put('AprovOrDeny', [ExhibidorController::class, 'AprovOrDeny']); // Actualizar información de aprobación o rechazo de solicitudes de exhibidores

Route::get('getClienteVentas6UltimosMeses', [ExhibidorController::class, 'getClienteVentas6UltimosMeses']); // Información del buzón

// Presupuestos
Route::get('getPresupuestos', [PresupuestosController::class, 'getPresupuestos']); // Obtener todos los presupuestos
Route::get('getFormaPago', [PresupuestosController::class, 'getFormaPago']); // Obtener todos las formas de pago
Route::get('getFormaPago2', [PresupuestosController::class, 'getFormaPago2']); // Obtener todos las formas de pago
Route::get('getPresupuestosPorDocumento', [PresupuestosController::class, 'getPresupuestosPorDocumento']); // Obtener todos los presupuestos por LIKE del documento
Route::get('getPresupuestoPorFecha', [PresupuestosController::class, 'getPresupuestoPorFecha']); // Obtener todos los presupuestos por cliente
Route::get('getPresupuestoPorCliente', [PresupuestosController::class, 'getPresupuestoPorCliente']); // Obtener todos los presupuestos por fecha
Route::get('getProductoByCodigo', [PresupuestosController::class, 'getProductoByCodigo']); // Obtener los productos por codigo unicamente
Route::get('getProductoByNombre', [PresupuestosController::class, 'getProductoByNombre']); // Obtener los productos por coincidencia con el nombre
Route::get('getProductosPresupuestos', [PresupuestosController::class, 'getProductosPresupuestos']); //Obtener los productos para el detalle visualizado
Route::get('getProductosPresupuestos2', [PresupuestosController::class, 'getProductosPresupuestos2']); //Obtener los productos para el detalle visualizado
Route::get('getSubtotalPresupuesto', [PresupuestosController::class, 'getSubtotalPresupuesto']); //Obtener los productos para el detalle visualizado
Route::get('getProductosPresupuestosPDF', [PresupuestosController::class, 'getProductosPresupuestosPDF']); //Obtener los productos para el detalle en PDF
Route::post('postPresupuestoEncabezado', [PresupuestosController::class, 'postPresupuestoEncabezado']); // Para ingresar el encabezado del presupuesto
Route::post('postPresupuestoDetalle', [PresupuestosController::class, 'postPresupuestoDetalle']); // Para ingresar el detalle del prespuesto
Route::post('updatePrices', [PresupuestosController::class, 'updatePrices']); // Para ingresar el encabezado del presupuesto
Route::post('copyPresupuestosToTemp', [PresupuestosController::class, 'copyPresupuestosToTemp']); // Para ingresar el encabezado del presupuesto
Route::post('convertPresupuesto', [PresupuestosController::class, 'convertPresupuesto']); // Para convertir el presupuestos a pedido
Route::delete('deletePresupuestoEncabezadoDetalle', [PresupuestosController::class, 'deletePresupuestoEncabezadoDetalle']); // Para borrar los encabezados y detalle del presupuesto
Route::post('postPresupuestoEncabezadoModificado', [PresupuestosController::class, 'postPresupuestoEncabezadoModificado']); // Para ingresar el encabezado del presupuesto

// Catalogo
Route::get('getCatalogoVert', [CatalogoController::class, 'getCatalogoVert']); //Obtener los productos para el detalle visualizado
Route::get('getCatalogoByGrupo', [CatalogoController::class, 'getCatalogoByGrupo']); //Obtener los productos para el detalle visualizado
Route::get('getCatalogoByGrupos', [CatalogoController::class, 'getCatalogoByGrupos']); //Obtener los productos para el detalle visualizado
Route::get('getArticuloByCodigo', [CatalogoController::class, 'getArticuloByCodigo']); //Obtener los productos para el detalle visualizado
Route::get('getFamilias', [CatalogoController::class, 'getFamilias']); //Obtener las familias de todos los productos
Route::get('getProductosByFamilia', [CatalogoController::class, 'getProductosByFamilia']); //Obtener los productos de cada familia consultada

// Productos Sugeridos
Route::get('getSugeridos', [PedidosController::class, 'getSugeridos']); //Obtener los productos para los sugeridos
Route::get('getSugeridosByFecha', [PedidosController::class, 'getSugeridosByFecha']); //Obtener los productos para los sugeridos
Route::get('getSugeridosPDF', [PedidosController::class, 'getSugeridosPDF']); //Obtener los productos para los sugeridos en PDF

//Consulta de productos
Route::get('getClientesAndZonaByCodigo', [ProductosController::class, 'getClientesAndZonaByCodigo']); //Obtener los clientes por zona y código
Route::get('getClientesAndZonaByNombre', [ProductosController::class, 'getClientesAndZonaByNombre']); //Obtener los clientes por zona y nombre
Route::get('getClientesByZonaCodigoAndTerm', [ProductosController::class, 'getClientesByZonaCodigoAndTerm']); //Obtener los clientes por zona, codigo y termino
Route::get('getClientesByZonaNombreAndTerm', [ProductosController::class, 'getClientesByZonaNombreAndTerm']); //Obtener los clientes por zona, nombre y termino

// Subir fotos
Route::post('postImg', [ImageController::class, 'postImg']);
Route::get('getImg', [ImageController::class, 'getImg']);

// Articulos
Route::get('getArticulosAndFotos', [ProductosController::class, 'getArticulosAndFotos']);
Route::get('getTopProductos', [ProductosController::class, 'getTopProductos']);
Route::get('getTopProductos2', [ProductosController::class, 'getTopProductos2']);
Route::get('getTopProductos2XLS', [ProductosController::class, 'getTopProductos2XLS']);
Route::get('getProductosEnPromocion', [ProductosController::class, 'getProductosEnPromocion']);
Route::get('getGeneralProductos', [ProductosController::class, 'getGeneralProductos']);
Route::get('getListaPrecioCadenas', [ProductosController::class, 'getListaPrecioCadenas']);

// Obtener y subir nuevos vídeos
Route::post('postNewVideo', [VideoController::class, 'postNewVideo']);
Route::get('getVideos', [VideoController::class, 'getVideos']);
Route::patch('registerOrUpdateViewer', [VideoController::class, 'registerOrUpdateViewer']);

// Link APK
Route::get('linkApk', [DashboardController::class, 'linkApk']);

// Auth
Route::post('login', [CustomUserController::class, 'login']);
Route::post('register', [CustomUserController::class, 'register']);
Route::group(['middleware' => 'api',], function () {
    Route::post('logout', [CustomUserController::class, 'logout']);
    Route::post('refresh', [CustomUserController::class, 'refresh']);
    Route::post('me', [CustomUserController::class, 'me']);
});

Route::post('insertZonaVendedor', [MiscController::class, 'insertZonaVendedor']);
