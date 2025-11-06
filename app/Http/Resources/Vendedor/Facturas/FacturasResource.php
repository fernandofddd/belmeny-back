<?php

namespace App\Http\Resources\Vendedor\Facturas;

use Illuminate\Http\Resources\Json\JsonResource;

class FacturasResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'Documento' => $this->Documento,
            'Agencia' => $this->Agencia,
            'codcliente' => $this->codcliente,
            'nombrecli' => $this->nombrecli,
            'CodigoVendedor' => $this->CodigoVendedor,
            'FechaDocumento' => $this->FechaDocumento,
            'DiasCredito' => $this->DiasCredito,
            'FechaVencimiento' => $this->FechaVencimiento,
            'TotalFact' => $this->TotalFact,
            'BaseImponible' => $this->BaseImponible,
            'Flete' => $this->Flete,
            'Abonado' => $this->Abonado,
            'TasaIVA' => $this->TasaIVA,
            'Alicuota' => $this->Alicuota,
            'Descuento' => $this->Descuento,
            'RetencionIVA' => $this->RetencionIVA,
            'TotalPend' => $this->TotalPend,
            'Estatus' => $this->Estatus,
            'DiasVencido' => $this->DiasVencido
        ];
    }
}
