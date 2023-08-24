<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dosage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DosageController extends Controller
{
    public function index(Request $request){
        $id = $request->user()->id;
        $dosages = Dosage::where("user_id", $id)->get();

        return response()->json([
            "data" =>$dosages
        ],200);
    }

    public function store(Request $request) : JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                "user_id"=> "required",
                "pill_name"=> "required",
                "start_date"=> "required",
                "end_date"=> "required",
                "dosage_frequency"=> "required",
                "dosage_times"=> "required",

            ],
            [
                "user_id.required" => "User id is required",
                "pill_name.required"=> "Dosage Name is required",
                "start_date.required"=> "Start date is required",
                "end_date.required"=> "End date is required",
                "dosage_frequency.required" => "Dosage Frequency is required",
                "dosage_times.required" => "Dosage Times is required",
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                "ok" => false,
                "msg" => "Adding Dosage Failed. " . join(" ,", $validator->errors()->all()),
                "error" => [
                    "msg" => "Request validation failed. " . join(" ,", $validator->errors()->all()),
                    "fix" => "Please fix all validation errors",
                ]
            ]);
        }

        try{

            Dosage::create([
                "user_id" => $request->user_id,
                "pill_name" => $request->pill_name,
                "start_date" => $request->start_date,
                "end_date" => $request->end_date,
                "dosage_times"=> json_encode($request->dosage_times),
                "dosage_frequency" => $request->dosage_frequency,
//                "dosage_interval" => $request->dosage_interval,
            ]);

            return response()->json([
                "ok" => true,
                "msg" => "Dosage saved successfully"
            ],200);
        }
        catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                "ok" => false,
                "msg" => "An internal error occurred",
            ],500);
        }
    }
}
