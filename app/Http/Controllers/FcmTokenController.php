<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['fcm_token' => 'required|string']);

        $request->user()->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json(['message' => 'Token enregistré']);
    }
}