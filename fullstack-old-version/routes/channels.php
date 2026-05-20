<?php

use App\Broadcasting\PostChannel;
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

Broadcast::channel('App.Models.User.{id}', fn ($user, $id) => (int) $user->id === (int) $id);

Broadcast::channel('post.{post}', PostChannel::class);

/*
|--------------------------------------------------------------------------
| Lucky Draw Channels
|--------------------------------------------------------------------------
*/

// Public channel for display screens (no auth required)
Broadcast::channel('lucky-draw.{luckyDrawId}', function () {
    return true; // Public read-only channel for display screens
});

// Private channel for admin controls
Broadcast::channel('private-lucky-draw.{luckyDrawId}', function ($user, $luckyDrawId) {
    // Check if user is authenticated and has admin role
    return $user && ($user->role === 'admin' || $user->isSysAdmin());
});
