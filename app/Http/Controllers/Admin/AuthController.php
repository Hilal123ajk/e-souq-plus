<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Requests\Admin\VerifyAdminOtpRequest;
use App\Jobs\SendAdminLoginOtp;
use App\Models\User;
use App\Services\AdminOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly AdminOtpService $otp,
    ) {}

    public function showLogin(): View
    {
        return view('admin.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::validate($credentials)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid email or password.']);
        }

        /** @var User|null $user */
        $user = User::query()->where('email', $credentials['email'])->first();

        if ($user === null || ! $user->isAdminUser()) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'You do not have access to the admin panel.']);
        }

        if ($this->otp->requiresVerification($user)) {
            $this->otp->markSessionPending($user->id, $request->boolean('remember'));

            $otp = $this->otp->issueOtp($user);

            try {
                SendAdminLoginOtp::dispatch($user->id, $otp);
            } catch (\Throwable $e) {
                report($e);

                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Unable to queue verification email. Ensure the queue worker is running and try again.']);
            }

            return redirect()
                ->route('admin.login.verify')
                ->with('status', 'Enter the 5-digit code being sent to '.$this->otp->maskEmail($user->email).'. It may take a few seconds to arrive.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function showVerifyOtp(): View|RedirectResponse
    {
        $userId = $this->otp->pendingUserId();

        if ($userId === null) {
            return redirect()->route('admin.login');
        }

        $user = User::query()->find($userId);

        if ($user === null || ! $user->isAdminUser()) {
            $this->otp->clearPendingSession();

            return redirect()->route('admin.login');
        }

        return view('admin.verify-otp', [
            'maskedEmail' => $this->otp->maskEmail($user->email),
        ]);
    }

    public function verifyOtp(VerifyAdminOtpRequest $request): RedirectResponse
    {
        $userId = $this->otp->pendingUserId();

        if ($userId === null) {
            return redirect()->route('admin.login');
        }

        $user = User::query()->find($userId);

        if ($user === null || ! $user->isAdminUser()) {
            $this->otp->clearPendingSession();

            return redirect()->route('admin.login');
        }

        if (! $this->otp->verifyOtp($user, $request->string('otp')->toString())) {
            return back()->withErrors(['otp' => 'Invalid or expired verification code.']);
        }

        $remember = $this->otp->pendingRemember();
        $this->otp->clearPendingSession();

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function resendOtp(): RedirectResponse
    {
        $userId = $this->otp->pendingUserId();

        if ($userId === null) {
            return redirect()->route('admin.login');
        }

        $user = User::query()->find($userId);

        if ($user === null || ! $user->isAdminUser()) {
            $this->otp->clearPendingSession();

            return redirect()->route('admin.login');
        }

        $otp = $this->otp->issueOtp($user);

        try {
            SendAdminLoginOtp::dispatch($user->id, $otp);
        } catch (\Throwable) {
            return back()->withErrors(['otp' => 'Unable to resend verification code. Try again shortly.']);
        }

        return back()->with('status', 'A new verification code is being sent to '.$this->otp->maskEmail($user->email).'.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
