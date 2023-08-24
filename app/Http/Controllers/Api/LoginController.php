<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Exception;
use App\Models\User;


class LoginController extends Controller
{
    public function login(Request $request): JsonResponse
    {

        try{

            $validator = Validator::make(
                $request->all(),
                [
                    "userid"=> "required",
                    "password"=> "required",
                ],
                [
                    "userid.required" => "User id is required",
                    "password.required"=> "Password is required",
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    "ok" => false,
                    "msg" => "Logging in failed. " . join(" ,", $validator->errors()->all()),
                    "error" => [
                        "msg" => "Request validation failed. " . join(" ,", $validator->errors()->all()),
                        "fix" => "Please fix all validation errors",
                    ]
                ]);
            }


            $authenticatedUser = User::where("deleted", "0")
                ->where(function($query) use ($request){
                    return $query->where("email", $request->userid)
                        ->orWhere("phone", $request->userid);
                })
                ->first();


            // Return if no user is found
            if (empty($authenticatedUser)) {
                return response()->json([
                    "ok"=> false,
                    "msg"=>"Login failed. Wrong email / phone or password"
                ]);

            }

            // Return if password is invalid
            if (!Hash::check($request->password, $authenticatedUser->password)) {

                return response()->json([
                    "ok"=> false,
                    "msg"=>"Login failed. Wrong email / phone or password"
                ]);
            }







            $token =  $authenticatedUser->createToken('loginToken');


            $payload = [
                "fullname"=> $authenticatedUser->intern->fname,
                "phone" => $authenticatedUser->phone,
                "token" =>$token->plainTextToken,

            ];

            return response()->json([
                "ok"=> true,
                "msg"=> "Login successful",
                "data"=> $payload
            ]);

        }catch(Exception $e){

            Log::error($e->getMessage());

            return response()->json([
                "ok"=> false,
                "msg"=> "An error occurred while logging in.",
                "error"=>[
                    "msg"=> $e->getMessage(),
                ]
            ]);
        }

    }
}
