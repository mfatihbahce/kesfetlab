<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ParentUser;
use Symfony\Component\HttpFoundation\Response;

class ParentApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token bulunamadı'
            ], 401);
        }

        try {
            // Token'ı veritabanında ara
            $parent = ParentUser::where('api_token', $token)
                               ->where('status', 'active')
                               ->first();

            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geçersiz token'
                ], 401);
            }

            // Veli kullanıcısını request'e ekle
            $request->attributes->set('user', $parent);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz token'
            ], 401);
        }

        return $next($request);
    }
}
