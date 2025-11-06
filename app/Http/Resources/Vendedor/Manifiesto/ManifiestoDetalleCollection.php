<?php

namespace App\Http\Resources\Vendedor\Manifiesto;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ManifiestoDetalleCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
