<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {

            $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            //find user by email
            $user = User::where('email', $request->email)->first();
            if(!Hash::check($request->password, $user->password)){
                throw new Exception('Invalid password');
            }

            //generate token
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            //return response

            return ResponseFormatter::success([
                'accessToken' => $tokenResult,
                'type_token' => 'Bearer',
                'user' => $user
            ], 'Login Success');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function register(Request $request)
    {
        try {

            //create validation
            $request->validate([
                'name' => ['required', 'string', 'max:256'],
                'email' => ['required', 'string', 'email', 'unique:users'],
                'password' => ['required','string', new Password]
            ],[
                'name.required' => 'Nama harus diisi!',
                'email.required' => 'Email harus diisi!',
                'password.required' => 'Password harus diisi!',
            ]);

            //create user register
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            //generate token
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            //Return Response
            return ResponseFormatter::success([
                'accessToken' => $tokenResult,
                'type_token' => 'Bearer',
                'user' => $user
            ], 'Register successfully');


        } catch (Exception $e) {
            //return response error
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        // Revoke Token
        $token = $request->user()->currentAccessToken()->delete();

        // Return response
        return ResponseFormatter::success($token, 'Logout success');
    }

    public function fetch(Request $request)
    {
        //Get User
        $user = $request->user();

        //Response
        return ResponseFormatter::success($user, 'Successfully Fetched User');
    }

}
