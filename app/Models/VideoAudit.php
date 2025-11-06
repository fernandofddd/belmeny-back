<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoAudit extends Model
{
    use HasFactory;

    protected $table = 'w041_videos_auditoria';

    public $timestamps = false;
    public $primaryKey = 'id';
    protected $fillable = [
        'usuario',
        'fecha_inicio',
        'fecha_fin',
        'id_video',
        'iniciado',
        'finalizado',
        'descargado'
    ];
}
