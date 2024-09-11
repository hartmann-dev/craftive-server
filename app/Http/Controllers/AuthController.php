<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    const TOKEN_NAME = 'craftive-token';

    public function register(Request $request)
    {

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users',
            'password' => 'required|string',
            'password_repeat' => 'required|same:password'
        ]);


        $user = User::create([
            'name'  => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = $user->createToken(self::TOKEN_NAME)->plainTextToken;

        return response()->json([
            'accessToken' => $token,
        ])->setStatusCode(201);
    }


    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            /** @var User $user */
            $user = Auth::user();
            $token =  $user->createToken(self::TOKEN_NAME)->plainTextToken;

            return response()->json([
                'accessToken' => $token,
            ])->setStatusCode(200);
        } else {
            return response()->json()->setStatusCode(401);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }
}
