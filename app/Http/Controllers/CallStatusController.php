<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CallStatusController extends Controller
{
    public function __invoke(Request $_request)
    {
        return response()->json(['success' => true], 200);
    }
}
