<?php

namespace App\Http\Resources\Vendedor\CobranzasVendedor;

use Illuminate\Http\Resources\Json\JsonResource;

class CobranzasResource extends JsonResource
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
            'CodCliente' => $this->CodCliente,
            'NombreCliente' => $this->NombreCliente,
            'FechaCobranza' => $this->FechaCobranza,
            'Responsable' => $this->Responsable,
            'TotalCobranza' => $this->TotalCobranza,
            'Comentarios' => $this->Comentarios,
            'Usuario' => $this->Usuario,
            'DocumentoAfectado' => $this->DocumentoAfectado,
            'MontoFactura' => $this->MontoFactura,
            'Descargado' => $this->Descargado,
        ];
    }
}
