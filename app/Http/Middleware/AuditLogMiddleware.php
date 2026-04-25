<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info("AuditLogMiddleware handle called for " . $request->method() . " " . $request->path());
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    public function terminate(Request $request, $response)
    {
        // Only log state-changing methods and successful responses
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) && $response->getStatusCode() < 400) {
            try {
                $user = auth('api')->user();
                $actorId = $user ? $user->id : null;
                $actorName = $user ? $user->full_name : 'System/Guest';

                $action = $request->method() . ' ' . $request->path();

                // Attempt to guess resource type from path
                $segments = $request->segments();
                $resourceType = count($segments) >= 3 ? strtoupper($segments[2]) : 'SYSTEM';

                DB::table('ipa_audit_log')->insert([
                    'actor_user_id' => $actorId,
                    'action'        => $action,
                    'resource_type' => $resourceType,
                    'resource_id'   => $request->route('id') ?? $request->route('user') ?? null,
                    'ip_address'    => $request->ip(),
                    'user_agent'    => $request->userAgent(),
                    'type'          => 'success',
                    'detail'        => "User {$actorName} performed {$action}",
                    'created_at'    => now(),
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to record audit log: " . $e->getMessage());
            }
        }
    }
}
