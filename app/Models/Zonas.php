<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zonas extends Model
{
    use HasFactory;

    protected $table = 'w004_zona';

    public $timestamps = null;

    protected $fillable = [
        'Sector',
        'CodVendedor',
        'CodSupervisor',
        'Nombre'
    ];
}
