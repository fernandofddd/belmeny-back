<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditoriaPagina extends Model
{
    use HasFactory;

    protected $table = 'w042_auditoria_pagina_usuarios';

    public $timestamps = false;
    
    protected $fillable = [
        'usuario',
        'presupuesto_borrado',
        'fecha',
    ];
}
