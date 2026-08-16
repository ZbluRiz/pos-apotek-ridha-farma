<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\Auth\LoginUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(AuthLoginRequest $request, LoginUser $loginUser): JsonResponse
    {
        $user = $loginUser->execute($request->validated());
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

    public function me(): JsonResponse
    {
        return response()->json([
            'data' => new UserResource(request()->user()),
        ]);
    }

    public function updateProfile(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $payload = $request->safe()->only(['name', 'email']);

        if ($request->filled('password')) {
            $payload['password'] = $request->validated('password');
        }

        $user->update($payload);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data' => new UserResource($user->refresh()),
        ]);
    }

    public function logout(): JsonResponse
    {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}
