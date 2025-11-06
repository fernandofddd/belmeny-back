<?php

namespace App\Http\Controllers\Api;

// Controllers
use App\Http\Controllers\Controller;

// Models
use App\Models\Zonas;
use App\Models\Metas;

// Http and supports
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class MiscController extends Controller
{
    // Despues del registro del usuario, se registra su Zona
    public function insertZonaVendedor(Request $request)
    {
        $metas = new Metas;

        $start = new Carbon('first day of this month');
        $end = new Carbon('last day of this month');

        $metas->vendedor = $request->CodVendedor;
        $metas->nombre = $request->Nombre;
        $metas->vert = 15000;
        $metas->ingco = 15000;
        $metas->imou = 0;
        $metas->quilosa = 0;
        $metas->fleximatic = 0;
        $metas->articulo = 0;
        $metas->global = 30000;
        $metas->zona = $request->Sector;
        $metas->total_vendido = 0;
        $metas->PeriodoInicio = $start->startOfMonth();
        $metas->PeriodoFin = $end->endOfMonth()->startOfDay();
        $metas->VentasIngco = 0;
        $metas->VentasVert = 0;
        $metas->save();

        $zonas = new Zonas;

        $zonas->Sector = $request->Sector;
        $zonas->CodVendedor = $request->CodVendedor;
        $zonas->CodSupervisor = $request->CodSupervisor;
        $zonas->Nombre = $request->Nombre;
        $zonas->save();
    }
}
