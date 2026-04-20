<?php

namespace Nodir\OneId\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private string $secret;
    private int    $ttl;
    private string $algo;
    private string $cookieName;
    private ?string $cookieDomain;

    public function __construct()
    {
        $this->secret       = config('oneid.jwt.secret');
        $this->ttl          = config('oneid.jwt.ttl', 1440);
        $this->algo         = config('oneid.jwt.algo', 'HS256');
        $this->cookieName   = config('oneid.jwt.cookie_name', 'access_token');
        $this->cookieDomain = config('oneid.jwt.cookie_domain');
    }

    /**
     * JWT token yaratish
     */
    public function issue(array $claims = []): string
    {
        $now = time();
        $payload = array_merge([
            'iss' => config('app.url'),
            'iat' => $now,
            'exp' => $now + ($this->ttl * 60),
            'jti' => bin2hex(random_bytes(16)),
        ], $claims);

        return JWT::encode($payload, $this->secret, $this->algo);
    }

    /**
     * JWT tokenni tekshirish va claimslarni qaytarish
     */
    public function parse(string $token): array
    {
        return (array) JWT::decode($token, new Key($this->secret, $this->algo));
    }

    /**
     * Request dan token olish (Bearer yoki Cookie)
     */
    public function extractToken($request): ?string
    {
        return $request->bearerToken() ?: $request->cookie($this->cookieName);
    }

    /**
     * Cookie yaratish
     */
    public function makeCookie(string $jwt): \Symfony\Component\HttpFoundation\Cookie
    {
        return cookie(
            $this->cookieName,
            $jwt,
            $this->ttl,
            '/',
            $this->cookieDomain,
            true,  // secure
            true,  // httpOnly
            false, // raw
            'None' // sameSite
        );
    }

    /**
     * Cookie ni o'chirish
     */
    public function forgetCookie(): \Symfony\Component\HttpFoundation\Cookie
    {
        return cookie(
            $this->cookieName,
            '',
            -60,
            '/',
            $this->cookieDomain,
            true,
            true,
            false,
            'None'
        );
    }

    public function getCookieName(): string
    {
        return $this->cookieName;
    }
}
