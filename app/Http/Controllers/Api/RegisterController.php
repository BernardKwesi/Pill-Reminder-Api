<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function store(Request $request): JsonResponse
    {

        $validator = Validator::make(
            $request->all(),
            [
                "fname" => ['required', 'string', 'max:255'],
                "lname" => ['required', 'string', 'max:255'],
                "email" => ['required',  'email', 'max:255', 'unique:user,email'],
                "phone" => ['required','unique:user,phone','max:10', "min:10"],
                "password"=> ['required']
            ],
            [
                "fullname.required" => "Name is required",
                "email.required" => "Email is required",
                "email.unique"=> "Email Address already exists",
                "phone.required" => "Phone number is required",
                "phone.unique"=> "Phone number already exists",

            ]
        );

        if ($validator->fails()) {
            return response()->json([
                "ok" => false,
                "msg" => "Account creation failed. " . join(" ,", $validator->errors()->all()),
                "error" => [
                    "msg" => "Request validation failed. " . join(" ,", $validator->errors()->all()),
                    "fix" => "Please fix all validation errors",
                ]
            ]);
        }




        try {
            DB::beginTransaction();

            $user = User::create([
                "email"=> $request->email,
                "phone" => $request->phone,
                "password" =>Hash::make($request->password),
                "createdate" =>  date("Y-m-d"),

            ]);


            //generate user login token
            $token =  $user->createToken('loginToken');

            $payload = [
                "fullname"=> $request->fname,
                "amount" => (float)optional($user->intern)->qualification->amount ,
                "user_id"=> $user->id,
                "email"=> $user->email,
                "phone" => $user->phone,
                "token" =>$token->plainTextToken,


            ];

            DB::commit();


            return response()->json([
                "ok" => true,
                "msg" => "Account created successfully",
                "data"=>$payload
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error($e->getMessage());

            return response()->json([
                "ok" => false,
                "msg" =>    "An error occured while adding record, please contact admin",
                "error" => [
                    "msg" => $e->getMessage(),
                    "fix" => "Please complete all required fields",
                ]
            ]);
        }

    }
}
