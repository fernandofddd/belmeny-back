<?php

namespace App\Http\Resources\Vendedor\Manifiesto;

use Illuminate\Http\Resources\Json\JsonResource;

class ManifiestoDetalleResource extends JsonResource
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
            'DocumentoDetalle' => $this->DocumentoDetalle,
            'DocPedido' => $this->DocPedido,
            'DocFactura' => $this->DocFactura,
            'NombreCliente' => $this->NombreCliente,
            'Cliente' => $this->Cliente,
            'FFacturacion' => $this->FFacturacion,
            'Zona' => $this->Zona,
            'SubZona' => $this->SubZona,
            'Cajas' => $this->Cajas,
            'Bolsas' => $this->Bolsas,
            'BaseImponible' => $this->BaseImponible,
            'FechaSalidaDetalle' => $this->FechaSalidaDetalle,
            'DireccionDespacho' => $this->DireccionDespacho,
            'Vendedor' => $this->Vendedor
        ];
    }
}
