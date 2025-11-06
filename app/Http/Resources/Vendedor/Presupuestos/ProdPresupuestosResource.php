<?php

namespace App\Http\Resources\Vendedor\Presupuestos;

use Illuminate\Http\Resources\Json\JsonResource;

class ProdPresupuestosResource extends JsonResource
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
            'CodigoCliente' => $this->CodigoCliente,
            'Codigo' => $this->Codigo,
            'Nombre' => $this->Nombre,
            'PrecioUnit' => $this->PrecioUnit,
            'Cantidad' => $this->Cantidad,
            'Subtotal' => $this->Subtotal,
            'Agencia' => $this->Agencia,
            'Lubricante' => $this->Lubricante,
        ];
    }
}
