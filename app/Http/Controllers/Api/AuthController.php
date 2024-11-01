<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
class AuthController extends Controller
{
    public function register(Request $request){
    $validator = Validator::make($request->all(), [
        'name' => 'required|string',
        'email' => 'required|string|email|unique:users',
        'password' => 'required|string|min:6',
        'c_password' => 'required|same:password'
    ], [
        'c_password.required' => 'The confirm password field is required.',
        'c_password.same' => 'The confirm password must match the password'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error' => true,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
    }

    $user = new User([
        'name'  => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
    ]);

    if($user->save()){
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;
        $user->assignRole('team_admin');
        return response()->json([
            'message' => 'Successfully created user!',
            'accessToken'=> $token,
        ], 201);
    } else {
        return response()->json(['error' => true, 'message' => 'Failed to create user.'], 500);
    }
}
public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|string|email',
        'password' => 'required|string'
    ], [
        'email.required' => 'The email field is required.',
        'email.email' => 'The email must be a valid email address.',
        'password.required' => 'The password field is required.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error' => true,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
    }

    $credentials = request(['email', 'password']);
    if (!Auth::attempt($credentials)) {
        return response()->json([
            'error' => true,
            'message' => 'Unauthorized',
            'errors' => ['email' => ['The email or password entered is incorrect.']]
        ], 401);
    }

    $user = $request->user();
    $tokenResult = $user->createToken('Personal Access Token');
    $token = $tokenResult->plainTextToken;

    return response()->json([
        'accessToken' => $token,
        'token_type' => 'Bearer',
    ]);
}



    public function user(Request $request)
    {
        $user = $request->user();

        if ($user) {
            return response()->json($user);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing token'
            ], 401);
        }
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);

    }
}
