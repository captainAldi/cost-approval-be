<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class FinanceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //Cek ada Header Auth
        if ($request->header('Authorization')) {
            $user = User::where('api_token', $request->header('Authorization'))->first();

            $checkVerify = $user->role;

            if ($checkVerify != 'finance') {
                $pesan = "You're not an Finance !";
                return response()->json([
                    'message' => $pesan
                ], 401);
            } else {
                return $next($request);
            }
            
        } else {
            $pesan = "Please Login";
            return response()->json([
                'message' => $pesan
            ], 401);
        }
    }
}