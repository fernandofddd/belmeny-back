<?php

namespace App\Http\Resources\Vendedor\Articulos;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticulosResource extends JsonResource
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
            'Codigo' => $this->Codigo,
            'Nombre' => $this->Nombre,
            'Precio1' => $this->Precio1,
            'Precio2' => $this->Precio2,
            'Precio3' => $this->Precio3,
            'Precio4' => $this->Precio4,
            'Precio5' => $this->Precio5,
            'Existencia' => $this->Existencia,
            'RutaImagen' => $this->RutaImagen,
            'VentaMinima' => $this->VentaMinima,
        ];
    }
}
