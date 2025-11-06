<?php

namespace App\Http\Controllers\Api;

// Controllers
use App\Http\Controllers\Controller;

// Http and support
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Models
use App\Models\Video;
use App\Models\VideoAudit;

class VideoController extends Controller
{
    //Registro de solicitudes
    public function postNewVideo(Request $request)
    {
        $video = new Video();

        $video->link = $request->link;
        $video->titulo = $request->titulo;
        $video->descripcion = $request->descripcion;
        $video->fecha_subida = $request->fecha_subida;
        $video->save();
    }

    public function getVideos(Request $request)
    {
        $video = DB::table("w040_videos")
            ->select("*")
            ->orderBy("fecha_subida", "DESC")
            ->get();

        return response()->json($video);
    }

    public function registerOrUpdateViewer(Request $request) {

        // $registerView = new VideoAudit();
        $now = Carbon::now();

        if($request->startedOrEnded === 'Start') {
            $registerView = VideoAudit::updateOrCreate(
                ["usuario" => $request->usuario, "id_video" => $request->id_video],
                ["fecha_inicio" => $now, "iniciado" => 1]
            );
            // $registerView->usuario = $request->usuario;
            // $registerView->usuario = $request->usuario;
            // $registerView->usuario = $request->usuario;
            // $registerView->usuario = $request->usuario;
        } else {
            // $registerView = DB::table("");
            $registerView = VideoAudit::updateOrCreate(
                ["usuario" => $request->usuario, "id_video" => $request->id_video],
                ["fecha_fin" => $now, "finalizado" => 1]
            );
        }
    }
}
