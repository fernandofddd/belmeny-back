<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MetasResource extends JsonResource
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
            'vendedor' => $this->vendedor,
            'vert' => $this->vert,
            'ingco' => $this->ingco,
            'imou' => $this->imou,
            'wadfow' => $this->wadfow,
            'quilosa' => $this->quilosa,
            'fleximatic' => $this->fleximatic,
            'articulo' => $this->articulo,
            'global' => $this->global,
            'zona' => $this->zona,
            'total_vendido' => $this->total_vendido,
            'PeriodoInicio' => $this->PeriodoInicio,
            'PeriodoFin' => $this->PeriodoFin,
            'VentasIngco' => $this->VentasIngco,
            'VentasVert' => $this->VentasVert,
            'VentasWadfow' => $this->VentasWadfow,
        ];
    }
}
