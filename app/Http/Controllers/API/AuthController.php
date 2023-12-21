<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ReturnResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ReturnResponse;

    public function login(Request $request)
    {
        $validasi = Validator::make($request->all(),[
            'email'     => 'required|string|email',
            'password'  => 'required|string'
        ]);

        if ($validasi->fails()) {
            return $this->failed(null, $validasi->errors());
        } else {
            $email      = $request->input('email');
            $password   = $request->input('password');
            $credentials = [
                'email'     => $email,
                'password'  => $password
            ]; 

            $result = Auth::attempt($credentials);

            if ($result) {
                $users = User::where('email', $email)->first();

                $data   = [
                    'users'         => $users,
                    'token'         => $users->createToken('auth_token')->plainTextToken,
                ];

                return $this->success($data, 'Berhasil Login');
            }else{
                return $this->failed($request->all(), 'Akun tidak ditemukan');
            }
        }
    }

    public function logout(Request $request){
        $user = $request->user();
        $user->tokens()->delete();
        
        return $this->success($user, 'Logout berhasil');
    }
}
