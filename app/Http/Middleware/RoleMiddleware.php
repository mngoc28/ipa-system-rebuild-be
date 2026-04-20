<?php

namespace App\Http\Middleware;

use App\Enums\HttpStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => __('auth.unauthorized')], HttpStatus::UNAUTHORIZED->value);
        }

        $allowed = [];
        foreach ($roles as $r) {
            $allowed = array_merge($allowed, explode(',', $r));
        }

        if (!$user->hasRole($allowed)) {
            return response()->json(['status' => 'error', 'message' => __('auth.forbidden')], HttpStatus::FORBIDDEN->value);
        }

        return $next($request);
    }
}
