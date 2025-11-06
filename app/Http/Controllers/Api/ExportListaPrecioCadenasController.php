<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
//use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
//use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class ExportListaPrecioCadenasController extends Controller
{
    public function export(Request $request)
    {
        try {
            // 1) Permisos de memoria/tiempo
            ini_set('memory_limit', '1024M');
            set_time_limit(0);

            // 2) Montar tu “getListaPrecioCadenas”
            $empresas = $request->ZonasVenta === '001,003,004,CCS'
                ? ['001', '003', '004', 'CCS']
                : ['001', '003', '004'];

            switch ($request->Marca) {
                case 'VERT':
                    $marcaCond = "aa.grupo <> '021' AND aa.grupo <> '027' AND aa.grupo <> '032'";
                    break;
                case 'INGCO':
                    $marcaCond = "aa.grupo = '021'";
                    break;
                case 'WADFOW':
                    $marcaCond = "aa.grupo = '027'";
                    break;
                case 'PDVSA':
                    $marcaCond = "aa.grupo = '032'";
                    break;
                default:
                    $marcaCond = "aa.grupo <> '021' AND aa.grupo <> '027' AND aa.grupo <> '032'";
            }

            // El precio según vendedor
            $precioCol = $request->Vendedor === 'V67'
                ? 'aa.precio1 as Precio'
                : 'aa.precio2 as Precio';

            $productos = DB::table('a020_articulos as aa')
                ->leftJoin('w050_inventario_codBarras_nueva as wic', 'aa.codigo', '=', 'wic.codigo')
                ->select(
                    'aa.RutaImagen',
                    'aa.codigo as Codigo',
                    'aa.Nombre as Nombre',
                    DB::raw($precioCol),
                    'aa.existencia as Existencia',
                    'aa.VentaMinima',
                    'wic.codalternativo as CodigoBarras',
                    'aa.Empresa'
                )
                ->whereRaw($marcaCond)
                ->whereIn('aa.Empresa', $empresas)
                ->orderBy('aa.orden', 'ASC')
                ->orderBy('aa.Nombre', 'ASC')
                ->get();

            // 3) Arrancar PhpSpreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Lista de Precios');

            // Cabeceras
            $cols = ['A' => 'Código', 'B' => 'Imagen', 'E' => 'Nombre', 'F' => 'Precio', 'G' => 'Existencia', 'H' => 'Venta Mínima', 'I' => 'Código Barras', 'K' => 'Empresa'];
            foreach ($cols as $col => $titulo) {
                $sheet->setCellValue("{$col}1", $titulo)
                    ->getStyle("{$col}1")->getFont()->setBold(true);
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Cliente HTTP para bajar imágenes
            // $http = new Client(['timeout' => 10]);

            // 4) Rellenar filas e insertar imágenes
            //$sheet->getColumnDimension('B')->setWidth(10);
            //$sheet->getColumnDimension('C')->setWidth(40);
            $row = 2;
            foreach ($productos as $prod) {
                $sheet->setCellValue("A{$row}", $prod->Codigo);
                $sheet->setCellValue("E{$row}", $prod->Nombre);
                $sheet->setCellValue("F{$row}", $prod->Precio);
                $sheet->setCellValue("G{$row}", $prod->Existencia);
                $sheet->setCellValue("H{$row}", $prod->VentaMinima);
                $sheet->setCellValue("I{$row}", $prod->CodigoBarras);
                $sheet->setCellValue("J{$row}", $prod->Empresa);

                try {
                    $inserted = false;
                    // Obtenemos la ruta absoluta desde .env
                    $baseLocal  = env('CATALOGO_PATH') . DIRECTORY_SEPARATOR . $prod->Codigo;
                    $extensions = ['.png', '.jpg', '.jpeg', '.webp'];

                    // Primero intentamos buscar el fichero local
                    foreach ($extensions as $ext) {
                        $fullPath = $baseLocal . $ext;
                        if (! file_exists($fullPath)) {
                            continue;
                        }

                        // (Aquí iría tu lógica de convertir PNG → JPG / usar MemoryDrawing, etc.)
                        // Por ejemplo:
                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                        $drawing->setPath($fullPath);
                        $drawing->setHeight(90);
                        $drawing->setCoordinates("B{$row}");
                        $drawing->setOffsetX(5);
                        $drawing->setOffsetY(5);
                        $drawing->setWorksheet($sheet);
                        $sheet->getRowDimension($row)->setRowHeight(80);

                        $inserted = true;
                        break;
                    }

                    // Si no lo encontramos local, usa la URL pública
                    if (! $inserted && $prod->RutaImagen) {
                        $response = Http::timeout(10)->get($prod->RutaImagen);
                        if ($response->ok()) {
                            $gd    = imagecreatefromstring($response->body());
                            $thumb = imagescale($gd, 120, 120);
                            imagedestroy($gd);

                            $mem = new MemoryDrawing();
                            $mem->setName("Img{$row}");
                            $mem->setDescription($prod->Nombre);
                            $mem->setImageResource($thumb);
                            $mem->setRenderingFunction(MemoryDrawing::RENDERING_PNG);
                            $mem->setMimeType(MemoryDrawing::MIMETYPE_PNG);
                            $mem->setResizeProportional(false);
                            $mem->setWidth(120);
                            $mem->setHeight(120);
                            $mem->setCoordinates("B{$row}");
                            $mem->setOffsetX(5);
                            $mem->setOffsetY(5);
                            $mem->setWorksheet($sheet);
                            $sheet->getRowDimension($row)->setRowHeight(90);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("Fila {$row}, imagen: {$e->getMessage()}");
                }

                $row++;
            }

            // 5) Devolver en streaming
            $writer = new Xlsx($spreadsheet);
            $response = new StreamedResponse(function () use ($writer) {
                $writer->save('php://output');
            });

            $fileName = 'lista_precios_' . now()->format('Ymd_His') . '.xlsx';
            $disposition = $response->headers->makeDisposition('attachment', $fileName);

            $response->headers->set(
                'Content-Type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );
            $response->headers->set('Content-Disposition', $disposition);

            return $response;
        } catch (\Throwable $e) {
            Log::error("ExportListaPrecioCadenas ERROR: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error'   => 'Export failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
