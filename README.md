# Laravel OneID

O'zbekiston OneID (EGov SSO) OAuth integratsiyasi Laravel uchun.

## Imkoniyatlar

- OneID OAuth 2.0 to'liq oqim (authorize → callback → userinfo)
- JWT autentifikatsiya (cookie + bearer token)
- RBAC (rollar va permissionlar)
- Avtomatik user ro'yxatga olish (pending → admin tasdiqlash → active)
- Middleware: `jwt.auth`, `role:admin`, `permission:users.manage`
- `HasRbac` trait — o'z User modelingizga qo'shsa bo'ladi
- Laravel 11, 12, 13 qo'llab-quvvatlanadi

## O'rnatish

```bash
composer require nodir/laravel-oneid
```

Config faylni chiqarish:
```bash
php artisan vendor:publish --tag=oneid-config
```

Migrationlarni chiqarish:
```bash
php artisan vendor:publish --tag=oneid-migrations
php artisan migrate
```

## .env sozlash

```env
# OneID
ONEID_CLIENT_ID=your_client_id
ONEID_CLIENT_SECRET=your_client_secret
ONEID_REDIRECT_URI=https://your-api.uz/api/auth/oneid/callback
ONEID_SCOPE=myportal

# JWT
JWT_SECRET=your-secret-min-32-chars
JWT_TTL_MIN=1440
JWT_COOKIE_DOMAIN=.your-domain.uz

# Frontend
FRONT_APP_URL=https://your-frontend.uz
```

## Foydalanish

### 1. Tayyor routelar (avtomatik)

Paket o'rnatilishi bilan quyidagi routelar ishlaydi:

| Method | URL | Tavsif |
|--------|-----|--------|
| GET | `/api/auth/oneid/url` | OneID URL qaytarish (SPA uchun) |
| GET | `/api/auth/oneid/redirect` | OneID ga 302 redirect |
| GET | `/api/auth/oneid/callback` | OneID dan qaytish |
| GET | `/api/auth/status` | User holati (pending ham) |
| GET | `/api/me` | Joriy user + roles + permissions |
| POST | `/api/auth/logout` | Chiqish |

### 2. O'z User modelingiz bilan ishlatish

```php
// app/Models/User.php
use Nodir\OneId\Models\Traits\HasRbac;
use Nodir\OneId\Enums\UserStatus;

class User extends Authenticatable
{
    use HasRbac; // roles(), hasRole(), hasPermission() qo'shiladi

    protected function casts(): array
    {
        return [
            'status' => UserStatus::class,
        ];
    }
}
```

`config/oneid.php` da modelni ko'rsating:
```php
'user_model' => App\Models\User::class,
```

### 3. Middleware ishlatish

```php
// routes/api.php

// JWT tekshirish
Route::middleware('jwt.auth')->group(function () {
    Route::get('/dashboard', DashboardController::class);
});

// Rol tekshirish
Route::middleware(['jwt.auth', 'role:admin'])->group(function () {
    Route::get('/admin/users', UserController::class);
});

// Permission tekshirish
Route::middleware(['jwt.auth', 'permission:users.manage'])->group(function () {
    Route::post('/users/{user}/block', BlockUserController::class);
});
```

### 4. Controller da RBAC

```php
public function index(Request $req)
{
    $user = $req->user();

    if ($user->hasRole('admin')) {
        // admin logika
    }

    if ($user->hasPermission('reports.export')) {
        // export ruxsati bor
    }

    $allPermissions = $user->allPermissions()->pluck('slug');
}
```

### 5. Rol berish

```php
$user->assignRole('sifat_menejeri', auth()->id());
$user->removeRole('tadbirkor');
$user->activate(); // pending → active
```

## Auth oqimi

```
1. Frontend → GET /api/auth/oneid/redirect
2. Brauzer → OneID login sahifasi
3. OneID → callback: /api/auth/oneid/callback?code=xxx
4. Backend → code → token → userinfo → user yaratish (pending)
5. Backend → JWT yaratish → cookie + redirect frontend ga
6. Yangi user → /pending (admin kutish)
7. Admin rol beradi → user active bo'ladi
8. Keyingi kirish → /callback → /dashboard
```

## Konfiguratsiya

`config/oneid.php` da barcha sozlamalar mavjud:

- `default_user_status` — yangi userlar holati (`pending` yoki `active`)
- `user_model` — o'z User modelingiz
- `routes.enabled` — tayyor routelarni o'chirish (o'zingiz yozish uchun)
- `roles` — seeder uchun rollar ro'yxati

## Litsenziya

[MIT License](LICENSE) - xohlaganingizcha ishlatishingiz mumkin.

---

## 👨‍💻 Muallif

**Nodir** — Senior PHP Developer, Uzbekistan

- 🌐 GitHub: [@Nodir7393](https://github.com/YOUR_USERNAME)
- 💼 Ish: Laravel, Yii2, Next.js, PostgreSQL

---

## ⭐ Loyihaga yordam berish

Agar paket yoqdi va ishingizga yaragan bo'lsa:

- ⭐ GitHub'da yulduzcha qo'ying
- 🐛 Topilgan bug'lar haqida xabar bering
- 💡 Yangi g'oyalar bilan bo'lishing
- 📢 Do'stlaringiz bilan ulashing

---

<p align="center">
  Made with ❤️ in Uzbekistan 🇺🇿
</p>
