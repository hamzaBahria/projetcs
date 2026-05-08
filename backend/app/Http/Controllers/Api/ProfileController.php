<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $user->avatar_url = $user->avatar ? asset('storage/' . $user->avatar) : null;
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->update($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès.',
            'data' => $user
        ]);
    }
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        $user = $request->user();
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();
        return response()->json([
            'success' => true,
            'message' => 'Avatar mis à jour.',
            'avatar_url' => asset('storage/' . $path)
        ]);
    }
}
