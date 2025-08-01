<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function (
    $user, $id
) {
    if (!$user) {
        return false;
    }
    return (int) $user->id === (int) $id;
});

Broadcast::channel('private-notifications.{userId}', function ($user, $userId) {
    if (!$user) {
        return false;
    }
    \Log::info('Channel authorization check', [
        'user_id' => $user->id,
        'requested_userId' => $userId,
        'socket_id' => request()->socket_id,
        'headers' => request()->headers->all()
    ]);

    try {
        $authorized = (string) $user->id === (string) $userId;
        \Log::info('Authorization result', ['authorized' => $authorized]);
        return $authorized;
    } catch (\Exception $e) {
        \Log::error('Channel authorization error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return false;
    }
});

Broadcast::channel('quote.{quoteId}', function ($user, $quoteId) {
    if (!$user) {
        return false;
    }
    \Log::info('Channel authorization attempt', [
        'user_id' => $user->id,
        'quote_id' => $quoteId,
        'time' => now()
    ]);
    return true; // For testing, allow all
});
