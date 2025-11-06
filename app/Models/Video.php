<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $table = 'w040_videos';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'link',
        'titulo',
        'descripcion',
        'fecha_subida'
    ];
}
