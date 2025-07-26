<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Illuminate\Auth\GenericUser;

class VerifyTokenAndScopes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$requiredScopes)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token missing'], 401);
        }

        try {
            $publicKey = file_get_contents(storage_path('oauth-public.key'));
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));

            // Ambil data dari token
            $userId = $decoded->sub ?? $decoded->user_id ?? null;

            if (!$userId) {
                return response()->json(['error' => 'User ID not found in token'], 401);
            }

            // Inject fake user ke request
            $userArray = [
                'id' => $userId,
                // 'name' => $decoded->name ?? null,
                // 'email' => $decoded->email ?? null,
                // Tambahkan field lain jika perlu
            ];

            $fakeUser = new GenericUser($userArray);

            $request->setUserResolver(function () use ($fakeUser) {
                return $fakeUser;
            });

            $scopes = $decoded->scopes ?? $decoded->scope ?? [];

            // Ubah ke array kalau bentuknya string
            if (is_string($scopes)) {
                $scopes = explode(' ', $scopes);
            }

            // Check if at least one required scope is present
            $matched = array_intersect($requiredScopes, $scopes);

            if (count($matched) === 0) {
                return response()->json([
                    'error' => 'None of the required scopes are present',
                    // 'required' => $requiredScopes,
                    // 'available' => $scopes,
                ], 403);
            }

            $request->attributes->add(['jwt_payload' => (array) $decoded]);

        } catch (ExpiredException $e) {
            return response()->json([
                'error' => 'Token expired: ' . $e->getMessage()
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid token: ' . $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}