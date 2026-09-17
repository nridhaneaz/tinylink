<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUrlRequest;
use App\Models\Url;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class UrlController extends Controller
{
    /**
     * Return a paginated list of the authenticated user's URLs.
     *
     * GET /api/urls  [auth:sanctum]
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 10), 100);

        $urls = $request->user()
            ->urls()
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'URLs retrieved successfully.',
            'data'    => $urls,
        ]);
    }

    /**
     * Create a new shortened URL for the authenticated user.
     *
     * POST /api/urls  [auth:sanctum]
     */
    public function store(StoreUrlRequest $request): JsonResponse
    {
        $shortCode = $request->filled('custom_code')
            ? $request->custom_code
            : $this->generateUniqueShortCode();

        $url = $request->user()->urls()->create([
            'original_url' => $request->url,
            'short_code'   => $shortCode,
            'click_count'  => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'URL shortened successfully.',
            'data'    => [
                'id'           => $url->id,
                'original_url' => $url->original_url,
                'short_code'   => $url->short_code,
                'click_count'  => $url->click_count,
            ],
        ], 201);
    }

    /**
     * Return the details of a specific URL owned by the authenticated user.
     *
     * GET /api/urls/{id}  [auth:sanctum]
     */
    public function show(Request $request, Url $url): JsonResponse
    {
        Gate::authorize('view', $url);

        return response()->json([
            'success' => true,
            'message' => 'URL retrieved successfully.',
            'data'    => [
                'id'           => $url->id,
                'original_url' => $url->original_url,
                'short_code'   => $url->short_code,
                'click_count'  => $url->click_count,
                'created_at'   => $url->created_at,
                'updated_at'   => $url->updated_at,
            ],
        ]);
    }

    /**
     * Delete a URL owned by the authenticated user.
     *
     * DELETE /api/urls/{id}  [auth:sanctum]
     */
    public function destroy(Request $request, Url $url): JsonResponse
    {
        Gate::authorize('delete', $url);

        $url->delete();

        return response()->json([
            'success' => true,
            'message' => 'URL deleted successfully.',
        ]);
    }

    /**
     * Return click statistics for a URL owned by the authenticated user.
     *
     * GET /api/urls/{id}/stats  [auth:sanctum]  (Bonus 2)
     */
    public function stats(Request $request, Url $url): JsonResponse
    {
        Gate::authorize('view', $url);

        return response()->json([
            'success' => true,
            'message' => 'URL statistics retrieved successfully.',
            'data'    => [
                'url'         => $url->original_url,
                'short_code'  => $url->short_code,
                'click_count' => $url->click_count,
            ],
        ]);
    }

    /**
     * Generate a unique 6-character short code, retrying on collision.
     */
    private function generateUniqueShortCode(): string
    {
        do {
            $code = Str::random(6);
        } while (Url::where('short_code', $code)->exists());

        return $code;
    }
}
