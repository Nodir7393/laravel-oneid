<?php

namespace Nodir\OneId\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OneIdService
{
    private string $clientId;
    private string $clientSecret;
    private string $authorizeUrl;
    private string $tokenUrl;
    private string $userInfoUrl;
    private string $logoutUrl;
    private string $redirectUri;
    private string $scope;

    public function __construct()
    {
        $c = fn(string $k, string $d = '') => config("oneid.egov.$k", $d);

        $this->clientId     = $c('client_id');
        $this->clientSecret = $c('client_secret');
        $this->authorizeUrl = $c('authorize_url');
        $this->tokenUrl     = $c('token_url');
        $this->userInfoUrl  = $c('userinfo_url');
        $this->logoutUrl    = $c('logout_url');
        $this->redirectUri  = $c('redirect_uri');
        $this->scope        = $c('scope', 'myportal');
    }

    /**
     * OneID authorize URL yaratish
     */
    public function buildAuthorizeUrl(?string $state = null): string
    {
        $state = $state ?: Str::random(40);
        session(['oneid_state' => $state]);

        return $this->authorizeUrl . '?' . http_build_query([
            'response_type' => 'one_code',
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'scope'         => $this->scope,
            'state'         => $state,
        ]);
    }

    /**
     * Authorization code ni access_token ga almashtirish
     */
    public function exchangeCode(string $code): array
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type'    => 'one_authorization_code',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'code'          => $code,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('OneID token exchange failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Access token orqali foydalanuvchi ma'lumotlarini olish
     */
    public function identify(string $accessToken): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get($this->userInfoUrl, [
            'grant_type'    => 'one_access_token_identify',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'access_token'  => $accessToken,
            'scope'         => $this->scope,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('OneID identify failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Remote logout
     */
    public function logoutRemote(string $accessToken): void
    {
        Http::get($this->logoutUrl, [
            'grant_type'    => 'one_log_out',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'access_token'  => $accessToken,
            'scope'         => $this->scope,
        ]);
    }

    /**
     * State tekshirish
     */
    public function validateState(?string $state): bool
    {
        return $state && $state === session('oneid_state');
    }

    /**
     * To'liq flow: code → token → userinfo
     */
    public function authenticate(string $code): array
    {
        $tokens = $this->exchangeCode($code);
        $accessToken = $tokens['access_token'] ?? null;

        if (!$accessToken) {
            throw new \RuntimeException('OneID: access_token olinmadi');
        }

        $userInfo = $this->identify($accessToken);

        return [
            'tokens'    => $tokens,
            'user_info' => $userInfo,
        ];
    }
}
