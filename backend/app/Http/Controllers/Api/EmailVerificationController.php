<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        $frontendUrl = config('app.frontend_url');

        if (!$request->hasValidSignature()) {
            return redirect()->away($frontendUrl . '/verify-email?status=error&message=' . urlencode('Lien invalide ou expiré.'));
        }

        $user = User::findOrFail($id);

        if (sha1($user->email) !== $hash) {
            return redirect()->away($frontendUrl . '/verify-email?status=error&message=' . urlencode('Lien invalide.'));
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->away($frontendUrl . '/verify-email?status=already_verified');
        }

        $user->markEmailAsVerified();

        return redirect()->away($frontendUrl . '/verify-email?status=success');
    }
}
