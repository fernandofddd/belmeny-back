<?php

namespace App\Http\Controllers\Api;

// Controllers
use App\Http\Controllers\Controller;

// Models
use App\Models\Manifiesto;
use App\Models\ManifiestoDetalle;

// Query filters
use App\Filters\V1\Vendedor\ManifiestoFilter;
use App\Filters\V1\Vendedor\ManifiestoDetalleFilter;

// Resources
use App\Http\Resources\Vendedor\Manifiesto\ManifiestoResource;
use App\Http\Resources\Vendedor\Manifiesto\ManifiestoCollection;
use App\Http\Resources\Vendedor\Manifiesto\ManifiestoDetalleCollection;
use App\Http\Resources\Vendedor\Manifiesto\ManifiestoDetalleResource;

// Http and supports
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManifiestoController extends Controller
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
    
    public function getManifiestoVendedor(Request $request)
    {
        if ($request->Vendedor === 'none') {
            $manifiesto = DB::table('z050_ManifiestoEncabezado')
                ->select('*')
                ->join('z051_ManifiestoDetalle', 'z050_ManifiestoEncabezado.Documento', '=', 'z051_ManifiestoDetalle.DocumentoDetalle')
                ->orderBy('z050_ManifiestoEncabezado.FechaSalida', 'desc')
                ->groupBy('z050_ManifiestoEncabezado.Documento')
                ->paginate(15);
        } else {
            $manifiesto = DB::table('z050_ManifiestoEncabezado')
                ->select('*')
                ->join('z051_ManifiestoDetalle', 'z050_ManifiestoEncabezado.Documento', '=', 'z051_ManifiestoDetalle.DocumentoDetalle')
                ->where('z051_ManifiestoDetalle.Vendedor', '=', $request->Vendedor)
                ->orderBy('z050_ManifiestoEncabezado.FechaSalida', 'desc')
                ->groupBy('z050_ManifiestoEncabezado.Documento')
                ->paginate(15);
        }

        return response()->json($manifiesto);
    }

    public function searchManifiesto(Request $request)
    {
        if ($request->Cliente) {
            $manifiesto = DB::table('z050_ManifiestoEncabezado')
                ->select('*')
                ->join('z051_ManifiestoDetalle', 'z050_ManifiestoEncabezado.Documento', '=', 'z051_ManifiestoDetalle.DocumentoDetalle')
                ->where('z051_ManifiestoDetalle.Cliente', 'LIKE', '%' . $request->Cliente . '%')
                ->where('z051_ManifiestoDetalle.Vendedor', '=', $request->Vendedor)
                ->orderBy('z050_ManifiestoEncabezado.FechaSalida', 'desc')
                ->groupBy('z050_ManifiestoEncabezado.Documento')
                ->paginate(15);

            return response()->json($manifiesto);
        }

        if ($request->Documento) {
            $manifiesto = DB::table('z050_ManifiestoEncabezado')
                ->select('*')
                ->join('z051_ManifiestoDetalle', 'z050_ManifiestoEncabezado.Documento', '=', 'z051_ManifiestoDetalle.DocumentoDetalle')
                ->where('z050_ManifiestoEncabezado.Documento', '=', $request->Documento)
                ->where('z051_ManifiestoDetalle.Vendedor', '=', $request->Vendedor)
                ->orderBy('z050_ManifiestoEncabezado.FechaSalida', 'desc')
                ->groupBy('z050_ManifiestoEncabezado.Documento')
                ->paginate(15);

            return response()->json($manifiesto);
        }

        if ($request->fechaInicio && $request->fechaFin) {
            $manifiesto = DB::table('z050_ManifiestoEncabezado')
                ->select('*')
                ->join('z051_ManifiestoDetalle', 'z050_ManifiestoEncabezado.Documento', '=', 'z051_ManifiestoDetalle.DocumentoDetalle')
                ->where('z051_ManifiestoDetalle.Vendedor', '=', $request->Vendedor)
                ->whereBetween('FechaSalida', [$request->fechaInicio, $request->fechaFin])
                ->orderBy('z050_ManifiestoEncabezado.FechaSalida', 'desc')
                ->groupBy('z050_ManifiestoEncabezado.Documento')
                ->paginate(500);

            return response()->json($manifiesto);
        }
    }

    public function getDetalleManifiesto(Request $request)
    {
        $filter = new ManifiestoDetalleFilter();
        $filterItems = $filter->transform($request);
        $manifiesto = ManifiestoDetalle::where($filterItems);

        return new ManifiestoDetalleCollection($manifiesto->paginate(50)->appends($request->query()));
    }

    public function getManifiestoxSupervisor(Request $request)
    {
        $supervisorsToQuery = [];
        $requestSupervisor = $request->Supervisor; 

        if ($requestSupervisor && isset($this->supervisor_general[$requestSupervisor])) {
            $supervisorsToQuery = $this->supervisor_general[$requestSupervisor];
        } else {
            $supervisorsToQuery = [$requestSupervisor];
        }
       

        $manifiestoQuery = DB::table('z050_ManifiestoEncabezado')
            ->select(
                'z050_ManifiestoEncabezado.Documento',
                'z050_ManifiestoEncabezado.Estado',
                'z050_ManifiestoEncabezado.FechaSalida',
                'z051_ManifiestoDetalle.Vendedor',
                'z050_ManifiestoEncabezado.EmpresaTransporte',
                'b040_usuario.Nombre as VendedorNombre',
                'z050_ManifiestoEncabezado.Estado as ZonaNombre'
            )
            ->join('z051_ManifiestoDetalle', 'z050_ManifiestoEncabezado.Documento', '=', 'z051_ManifiestoDetalle.DocumentoDetalle')
            ->join('b040_usuario', 'z051_ManifiestoDetalle.Vendedor', '=', 'b040_usuario.CodVendedor');

        if (!empty($supervisorsToQuery)) {
            $manifiestoQuery->whereIn('b040_usuario.CodSupervisor', $supervisorsToQuery); 
        } else {
            $manifiestoQuery->where('b040_usuario.CodSupervisor', $requestSupervisor);
        }
        
        $manifiesto = $manifiestoQuery->orderBy('z050_ManifiestoEncabezado.Documento', 'desc')
            ->orderBy('b040_usuario.Nombre', 'asc')
            ->paginate(15);

        return response()->json($manifiesto);
    }


    public function getDetalleManifiestoxSupervisor(Request $request)
    {
        $manifiesto = DB::table('z051_ManifiestoDetalle') //traer el detalle de los manifiestos seleccionados
            ->where('DocumentoDetalle', $request->DocumentoDetalle)
            ->where('Vendedor', $request->Supervisor)
            ->orderBy('FFacturacion', 'desc')
            ->get();

        return response()->json($manifiesto);
    }

    public function searchManifiestoxSupervisor(Request $request)
    {
        // --- START: Logic to determine $supervisorsToQuery ---
        $supervisorsToQuery = [];
        $requestSupervisor = $request->Supervisor; // Get the requested supervisor code

        // Check if the requested supervisor is a key in our general supervisor map
        if ($requestSupervisor && isset($this->supervisor_general[$requestSupervisor])) {
            // If it's a general supervisor (e.g., S02), include all its associated sub-supervisors
            $supervisorsToQuery = $this->supervisor_general[$requestSupervisor];
        } else {
            // If it's not a general supervisor (e.g., S01, S06, or non-'S' type),
            // treat it as an individual supervisor to query directly.
            $supervisorsToQuery = [$requestSupervisor];
        }
        // --- END: Logic to determine $supervisorsToQuery ---

        // Helper function to build the base query, avoiding repetition
        $getBaseQuery = function() use ($supervisorsToQuery) {
            $query = DB::table('z050_ManifiestoEncabezado')
                ->select(
                    'z050_ManifiestoEncabezado.Documento',
                    'z050_ManifiestoEncabezado.Estado',
                    'z050_ManifiestoEncabezado.FechaSalida',
                    'z051_ManifiestoDetalle.Vendedor',
                    'z050_ManifiestoEncabezado.EmpresaTransporte',
                    'b040_usuario.Nombre as VendedorNombre',
                    'z050_ManifiestoEncabezado.Estado as ZonaNombre'
                )
                ->join('z051_ManifiestoDetalle', 'z050_ManifiestoEncabezado.Documento', '=', 'z051_ManifiestoDetalle.DocumentoDetalle')
                ->join('b040_usuario', 'z051_ManifiestoDetalle.Vendedor', '=', 'b040_usuario.CodVendedor');
            
            // Apply the supervisor filter using whereIn
            if (!empty($supervisorsToQuery)) {
                $query->whereIn('b040_usuario.CodSupervisor', $supervisorsToQuery);
            }
            // Note: No 'else' for an empty $supervisorsToQuery as the logic above ensures it's never empty if $request->Supervisor has a value.
            
            return $query;
        };


        if ($request->Cliente) {
            $manifiesto = $getBaseQuery() // Use the helper to get the base query with supervisor filter
                ->where('z051_ManifiestoDetalle.Cliente', 'LIKE', '%' . $request->Cliente . '%')
                ->orderBy('z050_ManifiestoEncabezado.Documento', 'desc')
                ->orderBy('b040_usuario.Nombre', 'asc')
                ->paginate(15); // Original pagination
            
            return response()->json($manifiesto);
        }

        if ($request->Documento) {
            $manifiesto = $getBaseQuery() // Use the helper to get the base query with supervisor filter
                ->where('z050_ManifiestoEncabezado.Documento', '=', $request->Documento)
                ->orderBy('z050_ManifiestoEncabezado.Documento', 'desc')
                ->orderBy('b040_usuario.Nombre', 'asc')
                ->paginate(15); // Original pagination
            
            return response()->json($manifiesto);
        }

        if ($request->fechaInicio && $request->fechaFin) {
            $manifiesto = $getBaseQuery() // Use the helper to get the base query with supervisor filter
                ->whereBetween('FechaSalida', [$request->fechaInicio, $request->fechaFin])
                ->orderBy('z050_ManifiestoEncabezado.Documento', 'desc')
                ->orderBy('b040_usuario.Nombre', 'asc')
                ->paginate(500); // Original pagination
            
            return response()->json($manifiesto);
        }

        // If no search criteria is provided, return an empty response or a default list
        return response()->json([]); 
    }
}
