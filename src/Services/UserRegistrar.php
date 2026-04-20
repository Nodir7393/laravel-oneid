<?php

namespace Nodir\OneId\Services;

use Illuminate\Support\Facades\DB;

class UserRegistrar
{
    /**
     * OneID dan kelgan ma'lumotlar asosida user yaratish yoki yangilash
     *
     * @return array{user: \Illuminate\Database\Eloquent\Model, is_new: bool}
     */
    public function registerOrUpdate(array $info): array
    {
        $pinfl    = $info['pin']        ?? null;
        $passport = $info['pport_no']   ?? null;
        $first    = $info['first_name'] ?? null;
        $middle   = $info['mid_name']   ?? null;
        $last     = $info['sur_name']   ?? null;
        $full     = $info['full_name']  ?? null;
        $login    = $info['user_id']    ?? null;

        $modelClass = config('oneid.user_model');
        $defaultStatus = config('oneid.default_user_status', 'pending');

        return DB::transaction(function () use (
            $modelClass, $defaultStatus,
            $pinfl, $passport, $first, $middle, $last, $full, $login
        ) {
            $q = $modelClass::query();
            if ($pinfl)    $q->orWhere('pinfl', $pinfl);
            if ($passport) $q->orWhere('passport', $passport);
            if ($login)    $q->orWhere('egov_login', $login);

            $user  = $q->first();
            $isNew = !$user;

            if ($isNew) {
                $user = new $modelClass();
                $user->status   = $defaultStatus;
                $user->password = bcrypt(str()->random(40));
            }

            if ($pinfl)    $user->pinfl      = $pinfl;
            if ($passport) $user->passport   = $passport;
            if ($login)    $user->egov_login = $login;
            $user->first_name  = $first  ?? $user->first_name;
            $user->middle_name = $middle ?? $user->middle_name;
            $user->last_name   = $last   ?? $user->last_name;
            $user->full_name   = $full   ?? trim(implode(' ', array_filter([$last, $first, $middle])));
            $user->last_login_at = now();
            $user->save();

            return ['user' => $user, 'is_new' => $isNew];
        });
    }
}
