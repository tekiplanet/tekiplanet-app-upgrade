<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        \Log::info('Storing device token', [
            'token' => $request->token,
            'user_id' => auth()->id()
        ]);

        $validated = $request->validate([
            'token' => 'required|string'
        ]);

        DeviceToken::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'token' => $validated['token']
            ],
            [
                'platform' => 'fcm',
                'last_used_at' => now()
            ]
        );

        return response()->json(['message' => 'Token stored successfully']);
    }
} 