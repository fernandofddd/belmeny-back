<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientesResource extends JsonResource
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
            'Empresa' => $this->Empresa,
            'Nombre' => $this->Nombre,
            'Rif' => $this->Rif,
            'Vendedor' => $this->Vendedor,
            'DireccionFiscal' => $this->DireccionFiscal,
            'Telefono1' => $this->Telefono1,
            'Correo' => $this->Correo,
            'Limite' => $this->Limite,
            'Descuento' => $this->Descuento,
            'Dias' => $this->Dias,
            'Ventas' => $this->Ventas,
            'Cobranzas' => $this->Cobranzas,
            'Devolucion' => $this->Devolucion,
            'Catalogo' => $this->Catalogo,
            'SaldoPendiente' => $this->SaldoPendiente,
            'ListaPrecios' =>$this->ListaPrecios,
        ];
    }
}
