<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckDirecteurAffectation
{
    public function handle(Request $request, Closure $next)
    {
        $personnel = $request->route('personnel');

        if ($personnel && $personnel->isDirecteur()) {
            $affectationsActives = $personnel->centres()
                ->wherePivot('statut', 'Actif')
                ->wherePivot('poste', 'Directeur')
                ->count();

            if ($affectationsActives >= 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Un directeur ne peut avoir plus de deux affectations actives',
                ], 422);
            }
        }

        return $next($request);
    }
}
