<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    public static function for(UserRole|array $roles): string
    {
        if (is_array($roles)) $roles = implode('|', array_map(fn($role) => $role->value, $roles));
        else $roles = $roles->value;

        return static::class . ":" . $roles;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (!in_array($request->user()->role->value, explode('|', $roles))) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
