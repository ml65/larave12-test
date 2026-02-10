<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Авторизация и получение токена
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $user = Auth::user();

        // Проверяем роль менеджера
        if (!$user->hasRole('manager')) {
            Auth::logout();

            return response()->json([
                'message' => 'Access denied. Manager role required.',
            ], 403);
        }

        // Создаем токен
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Выход и удаление токена
     */
    public function logout(): JsonResponse
    {
        $user = Auth::user();

        if ($user) {
            // Удаляем текущий токен
            $user->currentAccessToken()?->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
