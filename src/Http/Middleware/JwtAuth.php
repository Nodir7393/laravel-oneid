<?php

namespace Nodir\OneId\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Nodir\OneId\Services\JwtService;
use Symfony\Component\HttpFoundation\Response;

class JwtAuth
{
    public function __construct(private JwtService $jwt) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->jwt->extractToken($request);
        if (!$token) return $this->fail('Token topilmadi', 401);

        try {
            $claims = $this->jwt->parse($token);
        } catch (\Throwable) {
            return $this->fail('Token yaroqsiz', 401);
        }

        $modelClass = config('oneid.user_model');
        $user = $modelClass::with('roles.permissions')->find($claims['sub'] ?? null);

        if (!$user) return $this->fail('Foydalanuvchi topilmadi', 401);
        if ($user->isBlocked()) return $this->fail('Akkauntingiz bloklangan', 403);

        if ($user->isPending()) {
            return response()->json([
                'status' => 403, 'success' => false,
                'data' => ['message' => 'Akkauntingiz hali tasdiqlanmagan', 'user_status' => 'pending'],
            ], 403);
        }

        auth()->setUser($user);
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }

    private function fail(string $msg, int $code): Response
    {
        return response()->json(['status' => $code, 'success' => false, 'data' => ['message' => $msg]], $code);
    }
}
