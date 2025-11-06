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
        Schema::create('b040_usuarios', function (Blueprint $table) {
            $table->string('Usuario')->primary()->unique();
            $table->string('Nombre');
            $table->string('password');
            $table->string('correo')->nullable();
            $table->timestamp('correo_verified_at')->nullable();
            $table->string('CodVendedor')->nullable();
            $table->string('CodSupervisor')->nullable();
            $table->string('CodGerente')->nullable();
            $table->string('Activo');
            $table->string('Version')->nullable();
            $table->rememberToken();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b040_usuarios');
    }
};
