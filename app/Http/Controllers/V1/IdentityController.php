<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\IdentityService;
use Illuminate\Http\Request;

class IdentityController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $service = app(IdentityService::class);
        $response = $service->register();

        return response()->json([
            'token' => $response,
        ]);
    }
}
