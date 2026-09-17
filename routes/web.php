<?php

use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'TinyLink API is running. See /api/* for endpoints.',
    ]);
});

/*
|--------------------------------------------------------------------------
| Public Short URL Redirect
|--------------------------------------------------------------------------
|
| This route is intentionally NOT behind auth:sanctum.
| It catches any {short_code} and redirects to the original URL.
| It is placed LAST so it doesn't swallow other routes.
|
*/
Route::get('/{short_code}', [RedirectController::class, 'redirect'])
    ->where('short_code', '[a-zA-Z0-9_-]+');
