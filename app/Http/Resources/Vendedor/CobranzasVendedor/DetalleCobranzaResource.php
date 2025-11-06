<?php

namespace App\Http\Resources\Vendedor\CobranzasVendedor;

use Illuminate\Http\Resources\Json\JsonResource;

class DetalleCobranzaResource extends JsonResource
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
            'FormaPago' => $this->FormaPago, //Transferencia o Efectivo
            'DocumentoFormaPago' => $this->DocumentoFormaPago, //Referencia
            'BancoPago' => $this->BancoPago,
            'FechaPago' => $this->FechaPago,
            'MontoParcial' => $this->MontoParcial,
            'Recibo' => $this->Recibo,
            'TasadelDia' => $this->TasadelDia,
        ];
    }
}
