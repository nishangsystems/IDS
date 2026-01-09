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
        $data['stats'] = $response->map(function($rec){
            if($rec == null){return;}
            $rec['program'] = $rec['name'];
            $rec['size'] = 0;
            // dd($rec);
            $row = Students::whereNotNull('printed_at')->where(['program'=>$rec['name'], 'campus'=>$rec['campus']])->select(['id', 'campus', 'program', DB::raw("COUNT(*) as size")])->groupBy(['campus', 'program'])->first();
            if($row != null){
                $rec['size'] = $row->size;
            }
            return $rec;
        })->filter(function($rec){return $rec != null;});
        
        return view('admin.stats.index', $data);
    }

    public function pending_upload(Request $request){
        $school_system_domain = School::first()->system_domain_url;
        $endpoint = "{$school_system_domain}/api/admitted_students";
        $response = Http::get($endpoint)->collect('data')->unique('matric');


        $students = Students::whereNotNull('photo')->groupBy('matricule')->distinct()->get();
        $uploaded = Students::whereNotNull('photo')->groupBy('matricule')->distinct()->pluck('matricule')->toArray();
        // dd($response->where('program_id', 11));
        dd([
            'uploaded'=>$students->where('program', 'Direct B.Sc Nursing')->count(),
            'students'=>$response->where('program', 'Direct B.Sc Nursing')->count(),
            'missing'=>$response->where('program', 'Direct B.Sc Nursing')->count() - $students->where('program', 'Direct B.Sc Nursing')->count(),
            'missing_students'=>$response->where('program', 'Direct B.Sc Nursing')->whereNotIn('matric', $uploaded)->values()->unique('matric')->values(),
            'strange_students'=>$students->where('program', 'Direct B.Sc Nursing')->whereNotIn('matricule', $response->pluck('matric')->toArray())->values(),
        ]);

        $pending = $response->reject(function($record)use($uploaded){
            return in_array($record['matric'], $uploaded);
        })->sortBy('program');
        $data['title'] = "Data Upload Statistics";
        $data['students'] = $pending;
        return view('admin.stats.pending_uploads', $data);
    }
}