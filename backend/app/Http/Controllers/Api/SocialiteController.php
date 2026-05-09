<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'authentification Google.',
            ], 400);
        }

        $user = User::where('email', $googleUser->email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
            ]);

            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->update([
                'verification_code' => $code,
                'verification_code_expires_at' => now()->addMinutes(60),
            ]);
            Mail::to($user)->send(new VerifyEmail($code));

            return redirect()->away(
                config('app.frontend_url').'/verify-email?email='.urlencode($user->email)
            );
        }

        if (!$user->google_id) {
            $user->update(['google_id' => $googleUser->id]);
        }

        if (!$user->hasVerifiedEmail()) {
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->update([
                'verification_code' => $code,
                'verification_code_expires_at' => now()->addMinutes(60),
            ]);
            Mail::to($user)->send(new VerifyEmail($code));

            return redirect()->away(
                config('app.frontend_url').'/verify-email?email='.urlencode($user->email)
            );
        }

        if (!$user->password) {
            return redirect()->away(
                config('app.frontend_url').'/set-password?email='.urlencode($user->email)
            );
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return redirect()->away(
            config('app.frontend_url').'/login?token='.$token.'&user='.urlencode(json_encode($user))
        );
    }

    public function setPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user->password) {
            return response()->json([
                'success' => false,
                'message' => 'Ce compte a déjà un mot de passe.',
            ], 400);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user,
        ]);
    }
}
