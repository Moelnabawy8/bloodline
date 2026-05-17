<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showForgot()
    {
        return view('auth.forgot');
    }

    public function sendResetLink(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', strtolower($data['email']))->first();

        if (! $user) {
            return back()->with('error', 'Email not found.');
        }

        $user->forceFill([
            'reset_token'        => Str::random(64),
            'reset_token_expire' => now()->addHour(),
        ])->save();

        $resetUrl = route('password.reset', ['token' => $user->reset_token]);

        try {
            Mail::to($user->email)->send(new PasswordResetMail($resetUrl));
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Failed to send email. Please try again.');
        }

        return back()->with('success', 'Reset link sent to your email.');
    }

    public function showReset(string $token)
    {
        $user = User::where('reset_token', $token)
            ->where('reset_token_expire', '>', now())
            ->first();

        if (! $user) {
            return redirect()->route('password.request')->with('error', 'Reset link is invalid or expired.');
        }

        return view('auth.reset', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token'    => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::where('reset_token', $data['token'])
            ->where('reset_token_expire', '>', now())
            ->first();

        if (! $user) {
            return redirect()->route('password.request')->with('error', 'Reset link is invalid or expired.');
        }

        $user->forceFill([
            'password'           => $data['password'],
            'reset_token'        => null,
            'reset_token_expire' => null,
        ])->save();

        return redirect()->route('login')->with('success', 'Password has been reset. You can now log in.');
    }
}
