<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email déjà vérifié.',
            ]);
        }

        if (!$user->verification_code || !$user->verification_code_expires_at) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun code de vérification trouvé. Veuillez vous inscrire à nouveau.',
            ], 400);
        }

        if ($user->verification_code_expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Le code de vérification a expiré.',
            ], 400);
        }

        if ($user->verification_code !== $validated['code']) {
            return response()->json([
                'success' => false,
                'message' => 'Code de vérification invalide.',
            ], 400);
        }

        $user->markEmailAsVerified();
        $user->update([
            'verification_code' => null,
            'verification_code_expires_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email vérifié avec succès.',
            'email' => $user->email,
        ]);
    }
}
