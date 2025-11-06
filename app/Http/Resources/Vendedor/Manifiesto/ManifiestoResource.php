<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ManifiestoResource extends JsonResource
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
            'Estado' => $this->Estado,
            'Chofer' => $this->Chofer,
            'ChoferCI' => $this->ChoferCI,
            'Vehiculo' => $this->Vehiculo,
            'Placa' => $this->Placa,
            'Marca' => $this->Marca,
            'Color' => $this->Color,
            'FechaSalida' => $this->FechaSalida,
            'EmpresaTransporte' => $this->EmpresaTransporte,
            'Observacion' => $this->Observacion,
        ];
    }
}
