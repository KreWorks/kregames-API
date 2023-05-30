<?php

namespace App\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use PHPOpenSourceSaver\JWTAuth\Token;
use PHPOpenSourceSaver\JWTAuth\Validators\TokenValidator;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use App\Http\Controllers\Controller;

use App\Models\User;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register', 'refresh']]);
    }

    public function register(Request $request){
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'username' => 'required|string|max:100|unique:users',
                'password' => 'required|string|min:6',
            ]);
        } catch(ValidationException $ve) {
            return response()->json([
                'status' => 400,
                'error' => $ve->errors()
            ], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);


        return response()->json([
            'status' => 200,
            'meta' => [
                'count' => 1,
                'entityType' => 'users',
            ],
            'data' => $user,
        ]);
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
        } catch(ValidationException $ve) {
            return response()->json([
                'status' => 400,
                'error' => $ve->errors()
            ], 400);
        }

        $credentials = $request->only('email', 'password');
        $token = Auth::attempt($credentials);

        if (!$token) {
            return response()->json([
                'status' => 401,
                'error' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
    
        return response()->json([
            'status' => 200,
            'meta' => [
                'count' => 1,
                'entityType' => 'users',
            ],
            'data' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }


    public function logout(Request $request)
    {
        $token = Auth::getToken();
        $requestToken = $request->bearerToken();

        if (explode(".", $token)[1] != explode(".", $requestToken)[1]){
            return response()->json([
                'status' => 401,
                'error' => ['token' => 'Invalid Token'],
            ], 401);
        }

        Auth::logout();
        return response()->json([
            'status' => 200,
            'meta' => [
                'count' => 0,
                'entityType' => 'users',
            ],
            'data' => null,
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 200,
            'meta' => [
                'count' => 1,
                'entityType' => 'users',
            ],
            'data' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

}
