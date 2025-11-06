<?php

namespace App\Http\Controllers\Api;

// Controllers
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseController as BaseController;

// Http and supports
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogoController extends BaseController
{
  public function getCatalogoVert(Request $request)
  {
    $catalogoVert = DB::table('a020_articulos')
      ->select('Codigo', DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(Nombre, '*', ''), '-', ''), '.', ''), '/', ''), '\"', '') as Nombre"), 'Existencia', 'Precio1', 'Precio2', 'Precio3', 'Precio4', 'UnidEmpaque', 'VentaMinima', 'Grupo', 'Subgrupo', 'RutaImagen', 'CantBulto')
      ->whereRaw("(Subgrupo BETWEEN '03-001' AND '03-029') OR 
        (Subgrupo BETWEEN '04-001' and '04-006') OR
        (Subgrupo BETWEEN '05-001' and '07-009') OR
        (Subgrupo BETWEEN '09-001' AND '09-002') OR
        (Subgrupo BETWEEN '10-001' and '10-004') OR
        (Subgrupo BETWEEN '11-001' and '11-007') OR
        (Subgrupo BETWEEN '12-001' and '12-005') OR 
        (Subgrupo BETWEEN '14-001' and '15-003') OR
        (Subgrupo BETWEEN '13-001' and '13-005') OR
        (Subgrupo BETWEEN '16-001' and '16-006')")
      ->orderBy('Grupo')
      ->orderBy('Subgrupo')
      ->orderBy('Nombre')
      ->paginate(40);

    return response()->json($catalogoVert);
  }

  public function getCatalogoByGrupo(Request $request)
  {

    $whereParam = "";

    switch ($request->seccion) {

        /*-----------------VERT-----------------*/
        // ILUMINACIÓN
      case 'bombillos':
        $whereParam = "(Subgrupo BETWEEN '03-001' AND '03-004') OR (Subgrupo = '03-008')";
        break;

      case 'cintasLED':
        $whereParam = "(Subgrupo = '03-005') OR (Subgrupo = '03-029')";
        break;

      case 'lamparas':
        $whereParam = "(Subgrupo = '03-009') OR (Subgrupo = '03-006') OR (Subgrupo BETWEEN '03-016' and '03-020') OR (Subgrupo = '03-025')";
        break;

      case 'paneles':
        $whereParam = "(Subgrupo BETWEEN '03-010' and '03-015')";
        break;

      case 'tubos':
        $whereParam = "(Subgrupo BETWEEN '03-021' and '03-024')";
        break;

      case 'alumbrados':
        $whereParam = "(Subgrupo = '03-026')";
        break;

      case 'reflectores':
        $whereParam = "(Subgrupo = '03-027')";
        break;

      case 'linternas':
        $whereParam = "(Subgrupo = '03-028')";
        break;

        //HOGAR
      case 'hogar':
        $whereParam = "(Subgrupo BETWEEN '11-001' and '11-007')";
        break;

      case 'banos':
        $whereParam = "(Subgrupo BETWEEN '10-001' and '10-004')";
        break;

      case 'jardineria':
        $whereParam = "(Subgrupo BETWEEN '12-001' and '12-005')";
        break;

      case 'plomeria':
        $whereParam = "(Subgrupo BETWEEN '07-001' and '07-009') OR (Subgrupo BETWEEN '09-001' AND '09-002')";
        break;

      case 'cerraduras':
        $whereParam = "(Subgrupo = '06-001')";
        break;

        //FERRETERIA EN GENERAL
      case 'ferreteriagral':
        $whereParam = "(Subgrupo BETWEEN '05-001' and '05-009')";
        break;

        //ELECTRICIDAD
      case 'electricidad':
        $whereParam = "(Subgrupo BETWEEN '04-001' and '04-006')";
        break;

        //AUTOMOTRIZ
      case 'automotriz':
        $whereParam = "(Subgrupo BETWEEN '13-001' and '13-005')";
        break;

        //CONSTRUCCION
      case 'construccion':
        $whereParam = "(Subgrupo BETWEEN '16-001' and '16-006')";
        break;

        //MISCELANEO
      case 'miscelaneos':
        $whereParam = "(Subgrupo BETWEEN '14-001' and '15-003')";
        break;

        /*-----------------IMOU-----------------*/
      case 'imou':
        $whereParam = "(Subgrupo BETWEEN '01-001' and '01-003')";
        break;

        /*-----------------FLEXIMATIC-----------------*/
      case 'fleximatic':
        $whereParam = "(Subgrupo BETWEEN '08-001' and '08-005')";
        break;

        /*-----------------QUILOSA-----------------*/
      case 'quilosa':
        $whereParam = "(Subgrupo BETWEEN '02-001' and '02-005')";
        break;

        /*-----------------WADFOW-----------------*/
      case 'wadfow':
        $whereParam = "(Grupo = '027')";
        break;

        /*-----------------INGCO-----------------*/
        //HERRAMIENTAS ELECTRICAS
      case 'electricas':
        $whereParam = "(Subgrupo BETWEEN '021-01' and '021-02')";
        break;

        //BOMBAS DE AGUA
      case 'bombas':
        $whereParam = "(Subgrupo = '021-03')";
        break;

        //MECHAS
      case 'mechas':
        $whereParam = "(Subgrupo = '021-04')";
        break;

        //CONSUMIBLES
      case 'consumibles':
        $whereParam = "(Subgrupo = '021-05')";
        break;

        //HERRAMIENTAS AISLADAS
      case 'aisladas':
        $whereParam = "(Subgrupo = '021-06')";
        break;

        //HERRAMIENTAS MANUALES
      case 'manuales':
        $whereParam = "(Subgrupo BETWEEN '021-07' and '021-08')";
        break;

        //HERRAMIENTAS DE MEDICION
      case 'medicion':
        $whereParam = "(Subgrupo = '021-09')";
        break;

        //HERRAMIENTAS NEUMATICAS
      case 'neumaticas':
        $whereParam = "(Subgrupo = '021-10')";
        break;

        //GATOS HIDRAULICOS
      case 'hidraulicos':
        $whereParam = "(Subgrupo = '021-11')";
        break;

        //ACCESORIOS
      case 'accesorios':
        $whereParam = "(Subgrupo = '021-12')";
        break;

        //JARDINERIA
      case 'jardineria-ingco':
        $whereParam = "(Subgrupo = '021-13')";
        break;

        //ACCESORIOS PARA PINTAR
      case 'pintura':
        $whereParam = "(Subgrupo = '021-14')";
        break;

        //BOLSO DE HERRAMIENTAS
      case 'bolsos':
        $whereParam = "(Subgrupo = '021-15')";
        break;

        //SEGURIDAD INDUSTRIAL
      case 'seguridad':
        $whereParam = "(Subgrupo = '021-16')";
        break;

        //BOTAS DE SEGURIDAD
      case 'botas':
        $whereParam = "(Subgrupo = '021-17')";
        break;

        //CANDADOS
      case 'candados':
        $whereParam = "(Subgrupo = '021-18')";
        break;

        //LINTERNAS
      case 'linternas-ingco':
        $whereParam = "(Subgrupo = '021-19')";
        break;
    }

    $articulos = DB::table('a020_articulos_catalogo2')
      ->select('*')
      ->whereRaw($whereParam)
      ->orderBy('Grupo', 'ASC')
      ->get();

    return response()->json($articulos);
  }

  public function getCatalogoByGrupos(Request $request)
  {

    $whereParam = "";

    switch ($request->seccion) {

        /*-----------------VERT-----------------*/
        // ILUMINACIÓN
      case 'bombillos':
        $whereParam = "ORN_Linea = 003 AND ORN_SubLinea BETWEEN 001 AND 011";
        break;

      case 'cintasLED':
        $whereParam = "(Subgrupo = '03-005') OR (Subgrupo = '03-029')";
        break;

      case 'lamparas':
        $whereParam = "ORN_Linea = 003 AND ORN_SubLinea IN (013, 023)";
        break;

      case 'lamparasOficina':
        $whereParam = "ORN_Linea = 003 AND ORN_SubLinea = 024";
        break;

      case 'paneles':
        $whereParam = "ORN_Linea = 003 AND ORN_SubLinea IN (014, 015, 016, 017, 018, 020, 021, 022, 024)";
        break;

      case 'tubos':
        $whereParam = "ORN_Linea = 003 AND ORN_SubLinea IN (012)";
        break;

      case 'alumbrados':
        $whereParam = "(Subgrupo = '03-026')";
        break;

      case 'reflectores':
        $whereParam = "ORN_Linea = 003 AND ORN_SubLinea BETWEEN 025 AND 027";
        break;

      case 'linternas':
        $whereParam = "(Subgrupo = '03-028')";
        break;

        //HOGAR
      case 'hogar':
        $whereParam = "(Subgrupo BETWEEN '11-001' and '11-007')";
        break;

      case 'banos':
        $whereParam = "(Subgrupo BETWEEN '10-001' and '10-004')";
        break;

      case 'jardineria':
        $whereParam = "(Subgrupo BETWEEN '12-001' and '12-005')";
        break;

      case 'plomeria':
        $whereParam = "ORN_Linea = 007";
        break;

      case 'cerraduras':
        $whereParam = "(Subgrupo = '06-001')";
        break;

        //FERRETERIA EN GENERAL
      case 'ferreteriagral':
        $whereParam = "ORN_Linea = 005";
        break;

        //ELECTRICIDAD
      case 'electricidad':
        $whereParam = "ORN_Linea = 004";
        break;

        //AUTOMOTRIZ
      case 'automotriz':
        $whereParam = "(Subgrupo BETWEEN '13-001' and '13-005')";
        break;

        //CONSTRUCCION
      case 'construccion':
        $whereParam = "(Subgrupo BETWEEN '16-001' and '16-006')";
        break;

        //MISCELANEO
      case 'miscelaneos':
        $whereParam = "(Subgrupo BETWEEN '14-001' and '15-003')";
        break;

        /*-----------------IMOU-----------------*/
      case 'imou':
        $whereParam = "(Subgrupo BETWEEN '01-001' and '01-003')";
        break;

        /*-----------------FLEXIMATIC-----------------*/
      case 'fleximatic':
        $whereParam = "(Subgrupo BETWEEN '08-001' and '08-005')";
        break;

        /*-----------------QUILOSA-----------------*/
      case 'quilosa':
        $whereParam = "(Subgrupo BETWEEN '02-001' and '02-005')";
        break;

        /*-----------------WADFOW-----------------*/
      case 'wadfow':
        $whereParam = "(Grupo = '027')";
        break;

        /*-----------------INGCO-----------------*/
        //HERRAMIENTAS ELECTRICAS
      case 'electricas':
        $whereParam = "(Subgrupo BETWEEN '021-01' and '021-02')";
        break;

        //BOMBAS DE AGUA
      case 'bombas':
        $whereParam = "(Subgrupo = '021-03')";
        break;

        //MECHAS
      case 'mechas':
        $whereParam = "(Subgrupo = '021-04')";
        break;

        //CONSUMIBLES
      case 'consumibles':
        $whereParam = "(Subgrupo = '021-05')";
        break;

        //HERRAMIENTAS AISLADAS
      case 'aisladas':
        $whereParam = "(Subgrupo = '021-06')";
        break;

        //HERRAMIENTAS MANUALES
      case 'manuales':
        $whereParam = "(Subgrupo BETWEEN '021-07' and '021-08')";
        break;

        //HERRAMIENTAS DE MEDICION
      case 'medicion':
        $whereParam = "(Subgrupo = '021-09')";
        break;

        //HERRAMIENTAS NEUMATICAS
      case 'neumaticas':
        $whereParam = "(Subgrupo = '021-10')";
        break;

        //GATOS HIDRAULICOS
      case 'hidraulicos':
        $whereParam = "(Subgrupo = '021-11')";
        break;

        //ACCESORIOS
      case 'accesorios':
        $whereParam = "(Subgrupo = '021-12')";
        break;

        //JARDINERIA
      case 'jardineria-ingco':
        $whereParam = "(Subgrupo = '021-13')";
        break;

        //ACCESORIOS PARA PINTAR
      case 'pintura':
        $whereParam = "(Subgrupo = '021-14')";
        break;

        //BOLSO DE HERRAMIENTAS
      case 'bolsos':
        $whereParam = "(Subgrupo = '021-15')";
        break;

        //SEGURIDAD INDUSTRIAL
      case 'seguridad':
        $whereParam = "(Subgrupo = '021-16')";
        break;

        //BOTAS DE SEGURIDAD
      case 'botas':
        $whereParam = "(Subgrupo = '021-17')";
        break;

        //CANDADOS
      case 'candados':
        $whereParam = "(Subgrupo = '021-18')";
        break;

        //LINTERNAS
      case 'linternas-ingco':
        $whereParam = "(Subgrupo = '021-19')";
        break;
    }

    $articulos = DB::table('a020_catalogo AS ac')
      ->select('*')
      ->whereRaw($whereParam)
      ->orderBy('ORN_Familia', 'ASC')
      ->orderBy('Precio', 'ASC')
      ->paginate(10);

    return response()->json($articulos);
  }

  public function getArticuloByCodigo(Request $request)
  {
    $articulo = DB::table("a020_catalogo as ac")
      ->select("*")
      ->where("Codigo", "=", $request->Codigo)
      ->get();

    return response()->json($articulo);
  }

  public function getFamilias(Request $request)
  {
    $familias = DB::table("a020_catalogo")
      ->select("ORN_Familia")
      ->groupBy("ORN_Familia")
      ->get();

    return response()->json($familias);
  }

  public function getProductosByFamilia(Request $request) {
    $productos = DB::table("a020_catalogo")
    ->select("codigo", "descripcion", "Propiedad1", "Propiedad2", "Propiedad3", "Propiedad4", "Precio", "Medidas")
    ->where("ORN_Familia", $request->familia)
    ->get();

    return response()->json($productos);
  }
}
