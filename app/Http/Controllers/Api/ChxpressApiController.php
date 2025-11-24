<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ChxpressApiController extends Controller
{
    /**
     * Normaliza un RIF / CodCliente extraído desde distintas fuentes.
     * Devuelve SOLO los dígitos del RIF (por ejemplo: "500572220") o NULL si no se puede extraer.
     */
    private function normalizeRifDigits(?string $raw)
    {
        if (!$raw) return null;

        $s = trim((string)$raw);

        // Si hay prefijo letra típico (J/V/G/E/P) preferimos interpretar como RIF
        if (preg_match('/[JVGEP]/i', $s)) {
            $justDigits = preg_replace('/\D+/', '', $s);
            if (strlen($justDigits) >= 6) return $justDigits;
        }

        // Buscar primer bloque de 6-12 dígitos
        if (preg_match('/([0-9]{6,12})/', $s, $m)) {
            return $m[1];
        }

        // último recurso: eliminar todo lo no-dígito y devolver si >=6
        $justDigits = preg_replace('/\D+/', '', $s);
        if (strlen($justDigits) >= 6) return $justDigits;

        return null;
    }

    /**
     * Comprueba si dos cadenas de dígitos RIF "coinciden" de forma tolerante.
     * Retorna true si una es igual a la otra o si una termina en la otra (sufijo).
     */
    private function rifDigitsMatchTolerant(?string $a, ?string $b)
    {
        if (!$a || !$b) return false;

        $a = preg_replace('/\D+/', '', (string)$a);
        $b = preg_replace('/\D+/', '', (string)$b);

        if ($a === '' || $b === '') return false;

        if ($a === $b) return true;

        $la = strlen($a);
        $lb = strlen($b);

        if ($la >= $lb) {
            if (substr($a, -$lb) === $b) return true;
        } else {
            if (substr($b, -$la) === $a) return true;
        }

        return false;
    }

    /**
     * GET /api/chxpress/guias
     */
    public function obtenerGuias(Request $request)
    {
        $factura = $request->query('factura');       
        $cliente  = $request->query('cliente');      
        $codVendedor = $request->query('CodVendedor'); 

        $cacheKey = 'chxpress_guias_latest' . ($codVendedor ? "_v{$codVendedor}" : '');

        $guiasRaw = Cache::remember($cacheKey, 20, function () {
            try {
                $resp = Http::timeout(8)->get('https://chxpress.com.ve/system/apiweb/');
                if ($resp->successful()) {
                    return $resp->json();
                }
            } catch (\Throwable $e) {
                // silencio intencional
            }
            return [];
        });

        if (is_null($guiasRaw)) $guiasRaw = [];
        if (!is_array($guiasRaw)) $guiasRaw = [$guiasRaw];

        $preFiltered = collect($guiasRaw)->filter(function ($g) use ($factura, $cliente) {
            $ok = true;
            if ($factura) {
                $ok = $ok && ((string)($g['factura_guia'] ?? '') === (string)$factura);
            }
            if ($cliente) {
                $cn = mb_strtolower(trim($cliente));
                $dest = mb_strtolower(trim($g['destinatario_nombre'] ?? ''));
                $rem  = mb_strtolower(trim($g['remitente_nombre'] ?? ''));
                $ok = $ok && (str_contains($dest, $cn) || str_contains($rem, $cn));
            }
            return $ok;
        })->values()->all();

        if (!$codVendedor) {
            return response()->json([
                'success' => true,
                'count' => count($preFiltered),
                'data' => array_values($preFiltered)
            ]);
        }

        // Facturas del vendedor
        $invoiceDocs = DB::table('e100_facturaencabezado')
            ->select('Documento', 'CodCliente')
            ->where('CodigoVendedor', $codVendedor)
            ->get();

        if ($invoiceDocs->isEmpty()) {
            return response()->json(['success' => true, 'count' => 0, 'data' => []]);
        }

        $invoiceSuffixes = [];   // sufijos de Documento (últimos 5 dígitos)
        $vendorRifDigits = [];   // rif normalizados del vendedor

        foreach ($invoiceDocs as $r) {
            $doc = (string)$r->Documento;
            $digits = preg_replace('/\D+/', '', $doc);
            if ($digits !== '') {
                $suf = substr($digits, -5);
                $suf = str_pad($suf, 5, '0', STR_PAD_LEFT);
                $invoiceSuffixes[$suf] = true;
            }

            $rawRif = property_exists($r, 'CodCliente') ? $r->CodCliente : null;
            if (!$rawRif) {
                $rawRif = isset($r->codcliente) ? $r->codcliente : null;
            }
            $rifDigits = $this->normalizeRifDigits((string)$rawRif);
            if ($rifDigits) {
                $vendorRifDigits[$rifDigits] = true;
            }
        }

        // Extra RIFs desde e100_encabezado (si existe)
        try {
            $extraRifs = DB::table('e100_encabezado')
                ->select('CodCliente')
                ->whereIn('Documento', $invoiceDocs->pluck('Documento')->toArray())
                ->pluck('CodCliente')
                ->toArray();

            foreach ($extraRifs as $r) {
                $d = $this->normalizeRifDigits((string)$r);
                if ($d) $vendorRifDigits[$d] = true;
            }
        } catch (\Throwable $ex) {
            // ignorar
        }

        // ------- Cruce: solo incluir si cumple condiciones -------
        $matches = [];
        foreach ($preFiltered as $g) {
            $include = false;

            // obtener rem/dest en formato solo dígitos
            $rem = preg_replace('/\D+/', '', (string)($g['remitente_rif'] ?? ''));
            $dest = preg_replace('/\D+/', '', (string)($g['destinatario_rif'] ?? ''));

            // 1) comparar sufijo factura (tomando solo dígitos de factura_guia)
            $factExt = preg_replace('/\D+/', '', (string)($g['factura_guia'] ?? ''));
            if ($factExt !== '') {
                $sufExt = str_pad(substr($factExt, -5), 5, '0', STR_PAD_LEFT);
                if (isset($invoiceSuffixes[$sufExt])) {
                    // Si tenemos RIFs del vendedor, no aceptamos solo por sufijo:
                    if (count($vendorRifDigits) === 0) {
                        // vendedor sin RIF conocido -> aceptamos por sufijo
                        $include = true;
                    } else {
                        // vendedor tiene RIFs -> validar que rem/dest coincidan con alguno de esos RIFs
                        foreach (array_keys($vendorRifDigits) as $vr) {
                            if ($rem !== '' && $this->rifDigitsMatchTolerant($rem, $vr)) {
                                $include = true;
                                break;
                            }
                            if ($dest !== '' && $this->rifDigitsMatchTolerant($dest, $vr)) {
                                $include = true;
                                break;
                            }
                        }
                        // si no se encuentra coincidencia de RIF aquí, NO incluir (aunque el sufijo coincida)
                    }
                }
            }

            // 2) comparar rif remitente / destinatario (por dígitos) si no incluido aún
            if (!$include && count($vendorRifDigits) > 0) {
                foreach (array_keys($vendorRifDigits) as $vr) {
                    if ($rem !== '' && $this->rifDigitsMatchTolerant($rem, $vr)) {
                        $include = true;
                        break;
                    }
                    if ($dest !== '' && $this->rifDigitsMatchTolerant($dest, $vr)) {
                        $include = true;
                        break;
                    }
                }
            }

            if ($include) {
                $matches[] = $g;
            }
        }

        // Ordenar por factura_guia, destinatario_nombre, fecha_salida desc
        usort($matches, function ($a, $b) {
            $fa = (string)($a['factura_guia'] ?? '');
            $fb = (string)($b['factura_guia'] ?? '');
            $cmp = strcmp($fa, $fb);
            if ($cmp !== 0) return $cmp;

            $ca = (string)($a['destinatario_nombre'] ?? '');
            $cb = (string)($b['destinatario_nombre'] ?? '');
            $cmp2 = strcmp($ca, $cb);
            if ($cmp2 !== 0) return $cmp2;

            $da = isset($a['fecha_salida']) && $a['fecha_salida'] ? strtotime($a['fecha_salida']) : 0;
            $db = isset($b['fecha_salida']) && $b['fecha_salida'] ? strtotime($b['fecha_salida']) : 0;
            return $db - $da;
        });

        return response()->json([
            'success' => true,
            'count' => count($matches),
            'data' => array_values($matches)
        ]);
    }
}
