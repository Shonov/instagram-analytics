<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterFormRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterFormRequest $request)
    {
        $user = new User();
        $user->email = $request->email;
        $user->name = $request->name;
        $user->password = bcrypt($request->password);
        $user->save();

        return response([
            'status' => 'success',
            'data' => $user,
        ], 200);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $credentials['email'] = strtolower($credentials['email']);

        $validator = Validator::make([$credentials['email']],['exists:users,email'], ['error.login_not_exist']);

        if ($validator->fails()) {
            return response([
                'status' => 'error',
                'error' => 'invalid.login',
                'msg' => $validator->errors()], 400);
        }
        if (!$token = $this->guard()->attempt($credentials)) {
            return response([
                'status' => 'error',
                'error' => 'invalid.credentials',
                'msg' => 'error.invalid_credentials',
            ], 400);
        }

        return response([
            'status' => 'success',
        ])->header('Access-Control-Expose-Headers', 'Authorization')
            ->header('Authorization', $token);
    }

    public function user(Request $request)
    {
        return response([
            'status' => 'success',
            'data' => $this->guard()->user()
        ]);
    }

    public function logout()
    {
        \JWTAuth::parseToken()->invalidate();

        return response([
            'status' => 'success',
            'msg' => 'Logged out Successfully.'
        ], 200);
    }

    public function refresh()
    {

        try{
            $token = $this->guard()->refresh();
        } catch (TokenExpiredException $e) {
            return response()->json(['status' => 'Token is Expired']);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ])
            ->header('Access-Control-Expose-Headers', 'Authorization')
            ->header('Authorization', $token);
    }


    public function guard()
    {
        return Auth::guard();
    }
}
