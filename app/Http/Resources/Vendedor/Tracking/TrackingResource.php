<?php

namespace App\Http\Resources\Vendedor\Tracking;

use Illuminate\Http\Resources\Json\JsonResource;

class TrackingResource extends JsonResource
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
            'NombreCliente' => $this->NombreCliente,
            'CodVendedor' => $this->CodVendedor,
            'FechaCreacion' => $this->FechaCreacion,
            'FinDepositario' => $this->FinDepositario,
            'FinEmpacador' => $this->FinEmpacador,
            'Facturacion' => $this->Facturacion,
            'FechaEnvio' => $this->FechaEnvio,
            'FechaAnulacion' => $this->FechaAnulacion,
            'FechaSalida' => $this->FechaSalida,
            'Cajas' => $this->Cajas,
            'Bolsas' => $this->Bolsas, 
            'Estado' => $this->Estado,
            'DocManifiesto' => $this->DocManifiesto,
        ];
    }
}
