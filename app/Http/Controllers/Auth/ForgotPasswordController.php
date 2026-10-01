<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    /**
     * Display the form to request a password reset link.
     */
    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $user = User::where('email', strtolower(trim($request->input('email'))))->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'Kami tidak dapat menemukan pengguna dengan alamat email tersebut.',
            ])->onlyInput('email');
        }

        // Generate token and send standard notification
        $status = Password::broker()->sendResetLink(
            ['email' => $user->email]
        );

        $demoResetUrl = null;
        // If in local/demo environment or log mailer, provide direct link for convenience
        if (app()->isLocal() || config('mail.default') === 'log' || ! file_exists(storage_path('installed'))) {
            $tokenRecord = DB::table('password_reset_tokens')->where('email', $user->email)->first();
            if ($tokenRecord) {
                // Laravel hashes tokens in DB via Hash::check, but broker createToken can provide raw token
                $demoToken = Password::broker()->createToken($user);
                $demoResetUrl = route('password.reset', ['token' => $demoToken, 'email' => $user->email]);
            }
        }

        if ($status === Password::RESET_LINK_SENT || $demoResetUrl) {
            $redirect = back()->with('status', 'Link reset password telah dikirimkan ke email Anda.');

            if ($demoResetUrl) {
                $redirect->with('demo_reset_url', $demoResetUrl);
            }

            return $redirect;
        }

        return back()->withErrors(['email' => __($status)])->onlyInput('email');
    }
}
