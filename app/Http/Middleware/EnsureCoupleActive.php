<?php

namespace App\Http\Middleware;

use App\Models\Couple;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureCoupleActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $couple = $user->activeCouple()->first();

        if (!$couple || $couple->status !== 'active') {
            return response()->json([
                'message' => 'No active couple found'
            ], 403);
        }

        $request->merge(['couple' => $couple]);

        return $next($request);
    }
}