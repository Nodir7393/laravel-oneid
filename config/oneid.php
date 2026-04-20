<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OneID (EGov SSO) sozlamalari
    |--------------------------------------------------------------------------
    */

    'egov' => [
        'client_id'     => env('ONEID_CLIENT_ID', env('EGOV_CLIENT_ID')),
        'client_secret' => env('ONEID_CLIENT_SECRET', env('EGOV_CLIENT_SECRET')),
        'redirect_uri'  => env('ONEID_REDIRECT_URI', env('EGOV_REDIRECT_URI')),
        'scope'         => env('ONEID_SCOPE', env('EGOV_SCOPE', 'myportal')),

        'authorize_url' => env('ONEID_AUTHORIZE_URL', env('EGOV_AUTHORIZE_URL', 'https://sso.egov.uz/sso/oauth/Authorization.do')),
        'token_url'     => env('ONEID_TOKEN_URL', env('EGOV_TOKEN_URL', 'https://sso.egov.uz/sso/oauth/Authorization.do')),
        'userinfo_url'  => env('ONEID_USERINFO_URL', env('EGOV_USERINFO_URL', 'https://sso.egov.uz/sso/oauth/Authorization.do')),
        'logout_url'    => env('ONEID_LOGOUT_URL', env('EGOV_LOGOUT_URL', 'https://sso.egov.uz/sso/oauth/Authorization.do')),
    ],

    /*
    |--------------------------------------------------------------------------
    | JWT sozlamalari
    |--------------------------------------------------------------------------
    */

    'jwt' => [
        'secret'        => env('JWT_SECRET'),
        'ttl'           => (int) env('JWT_TTL_MIN', 1440),       // daqiqa (default: 24 soat)
        'algo'          => env('JWT_ALGO', 'HS256'),
        'cookie_name'   => env('JWT_COOKIE_NAME', 'access_token'),
        'cookie_domain' => env('JWT_COOKIE_DOMAIN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend URL (callback dan keyin redirect uchun)
    |--------------------------------------------------------------------------
    */

    'frontend_url' => env('FRONT_APP_URL', env('FRONTEND_URL', 'http://localhost:3000')),

    /*
    |--------------------------------------------------------------------------
    | Yangi foydalanuvchi holati
    |--------------------------------------------------------------------------
    | 'pending' — admin tasdiqlashini kutadi (tavsiya etiladi)
    | 'active'  — darhol kiradi (test uchun)
    */

    'default_user_status' => env('ONEID_DEFAULT_STATUS', 'pending'),

    /*
    |--------------------------------------------------------------------------
    | User model (o'z modelingizni ishlatish mumkin)
    |--------------------------------------------------------------------------
    */

    'user_model' => env('ONEID_USER_MODEL', \Nodir\OneId\Models\User::class),

    /*
    |--------------------------------------------------------------------------
    | Route sozlamalari
    |--------------------------------------------------------------------------
    */

    'routes' => [
        'enabled'    => true,
        'prefix'     => 'api',
        'middleware' => ['web'],

        // Frontend sahifalar
        'callback_path' => '/callback',   // token bilan redirect
        'pending_path'  => '/pending',    // admin kutish sahifasi
        'blocked_path'  => '/blocked',    // bloklangan sahifa
    ],

    /*
    |--------------------------------------------------------------------------
    | RBAC — rollar va permissionlar
    |--------------------------------------------------------------------------
    | Seeder da ishlatiladi. O'zingiz qo'shishingiz/o'zgartirishingiz mumkin.
    */

    'roles' => [
        ['slug' => 'admin',               'name' => 'Administrator',       'description' => 'Barcha ruxsatlarga ega'],
        ['slug' => 'sifat_menejeri',       'name' => 'Sifat menejeri',     'description' => 'Ish reja, bajarilish monitoringi'],
        ['slug' => 'sanoat_boshqarmasi',   'name' => 'Sanoat boshqarmasi', 'description' => 'Nazorat va tasdiqlash'],
        ['slug' => 'inspektor',            'name' => 'Inspektor',          'description' => 'Korxona tekshiruvi'],
        ['slug' => 'tadbirkor',            'name' => 'Tadbirkor',          'description' => 'Korxona egasi'],
    ],

];
