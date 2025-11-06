<?php

namespace App\Http\Resources\Vendedor\Presupuestos;

use Illuminate\Http\Resources\Json\JsonResource;

class PresupuestosResource extends JsonResource
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
            'id' => $this->id,
            'Documento' => $this->Documento,
            'Codcliente' => $this->Codcliente,
            'NombreCliente' => $this->NombreCliente,
            'Vendedor' => $this->Vendedor,
            'FormaPago' => $this->FormaPago,
            'FechaPresupuesto' => $this->FechaPresupuesto,
            'Monto' => $this->Monto,
            'Convertido' => $this->Convertido,
            'Descuento' => $this->Descuento,
            'DiasPromocion' => $this->DiasPromocion,
            'TipoPromocion' => $this->TipoPromocion,
            'MontoPromocion' => $this->MontoPromocion,
        ];
    }
}
