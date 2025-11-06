<?php

namespace App\Http\Resources\Vendedor\Pedidos;

use Illuminate\Http\Resources\Json\JsonResource;

class ProdPedidosResource extends JsonResource
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
            'ListaPrecio' => $this->ListaPrecio,
            'PrecioUnit' => $this->PrecioUnit,
            'Cantidad' => $this->Cantidad,
            'Subtotal' => $this->Subtotal,
            'FechaHora' => $this->FechaHora,
            'Descargado' => $this->Descargado,
            'Agencia' => $this->Agencia
        ];
    }
}
