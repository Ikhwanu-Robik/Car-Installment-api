<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsValidator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentToken = $request->bearerToken();
        [$id, $token] = explode('|', $currentToken, 2);
        $tokenData = DB::table('personal_access_tokens')->find($id);

        if ($tokenData->tokenable_type != "App\\Models\\User") {
            return response()->json(["message" => "The user must be a validator"], 401);
        }
        
        return $next($request);
    }
}
