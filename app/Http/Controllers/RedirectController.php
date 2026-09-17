<?php

namespace App\Http\Controllers;

use App\Models\Url;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class RedirectController extends Controller
{
    /**
     * Find the URL by short code, increment click count, and redirect.
     *
     * GET /{short_code}  (public, no authentication required)
     */
    public function redirect(string $shortCode): RedirectResponse|JsonResponse
    {
        $url = Url::where('short_code', $shortCode)->first();

        if (! $url) {
            return response()->json([
                'success' => false,
                'message' => 'Short URL not found.',
            ], 404);
        }

        // Safely increment the click count using an atomic DB-level increment
        $url->increment('click_count');

        return redirect()->away($url->original_url);
    }
}
