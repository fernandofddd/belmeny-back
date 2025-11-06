<?php

namespace App\Http\Resources\Vendedor\Pedidos;

use Illuminate\Http\Resources\Json\JsonResource;

class PedidosResource extends JsonResource
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
            'Latitud' => $this->Latitud,
            'Longitud' => $this->Longitud,
            'Codcliente' => $this->Codcliente,
            'NombreCliente' => $this->NombreCliente,
            'Vendedor' => $this->Vendedor,
            'TipoPedido' => $this->TipoPedido,
            'FormaPago' => $this->FormaPago,
            'fechayhora' => $this->fechayhora,
            'NumeroOrden' => $this->NumeroOrden,
            'Responsable' => $this->Responsable,
            'Comentarios' => $this->Comentarios,
            'Descargado' => $this->Descargado,
            'Monto' => $this->Monto,
            'AplicaDescuento' => $this->AplicaDescuento,
            'Equipo' => $this->Equipo,
            'Version' => $this->Version,
            'Descuento' => $this->Descuento,
        ];
    }
}
