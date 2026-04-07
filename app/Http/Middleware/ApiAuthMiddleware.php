<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
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
            // Token'ı decode et
            $decoded = base64_decode($token);
            $parts = explode('|', $decoded);

            if (count($parts) !== 3) {
                throw new \Exception('Geçersiz token formatı');
            }

            $userId = $parts[0];
            $timestamp = $parts[1];
            $phone = $parts[2];

            // Token'ın 24 saat geçerli olup olmadığını kontrol et
            if (time() - $timestamp > 86400) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token süresi dolmuş'
                ], 401);
            }

            // Kullanıcıyı bul
            $user = User::where('id', $userId)
                       ->where('phone', $phone)
                       ->where('role', 'instructor')
                       ->where('is_active', true)
                       ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geçersiz token'
                ], 401);
            }

            // Kullanıcıyı request'e ekle
            $request->attributes->set('user', $user);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz token'
            ], 401);
        }

        return $next($request);
    }
}
