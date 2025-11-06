<?php

namespace App\Http\Resources\Vendedor\CobranzasVendedor;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CobranzasCollection extends ResourceCollection
{
    /**
     * Transform the resource collection an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
