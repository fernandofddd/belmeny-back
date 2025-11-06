<?php

namespace App\Http\Controllers\Api;

// Controllers
use App\Http\Controllers\Controller;

// Models
use App\Models\Exhibidor;
use App\Models\Presolicitud;
use App\Models\Image;

// Http and supports
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExhibidorController extends Controller
{
    //Registro de solicitudes
    public function postSolicitud(Request $request)
    {
        $exhibidor = new Exhibidor();

        $exhibidor->nro_pedido = $request->nro_pedido;
        $exhibidor->usuario = $request->usuario;
        $exhibidor->cliente = $request->cliente;
        $exhibidor->marca = $request->marca;
        $exhibidor->motivo_solicitud = $request->motivoSolicitud;
        $exhibidor->tipo_exhibidor = $request->tipoExhibidor;
        $exhibidor->ancho = $request->ancho;
        $exhibidor->alto = $request->alto;
        $exhibidor->material_exhibidor = $request->materialExhibidor;
        $exhibidor->monto_exhibidor = $request->montoExhibidor;
        $exhibidor->aprobacion_supervisor = $request->aprobacion_supervisor;
        $exhibidor->aprobacion_gerencia = $request->aprobacion_gerencia;
        $exhibidor->fecha_solicitud = $request->fecha_solicitud;
        $exhibidor->save();
    }

    //Buzón de solicitudes
    public function getBuzon(Request $request)
    {
        $buzon = Exhibidor::select('*')
            // ->where('usuario', '=', $request->usuario)
            ->orderBy('fecha_solicitud', 'desc')
            ->paginate(5);

        return response()->json($buzon);
    }

    //Buzón de presolicitudes
    public function getBuzonPreSolicitud(Request $request)
    {
        $buzon = Presolicitud::select('*')
            ->orderBy('fecha_solicitud', 'desc')
            ->paginate(5);

        return response()->json($buzon);
    }

    public function getMessagePreSolicitud(Request $request)
    {
        $message = Presolicitud::select('*')
            ->where('id_presolicitud', '=', $request->id_presolicitud)
            ->get();

        return response()->json($message);
    }

    public function getMessage(Request $request)
    {
        $message = Exhibidor::select('*')
            ->where('nro_solicitud', '=', $request->nro_solicitud)
            ->get();

        return response()->json($message);
    }

    public function actualizarLeidoVendedorPreSolicitud(Request $request)
    {
        $buzon = Presolicitud::where('id_presolicitud', $request->nro_solicitud);

        $buzon->update(['leido_vendedor' => 1]);

        return response()->json(["message" => "La solicitud ha sido leída por el vendedor..."]);
    }

    public function actualizarLeidoSupervisorPreSolicitud(Request $request)
    {
        $buzon = Presolicitud::where('id_presolicitud', $request->nro_solicitud);

        $buzon->update(['leido_supervisor' => 1]);

        return response()->json(["message" => "La solicitud ha sido leída por el supervisor..."]);
    }

    public function actualizarLeidoGerenciaPreSolicitud(Request $request)
    {
        $buzon = Presolicitud::where('id_presolicitud', $request->nro_solicitud);

        $buzon->update(['leido_gerencia' => 1]);

        return response()->json(["message" => "La solicitud ha sido leída por el gerente..."]);
    }

    public function actualizarLeidoVendedor(Request $request)
    {
        $buzon = Exhibidor::where('nro_solicitud', $request->nro_solicitud);

        $buzon->update(['leido_vendedor' => 1]);

        return response()->json(["message" => "La solicitud ha sido leída por el vendedor..."]);
    }

    public function actualizarLeidoSupervisor(Request $request)
    {
        $buzon = Exhibidor::where('nro_solicitud', $request->nro_solicitud);

        $buzon->update(['leido_supervisor' => 1]);

        return response()->json(["message" => "La solicitud ha sido leída por el supervisor..."]);
    }

    public function actualizarLeidoGerencia(Request $request)
    {
        $buzon = Exhibidor::where('nro_solicitud', $request->nro_solicitud);

        $buzon->update(['leido_gerencia' => 1]);

        return response()->json(["message" => "La solicitud ha sido leída por el gerente..."]);
    }

    public function AprovOrDeny(Request $request)
    {
        if ($request->typeUser == 'supervisor' && $request->decision == 'Aprobado') {
            $solicitud = Exhibidor::where('nro_solicitud', $request->nro_solicitud);
            $solicitud->update([
                'aprobacion_supervisor' => 'Aprobado',
                'observacion_supervisor' => 'N/A',
            ]);
            return response()->json(["message" => "La solicitud ha sido aprobada por el usuario " . $request->usuario]);
        } else if ($request->typeUser == 'supervisor' && $request->decision == 'Denegado') {
            $solicitud = Exhibidor::where('nro_solicitud', $request->nro_solicitud);
            $solicitud->update([
                'aprobacion_supervisor' => 'Denegado',
                'observacion_supervisor' => $request->observacion,
                'aprobacion_gerencia' => 'Denegado',
                'observacion_gerencia' => 'N/A'
            ]);
            return response()->json(["message" => "La solicitud ha sido denegada por el usuario " . $request->usuario]);
        } else if ($request->typeUser == 'gerente' && $request->decision == 'Aprobado') {
            $solicitud = Exhibidor::where('nro_solicitud', $request->nro_solicitud);
            $solicitud->update([
                'aprobacion_gerencia' => 'Aprobado',
                'observacion_gerencia' => 'N/A',
                'observacion_supervisor' => 'N/A',
            ]);
            return response()->json(["message" => "La solicitud ha sido aprobada por el usuario " . $request->usuario]);
        } else if ($request->typeUser == 'gerente' && $request->decision == 'Denegado') {
            $solicitud = Exhibidor::where('nro_solicitud', $request->nro_solicitud);
            $solicitud->update([
                'aprobacion_supervisor' => 'Denegado',
                'observacion_supervisor' => 'N/A',
                'aprobacion_gerencia' => 'Denegado',
                'observacion_gerencia' => $request->observacion
            ]);
            return response()->json(["message" => "La solicitud ha sido denegada por el usuario " . $request->usuario]);
        }
    }

    public function getClienteVentas6UltimosMeses(Request $request)
    {
        $ventasCliente = DB::table('e200_FacturaEncabezado')
            ->select('codcliente', 'CodigoVendedor', DB::raw('sum(TotalFinal) as Total_Vendido'))
            ->where('CodigoVendedor', '=', $request->CodigoVendedor)
            ->where('codcliente', '=', $request->codcliente)
            ->whereBetween('FechaDocumento', ['2022-06-01', '2022-12-31'])
            ->get();

        return response()->json($ventasCliente);
    }

    public function postPreSolicitud(Request $request)
    {
        $preSolicitud = new Presolicitud;

        $preSolicitud->cliente = $request->cliente;
        $preSolicitud->sugerencia = $request->sugerencia;
        $preSolicitud->imagenes_id = $request->imagenes_id;
        $preSolicitud->fecha_solicitud = $request->fecha_solicitud;
        $preSolicitud->usuario = $request->usuario;
        $preSolicitud->marca = $request->marca;
        $preSolicitud->rif = $request->rif;

        $preSolicitud->save();

        return response()->json($preSolicitud);
    }

    //Aprobación de supervisor

    //Aprobación de gerencia
}
