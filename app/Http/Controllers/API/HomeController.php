<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    // get student by matricule and check ID card data upload status
    public function data_upload_status(Request $request){
        if(!$request->has('matric') || $request->matric == null){
            return response()->json(['message' => "Invalid input. Matricule expected but was not provided in the request"], 400);
        }

        $matric = $request->matric;

        $student = \App\Models\Students::where('matricule', $matric)->first();

        if(empty($student)){
            return response()->json(['matric' => $matric, 'student_exists' => 0, 'upload_status' => 0, 'url' => url('/')]);
            }
            
        if(empty($student->photo) || empty($student->img_path)){
                return response()->json(['matric' => $matric, 'student_exists' => 1, 'upload_status' => 0, 'url' => url('/')]);
        }
        
        return response()->json(['matric' => $matric, 'student_exists' => 1, 'upload_status' => 1, 'url' => url('/')]);
    }
}
