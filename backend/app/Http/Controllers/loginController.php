<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class loginController extends Controller
{
    //
    public function submit(Request $request)
    {
        // validation the phone number
        $request->validate([
            'phone' => 'required|numeric|digits:11|unique:users,phone',
            // 'password' => 'required|min:6|max:20'
        ]);

        // find or create a user model

        $user = User::firstOrCreate([
            'phone' => $request->phone
        ]);
        if (!user) {
            return response()->json([
                'message' => 'Something went wrong'
            ], 500);

        }

        // send the user a verification code
        
        $user->notify(new loginNeedVerification());

        //return back a response

        return response()->json([
            'message' => 'A verification code has been sent to your phone number'
        ], 200);
    }
    public function verify(Request $request)
    { 
        //validate the request

        $request->validate([
            'phone' => 'required|numeric|digits:11',
            'login_code' => 'required|numeric|between:1000,9999'
        ]);

        // find the user

        $user = User::where('phone', $request->phone)->where('login_code', $request->login_code)->first();
        
        //is the code provided the some one saved?


        //if so, returen back an auth token

        if ($user) {
            return $user->createToken($request->login_code)->plainTextToken;
        }

        //if not,return back a massege


    }
}
