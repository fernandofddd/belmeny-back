<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ImageController extends Controller
{
    public function postImg(Request $request)
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = $file->getClientOriginalName();
            $finalName = date('His') . $filename;

            $request->file('image')->storeAs('images/', $finalName, 'public');

            $rutaImagen = new Image;

            $rutaImagen->image_name = 'http://127.0.0.1:8000/storage/images/' . $finalName;
            $rutaImagen->save();

            return response()->json($rutaImagen);
        } else {
            return response()->json(["message" => "You must select an image"], 404);
        }
    }

    public function getImg(Request $request)
    {
        $img = DB::table('images')
            ->select('*')
            ->where('id', '=', $request->id)
            ->get();

        return response()->json($img);
    }
}
