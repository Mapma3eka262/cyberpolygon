<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckActiveTeam
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        
        if ($user->team && !$user->team->is_active) {
            if (!$request->routeIs('dashboard')) {
                return redirect()->route('dashboard')
                    ->with('error', 'Ваша команда деактивирована. Обратитесь к администратору.');
            }
        }

        return $next($request);
    }
}