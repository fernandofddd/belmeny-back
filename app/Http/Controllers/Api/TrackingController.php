<?php

namespace App\Http\Controllers\Api;

// Controllers
use App\Http\Controllers\Controller;

// Models
use App\Models\Tracking;

// Query filters
use App\Filters\V1\Vendedor\TrackingFilter;

// Resources
use App\Http\Resources\Vendedor\Tracking\TrackingResource;
use App\Http\Resources\Vendedor\Tracking\TrackingCollection;

// Http and support
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackingController extends Controller
{
  protected $excludedVendors = ['V1', 'V2', 'T1', 'CCS1'];

    protected $supervisor_general = [
        'S02' => ['S02','S06', 'S07'],
        'S03' => ['S03','S08'],
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
        'S08' => 'Franklin Taylor',
    ];

  public function TrackPedidosVendedor(Request $request)
  {
    $filter = new TrackingFilter();
    $filterItems = $filter->transform($request);

    $tracking = Tracking::where($filterItems)->orderBy('Estado')->orderByDesc('FechaCreacion');

    return new TrackingCollection($tracking->paginate(15)->appends($request->query()));
  }

  public function getTrackingPorFecha(Request $request)
  {
    if ($request->CodVendedor != 'none') {
      $tracking = DB::table('s060_despacho')
        ->where('CodVendedor', '=', $request->CodVendedor)
        ->whereBetween('FechaCreacion', [$request->fechaInicio, $request->fechaFin])
        ->orderBy('Estado', 'ASC')
        ->orderBy('FechaCreacion', 'DESC')
        ->get();
    } else {
      $tracking = DB::table('s060_despacho')
        ->whereBetween('FechaCreacion', [$request->fechaInicio, $request->fechaFin])
        ->orderBy('Estado', 'ASC')
        ->orderBy('FechaCreacion', 'DESC')
        ->get();
    }

    return response()->json($tracking);
  }

  public function getTrackingDetalle(Request $request)
  {
      $tracking = DB::table('s060_despacho')
        ->select('*')
        ->where('Documento', '=', $request->Documento)
        ->orderBy('FechaCreacion', 'DESC')
        ->get();


    return response()->json($tracking);
  }

  public function getTrackingCliente(Request $request)
  {
    $tracking = DB::table('s060_despacho')
      ->where('NombreCliente', 'LIKE', '%' . $request->NombreCliente . '%')
      ->orderBy('Estado', 'asc')
      ->orderBy('FechaCreacion', 'desc');

    if ($request->CodVendedor != 'none') {
      $tracking->where('CodVendedor', '=', $request->CodVendedor);
    }

    $tracking = $tracking->get();

    return response()->json($tracking);
  }

   public function getTrackingxZonaSupervisor(Request $request)
    {
        $supervisorsToQuery = [];
        $requestCodSupervisor = $request->CodSupervisor;

        if ($requestCodSupervisor && isset($this->supervisor_general[$requestCodSupervisor])) {
            $supervisorsToQuery = $this->supervisor_general[$requestCodSupervisor];
        } else {
            $supervisorsToQuery = [$requestCodSupervisor];
        }
        $tracking = DB::table('s060_despacho as sd')
            ->join('w004_zona as z', 'sd.CodVendedor', '=', 'z.CodVendedor')
            ->select(['sd.*', 'z.Sector', 'z.Nombre'])
            ->whereBetween('FechaCreacion', [
                DB::raw('DATE_SUB(LAST_DAY(NOW()), INTERVAL DAYOFMONTH(LAST_DAY(NOW())) -1 day)'),
                DB::raw('LAST_DAY(NOW())')
            ]);
        if (!empty($supervisorsToQuery)) {
            $tracking->whereIn('z.CodSupervisor', $supervisorsToQuery);
        } else {
            $tracking->where('z.CodSupervisor', '=', $requestCodSupervisor);
        }
        if ($request->Zona !== 'todas') {
            $tracking->where('z.Sector', '=', $request->Zona);
        }

        $tracking = $tracking->orderBy('Estado', 'ASC')
            ->orderBy('FechaCreacion', 'DESC')
            ->paginate(15);

        return response()->json($tracking);
    }

  public function getTrackingxZonaSupervisorDocumento(Request $request)
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

        $tracking = DB::table('s060_despacho as sd')
            ->join('w004_zona as z', 'sd.CodVendedor', '=', 'z.CodVendedor')
            ->select(['sd.*', 'z.Sector', 'z.Nombre AS NombreVendedor']);

        // Apply the supervisor filter using whereIn
        if (!empty($supervisorsToQuery)) {
            $tracking->whereIn('z.CodSupervisor', $supervisorsToQuery); // <-- KEY CHANGE HERE!
        } else {
            // Fallback for cases where $supervisorsToQuery might be empty (e.g., $request->CodSupervisor was null)
            // or if a direct match is needed outside of defined groups.
            $tracking->where('z.CodSupervisor', '=', $requestCodSupervisor);
        }

        // Add the specific document filter
        $tracking->where('sd.Documento', $request->Documento);

        // Add the date range for the current month
        $tracking->whereBetween('sd.FechaCreacion', [
            DB::raw('DATE_SUB(CURDATE(), INTERVAL (DAY(CURDATE())-1) DAY)'), // First day of current month
            DB::raw('LAST_DAY(CURDATE())') // Last day of current month
        ]);

        // Finally, get the results
        $tracking = $tracking->get();

        return response()->json($tracking);
    }
  public function getTrackingxZonaSupervisorCliente(Request $request)
    {
        // Your initial pagination variables are defined but not used with ->get()
        // If you intend to paginate, you'll need to change ->get() to ->paginate($perPage).
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);

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

        $tracking = DB::table('s060_despacho as sd')
            ->join('w004_zona as z', 'sd.CodVendedor', '=', 'z.CodVendedor')
            ->select(['sd.*', 'z.Sector', 'z.Nombre AS NombreVendedor']);

        // Apply the supervisor filter using whereIn
        if (!empty($supervisorsToQuery)) {
            $tracking->whereIn('z.CodSupervisor', $supervisorsToQuery); // <-- KEY CHANGE HERE!
        } else {
            // Fallback for cases where $supervisorsToQuery might be empty (e.g., $request->CodSupervisor was null)
            // or if a direct match is needed outside of defined groups.
            $tracking->where('z.CodSupervisor', '=', $requestCodSupervisor);
        }

        // Add the specific client name filter
        $tracking->where('NombreCliente', 'LIKE', '%' . $request->Cliente . '%');

        // Add the date range for the current month
        $tracking->whereBetween('sd.FechaCreacion', [
            DB::raw('DATE_SUB(CURDATE(), INTERVAL (DAY(CURDATE())-1) DAY)'), // First day of current month
            DB::raw('LAST_DAY(CURDATE())') // Last day of current month
        ]);

        // Apply the limit if it was intended to be used with ->get()
        // If you actually want pagination, remove ->limit(350) and change ->get() to ->paginate($perPage)
        $tracking->limit(350);

        // Finally, get the results
        $tracking = $tracking->get();

        return response()->json($tracking);
    }

  public function getTrackingxZonaSupervisorFecha(Request $request)
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

        // Initialize $tracking outside the if/else to avoid potential 'undefined variable' issues
        $tracking = null;

        // Base query components that are common to both branches
        $baseQuery = DB::table('s060_despacho as sd')
            ->join('w004_zona as z', 'sd.CodVendedor', '=', 'z.CodVendedor')
            ->select(['sd.*', 'z.Sector', 'z.Nombre AS NombreVendedor']); // Use NombreVendedor as alias for consistency

        // Apply the supervisor filter using whereIn to the base query
        if (!empty($supervisorsToQuery)) {
            $baseQuery->whereIn('z.CodSupervisor', $supervisorsToQuery);
        } else {
            $baseQuery->where('z.CodSupervisor', '=', $requestCodSupervisor);
        }

        // --- Conditional logic based on $request->CodVendedor ---
        if ($request->CodVendedor !== 'none') {
            // Path 1: Specific date range from request (e.g., when a user selects dates)
            $tracking = $baseQuery->clone() // Clone the base query to avoid modifying it for the other branch
                ->whereBetween('FechaCreacion', [$request->fechaInicio, $request->fechaFin])
                ->limit(350)
                ->get();
        } else {
            // Path 2: Default date range (current month) and specific ordering/pagination
            $tracking = $baseQuery->clone() // Clone the base query
                ->select(['sd.*', 'z.Sector', 'z.Nombre']) // Original alias was 'Nombre' here, let's keep consistency.
                                                           // If you want NombreVendedor here too, use that alias.
                ->whereBetween('FechaCreacion', [
                    DB::raw('DATE_SUB(LAST_DAY(NOW()), INTERVAL DAYOFMONTH(LAST_DAY(NOW())) -1 day)'),
                    DB::raw('LAST_DAY(NOW())')
                ])
                ->orderBy('Estado', 'ASC')
                ->orderBy('FechaCreacion', 'DESC')
                ->limit(350)
                ->get();
        }

        return response()->json($tracking);
    }
}
