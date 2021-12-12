<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private $request;
    private $user;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->user = new User;
    }

    public function register(): JsonResponse
    {
        $this->request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email|email',
            'password' => 'required|string|confirmed',
        ]);

        $user = new $this->user;
        $user->fill($this->request->all());
        $user->password = bcrypt($this->request->get('password'));
        $user->save();

        return response()->json([], 201);
    }

    public function login(): JsonResponse
    {
        $credentials = $this->request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = $this->user->query()
            ->where('email', $this->request->get('email'))
            ->first();

        if (auth()->attempt($credentials)) {
            $token = $user->createToken('auth_token')->plainTextToken;
            $user->access_token = $token;

            return response()->json($user);
        }

        return response()->json(['message' => 'The provided credentials do not match our records.'], 401);
    }

    public function logout(): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return response()->json([], 204);
    }

    public function me(): JsonResponse
    {
        $user = auth()->user();

        return response()->json($user);
    }
}
