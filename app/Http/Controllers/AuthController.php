<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse {
        $this->validate(
            $request,
            [
                'username' => ['required', 'unique:users,username'],
                'email' => ['required', 'email', 'unique:users,email'],
                'competition_type' => ['required', Rule::in(['CRYSTAL','ISOTERM'])],
            ],
            [
                'competition_type.in' => 'competition type must be either CRYSTAL or ISOTERM'
            ]
        );

        $user = User::query()->create(
            [
                'email' => $request->input('email'),
                'username' => $request->input('username'),
                'competition_type' => $request->input('competition_type'),
                'password' => $request->input('password'),
            ]
        );

        return response()->json([
            'user_id' => $user->id,
            'message' => 'user created',
        ]);
    }

    public function login(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'username' => 'email',
        ]);

        if ($validator->fails()) {
            $credentials = [
                'username' => $request->input('username'),
                'password' => $request->input('password')
            ];
        } else {
            $credentials = [
                'email' => $request->input('username'),
                'password' => $request->input('password')
            ];
        }

        if (! auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'wrong credentials',
            ], 401);
        }

        $user = auth()->user();

        $tokenResult = $user->createToken(request('device', 'Unknown Device'));
        $token = $tokenResult->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'competition_type' => $user->competition_type,
            'expires_at' => now()->addYear(),
        ]);
    }
}
