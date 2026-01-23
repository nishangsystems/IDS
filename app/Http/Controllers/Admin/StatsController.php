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
        $current_year = \App\Helpers\Helpers::instance()->getCurrentAccademicYear();


        $students = Students::whereNotNull('img_path')->where('img_path', '!=', '')->where('admission_batch_id', $current_year)->groupBy('matricule')->distinct()->get();
        $uploaded = Students::whereNotNull('img_path')->where('img_path', '!=', '')->where('admission_batch_id', $current_year)->groupBy('matricule')->distinct()->pluck('matricule')->toArray();
        // dd($response->where('program_id', 11));
        // dd([
        //     'uploaded'=>$students->where('program', 'Direct B.Sc Nursing')->count(),
        //     'students'=>$response->where('program', 'Direct B.Sc Nursing')->count(),
        //     'missing'=>$response->where('program', 'Direct B.Sc Nursing')->count() - $students->where('program', 'Direct B.Sc Nursing')->count(),
        //     'missing_students'=>$response->where('program', 'Direct B.Sc Nursing')->whereNotIn('matric', $uploaded)->values()->unique('matric')->values(),
        //     'strange_students'=>$students->where('program', 'Direct B.Sc Nursing')->whereNotIn('matricule', $response->pluck('matric')->toArray())->values(),
        // ]);

        $pending = $response->filter(function($record)use($uploaded){
            return !in_array($record['matric'], $uploaded);
        })->sortBy('program');

        $pending_x = $response->groupBy('program')->map(function($group)use($students){
            $missing_count = $group->count() - $students->where('program', $group->first()['program'])->count();
            return [
                'program'=>$group->first()['program'],
                'missing_count'=>$missing_count,
                'total_count'=>$group->count(),
            ];
        });
        // dd($pending_x->sortByDesc('missing_count'));
        $data['title'] = "Data Upload Statistics";
        $data['students'] = $pending_x;
        $data['conflicts'] = $students->whereNotIn('matricule', $response->pluck('matric')->toArray())->count();
        return view('admin.stats.pending_uploads', $data);
    }
}