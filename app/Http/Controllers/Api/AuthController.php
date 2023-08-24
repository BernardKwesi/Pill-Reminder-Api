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

class AuthController extends Controller
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


    public function register(Request $request): JsonResponse
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
