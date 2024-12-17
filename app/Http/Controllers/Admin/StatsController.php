<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Students;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class StatsController extends Controller
{
    //

    public function index(Request $request) {
        $school_system_domain = School::first()->system_domain_url;
        $endpoint = "{$school_system_domain}/api/program_admission_data";
        $response = Http::get($endpoint)->collect('data');
        // dd($response);
        $data['title'] = "Per Campus Per Program Statistics";
        $data['stats'] = $response->filter(function($rec){return $rec != null;})->map(function($rec){
            return Students::whereNotNull('printed_at')->where(['program'=>$rec['name'], 'campus'=>$rec['campus']])->select(['id', 'campus', 'program', DB::raw("COUNT(*) as size")])->groupBy(['campus', 'program'])->get()
                ->each(function($elm)use($rec){$elm->admitted_students = $rec['admitted_students'];});
        });
        // $data['stats'] = Students::whereNotNull('printed_at')
        //     ->select([
        //         'id', 'campus', 'program', DB::raw("COUNT(*) as size")
        //     ])
        //     ->groupBy(['campus', 'program'])->orderBy('program')->get();
        dd($data);
        return view('admin.stats.index', $data);
    }
}
