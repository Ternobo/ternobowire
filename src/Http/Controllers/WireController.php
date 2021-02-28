<?php
namespace Ternobo\TernoboWire\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class WireController extends Controller
{

    public function getData($token, Request $request)
    {
        $data = Cache::pull($token);
        // dd($data);
        if ($data != null) {
            return response()->json(json_decode($data));
        }
        return abort(404);
    }

    public function getUser(Request $request)
    {
        if (Auth::check()) {
            return response()->json([
                "user" => Auth::user(),
            ]);
        } else {
            return response()->json([
                "user" => null,
            ]);
        }
    }
    public function getToken()
    {
        return response()->json([
            "token" => csrf_token(),
        ]);
    }
}
