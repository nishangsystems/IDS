<?php

use App\Http\Controllers\ApiContoller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('upload_statistics', function(Request $request){
    $year_id = $request->year_id;
    if($year_id == null){
        $year_id = \App\Helpers\Helpers::instance()->getCurrentAccademicYear();
    }

    $students = \App\Models\Students::select(['program', 'level', 'campus', DB::raw("SUM(CASE WHEN img_path IS NULL THEN 0 ELSE 1 END) as uploaded"), DB::raw("SUM(CASE WHEN img_path IS NULL THEN 1 ELSE 0 END) as pending")])->groupBy(['program', 'level', 'campus'])->get();
    return response()->json(['data' => $students]);
});



Route::get('data_upload_status', [\App\Http\Controllers\API\HomeController::class, 'data_upload_status']);