<?php

namespace Nodir\OneId\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();
        if (!$user)                return $this->deny(401);
        if ($user->hasRole('admin')) return $next($request);

        foreach ($permissions as $p) {
            if ($user->hasPermission($p)) return $next($request);
        }
        return $this->deny(403);
    }

    private function deny(int $code): Response
    {
        $msg = $code === 401 ? 'Avtorizatsiya talab qilinadi' : 'Ruxsat berilmagan';
        return response()->json(['status' => $code, 'success' => false, 'data' => ['message' => $msg]], $code);
    }
}
