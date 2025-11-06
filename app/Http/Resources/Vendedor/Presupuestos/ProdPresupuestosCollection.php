<?php

namespace App\Http\Resources\Vendedor\Presupuestos;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProdPresupuestosCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
