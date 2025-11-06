<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('e010__pedidoEncabezado', function (Blueprint $table) {
            $table->string('Documento', 10);
            $table->string('Latitud', 30)->nullable();
            $table->string('Longitud', 30)->nullable();
            $table->string('Codcliente', 20);
            $table->string('NombreCliente', 200);
            $table->string('Vendedor', 10);
            $table->string('TipoPedido', 20);
            $table->string('FormaPago', 20);
            $table->dateTime('fechayhora');
            $table->string('NumeroOrden', 20)->nullable();
            $table->string('Responsable', 100)->nullable();
            $table->string('Comentarios', 200)->nullable();
            $table->int('Descargado');
            $table->double('Monto', 18, 2)->nullable();
            $table->int('AplicaDescuento');
            $table->string('Equipo', 100)->nullable();
            $table->string('Version', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('e010__pedido_encabezado');
    }
};
