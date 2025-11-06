<?php

namespace App\Http\Resources\Vendedor\Facturas;

use Illuminate\Http\Resources\Json\JsonResource;

class ProdFacturasResource extends JsonResource
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
            'Grupo' => $this->Grupo,
            'fechadoc' => $this->fechadoc,
            'Cliente' => $this->Cliente,
            'Codigo' => $this->Codigo,
            'Nombre' => $this->Nombre,
            'Cantidad' => $this->Cantidad,
            'TasaIVA' => $this->TasaIVA,
            'PrecioUnitario' => $this->PrecioUnitario,
            'SubImpuesto' => $this->SubImpuesto,
            'Subtotal' => $this->Subtotal,
            'CodigoVendedor' => $this->CodigoVendedor,
            'CodCliente' => $this->CodCliente,
        ];
    }
}
