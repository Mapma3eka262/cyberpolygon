<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckCaptain
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        if (!$user->isCaptain()) {
            return redirect()->route('dashboard')
                ->with('error', 'Эта функция доступна только капитанам команд.');
        }

        return $next($request);
    }
}