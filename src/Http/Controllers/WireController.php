<?php
namespace Ternobo\TernoboWire\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WireController extends Controller
{
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
}
