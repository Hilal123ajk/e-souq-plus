<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\AdminLoginOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminOtpService
{
    private const CACHE_PREFIX = 'admin_login_otp:';

    public function requiresVerification(User $user): bool
    {
        if ($user->admin_otp_verified_at === null) {
            return true;
        }

        $validDays = (int) config('esouq.admin_otp_valid_days', 7);

        return $user->admin_otp_verified_at->lt(now()->subDays($validDays));
    }

    public function issueOtp(User $user): string
    {
        $otp = (string) random_int(10000, 99999);
        $ttlMinutes = (int) config('esouq.admin_otp_expiry_minutes', 15);

        Cache::put(
            self::CACHE_PREFIX.$user->id,
            Hash::make($otp),
            now()->addMinutes($ttlMinutes),
        );

        return $otp;
    }

    public function sendOtp(User $user): void
    {
        $otp = $this->issueOtp($user);
        $ttlMinutes = (int) config('esouq.admin_otp_expiry_minutes', 15);

        Mail::to($user->email)->send(new AdminLoginOtpMail(
            userName: $user->name,
            otp: $otp,
            expiresMinutes: $ttlMinutes,
        ));
    }

    public function verifyOtp(User $user, string $otp): bool
    {
        $cacheKey = self::CACHE_PREFIX.$user->id;
        $hash = Cache::get($cacheKey);

        if (! is_string($hash) || ! Hash::check(trim($otp), $hash)) {
            return false;
        }

        Cache::forget($cacheKey);

        $user->forceFill(['admin_otp_verified_at' => now()])->save();

        return true;
    }

    public function markSessionPending(int $userId, bool $remember): void
    {
        session([
            'admin_login_pending_user_id' => $userId,
            'admin_login_remember' => $remember,
        ]);
    }

    public function clearPendingSession(): void
    {
        session()->forget(['admin_login_pending_user_id', 'admin_login_remember']);
    }

    public function pendingUserId(): ?int
    {
        $id = session('admin_login_pending_user_id');

        return is_numeric($id) ? (int) $id : null;
    }

    public function pendingRemember(): bool
    {
        return (bool) session('admin_login_remember', false);
    }

    public function maskEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $visible = Str::substr($local, 0, min(2, strlen($local)));

        return $visible.'***@'.$domain;
    }
}
