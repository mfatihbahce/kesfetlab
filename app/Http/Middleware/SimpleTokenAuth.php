<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ParentUser;

class SimpleTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token bulunamadı'
            ], 401);
        }

        try {
            // Önce basit API token kontrolü (ParentUser için)
            $parent = ParentUser::where('api_token', $token)
                               ->where('status', 'active')
                               ->first();
            
            if ($parent) {
                $request->merge(['user' => $parent]);
                return $next($request);
            }

            // Eğer parent token değilse, instructor token kontrolü
            $decoded = base64_decode($token);
            $parts = explode('|', $decoded);
            
            if (count($parts) !== 3) {
                throw new \Exception('Geçersiz token formatı');
            }

            $instructorId = $parts[0];
            $timestamp = $parts[1];
            $phone = $parts[2];

            // Token'ın 24 saat geçerli olması
            if (time() - $timestamp > 86400) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token süresi dolmuş'
                ], 401);
            }

            $instructor = User::where('id', $instructorId)
                             ->where('phone', $phone)
                             ->where('role', 'instructor')
                             ->where('is_active', true)
                             ->first();

            if (!$instructor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geçersiz token'
                ], 401);
            }

            // Request'e instructor'ı ekle
            $request->merge(['user' => $instructor]);
            
            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz token'
            ], 401);
        }
    }
}
