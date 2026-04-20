<?php

namespace Nodir\OneId\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cookie;
use Nodir\OneId\Services\JwtService;
use Nodir\OneId\Services\OneIdService;
use Nodir\OneId\Services\UserRegistrar;

class OneIdController extends Controller
{
    public function __construct(
        private OneIdService $oneId,
        private JwtService $jwt,
        private UserRegistrar $registrar,
    ) {}

    /**
     * GET /api/auth/oneid/url
     * OneID authorize URL qaytarish (SPA uchun)
     */
    public function url(): JsonResponse
    {
        return response()->json([
            'status' => 200, 'success' => true,
            'data' => ['url' => $this->oneId->buildAuthorizeUrl()],
        ]);
    }

    /**
     * GET /api/auth/oneid/redirect
     * OneID ga 302 redirect (brauzer uchun)
     */
    public function redirect()
    {
        return redirect()->away($this->oneId->buildAuthorizeUrl());
    }

    /**
     * GET /api/auth/oneid/callback
     * OneID dan qaytish: code → token → user → JWT → frontend redirect
     */
    public function callback(Request $req)
    {
        // State tekshirish
        if (!$this->oneId->validateState($req->query('state'))) {
            return $this->fail("Noto'g'ri state parametr", 403);
        }

        $code = $req->query('code');
        if (!$code) return $this->fail('Authorization code topilmadi', 400);

        // OneID dan token va userinfo olish
        try {
            $result = $this->oneId->authenticate($code);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 500);
        }

        // User yaratish yoki yangilash
        $reg = $this->registrar->registerOrUpdate($result['user_info']);
        $user = $reg['user'];

        // JWT yaratish
        $jwt = $this->jwt->issue(['sub' => (string) $user->id]);
        Cookie::queue($this->jwt->makeCookie($jwt));

        // Frontend ga redirect
        $front = rtrim(config('oneid.frontend_url', 'http://localhost:3000'), '/');
        $routes = config('oneid.routes');

        if ($user->isPending()) {
            return redirect()->away($front . ($routes['pending_path'] ?? '/pending') . '?token=' . urlencode($jwt));
        }
        if ($user->isBlocked()) {
            return redirect()->away($front . ($routes['blocked_path'] ?? '/blocked'));
        }

        return redirect()->away($front . ($routes['callback_path'] ?? '/callback') . '?token=' . urlencode($jwt));
    }

    /**
     * GET /api/me
     * Joriy foydalanuvchi ma'lumotlari + roles + permissions
     */
    public function me(Request $req): JsonResponse
    {
        $user = $req->user();
        if (!$user) return $this->fail("Avtorizatsiyadan o'tilmagan", 401);

        $user->load('roles.permissions');

        return response()->json([
            'status' => 200, 'success' => true,
            'data' => [
                'user'        => $user,
                'roles'       => $user->roles->pluck('slug'),
                'permissions' => $user->allPermissions()->pluck('slug'),
            ],
        ]);
    }

    /**
     * GET /api/auth/status
     * Foydalanuvchi holati (pending userlar uchun ham, jwt.auth middleware siz)
     */
    public function status(Request $req): JsonResponse
    {
        $token = $this->jwt->extractToken($req);
        if (!$token) return $this->fail('Token topilmadi', 401);

        try {
            $claims = $this->jwt->parse($token);
        } catch (\Throwable) {
            return $this->fail('Token yaroqsiz', 401);
        }

        $modelClass = config('oneid.user_model');
        $user = $modelClass::find($claims['sub'] ?? null);
        if (!$user) return $this->fail('Foydalanuvchi topilmadi', 401);

        return response()->json([
            'status' => 200, 'success' => true,
            'data' => [
                'user_status' => $user->status->value,
                'has_roles'   => $user->roles()->exists(),
                'full_name'   => $user->full_name,
            ],
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $req): JsonResponse
    {
        $token = $req->cookie($this->jwt->getCookieName());
        auth()->logout();

        if ($token) {
            try { $this->oneId->logoutRemote($token); } catch (\Throwable) {}
        }

        Cookie::queue($this->jwt->forgetCookie());

        return response()->json([
            'status' => 200, 'success' => true,
            'data' => ['message' => 'Tizimdan chiqdingiz'],
        ])->withCookie(cookie()->forget($this->jwt->getCookieName()));
    }

    private function fail(string $msg, int $code = 400): JsonResponse
    {
        return response()->json(['status' => $code, 'success' => false, 'data' => ['message' => $msg]], $code);
    }
}
