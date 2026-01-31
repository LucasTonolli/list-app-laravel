<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasHeader('token')) return response()->json(['message' => 'Token not found'], 401);

        $token = $request->header('token');

        if (uuid_is_valid($token) === false) return response()->json(['message' => 'Invalid token'], 401);

        if (!User::where('uuid', $token)->exists()) return response()->json(['message' => 'Invalid token'], 401);

        return $next($request);
    }
}
