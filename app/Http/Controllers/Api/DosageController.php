<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DosageResource;
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
            "ok" => true,
            "msg" => "Data fetched successfully",
            "data" =>DosageResource::collection($dosages)
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
                "dosage_frequency"=> "required",
                "dosage_times"=> "required",
                "medication_quantity" => "required",
                "quantity_per_dose" => "required"
            ],
            [
                "user_id.required" => "User id is required",
                "pill_name.required"=> "Dosage Name is required",
                "start_date.required"=> "Start date is required",
                "quantity_per_dose.required" => "Quantity per dose is required",
                "medication_quantity.required" => "Medication quantity is required",
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
                "medication_quantity" => $request->medication_quantity,
                "updated_quantity" => $request->medication_quantity,
                "quantity_per_dose" => $request->quantity_per_dose,
                "dosage_times"=> json_encode(json_decode($request->dosage_times,true)),
                "dosage_frequency" => $request->dosage_frequency,

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
    
    public function update(Request $request) : JsonResponse
    {
           $validator = Validator::make(
            $request->all(),
            [
            
                "dosage_id"=> "required|exists:pill_reminders,id",
                "pill_name"=> "required",
                "start_date"=> "required",
                "dosage_frequency"=> "required",
                "dosage_times"=> "required",
                "medication_quantity" => "required",
                "quantity_per_dose" => "required"
            ],
            [
                "user_id.required" => "User id is required",
                "pill_name.required"=> "Dosage Name is required",
                "start_date.required"=> "Start date is required",
                "quantity_per_dose.required" => "Quantity per dose is required",
                "medication_quantity.required" => "Medication quantity is required",
                "dosage_frequency.required" => "Dosage Frequency is required",
                "dosage_times.required" => "Dosage Times is required",
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                "ok" => false,
                "msg" => "Updating Dosage Failed. " . join(" ,", $validator->errors()->all()),
                "error" => [
                    "msg" => "Request validation failed. " . join(" ,", $validator->errors()->all()),
                    "fix" => "Please fix all validation errors",
                ]
            ]);
        }
        
        try{
            $dosage = Dosage::where("id", $request->dosage_id)->first();
        
            $dosage->update([
                    "pill_name" => $request->pill_name,
                    "start_date" => $request->start_date,
                    "medication_quantity" => $request->medication_quantity,
                    "quantity_per_dose" => $request->quantity_per_dose,
                    "dosage_times"=> json_encode($request->dosage_times),
                    "dosage_frequency" => $request->dosage_frequency,
            ]);
            
            return response()->json([
                    "ok" => true,
                    "msg" => "Dosage updated successfully"
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
    
    public function markDosage(Request $request) : JsonResponse
    {
           $validator = Validator::make(
            $request->all(),
            [
               
                "dosage_id"=> "required|exists:pill_reminders,id",
               
            ],
            [
                "user_id.required" => "User id is required",
                "dosage_id.required" => "Dosage id is required"
            ]
        );
        
          if ($validator->fails()) {
            return response()->json([
                "ok" => false,
                "msg" => "Marking Dosage Failed. " . join(" ,", $validator->errors()->all()),
                "error" => [
                    "msg" => "Request validation failed. " . join(" ,", $validator->errors()->all()),
                    "fix" => "Please fix all validation errors",
                ]
            ]);
        }
        
        try{
            
            $dosage = Dosage::where("id", $request->dosage_id)->first();
            if(empty($dosage)){
                return response()->json([
                    "ok" => false,
                    "msg" => "Dosage not found",
                    "data" => []
                ]);
            }
                     
            $dosage->update([
                "updated_quantity" => $dosage->medication_quantity - $dosage->quantity_per_dose
            
            ]);         
            
            //Todo : notify firebase when the medication quantity is less than the original 20%
            
            return response()->json([
                "ok" => true,
                "msg" => "Dosage marked successfully",
                "data" => []
            ]);
        
        }
        catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                "ok" => false,
                "msg" => "An internal error occurred",
            ],500);
        }
    }
    
    
    public function destroy(Request $request) :JsonResponse 
    {
        
        $dosage = Dosage::where("id", $request->dosage_id)->first();
        
        if(empty($dosage)){
            return response()->json([
                "ok" => false,
                "msg" => "Dosage with id '$request->dosage_id' was not found",
                "data" => []
            ]);
        }
        
        $dosage->delete();
        
        return response()->json([
            "ok" => true,
            "msg" => "Dosage deleted successfully"
        ]);
        
    }
}
