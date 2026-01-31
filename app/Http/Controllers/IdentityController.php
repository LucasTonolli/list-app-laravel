<?php

namespace App\Http\Controllers;

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
