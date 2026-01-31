<?php

namespace App\Http\Controllers;

use App\Services\IdentitiesService;
use Illuminate\Http\Request;

class Identities extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $service = app(IdentitiesService::class);
        $response = $service->register();

        return response()->json([
            'token' => $response,
        ]);
    }
}
