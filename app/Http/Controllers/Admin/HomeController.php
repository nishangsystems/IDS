<?php


namespace App\Http\Controllers\Admin;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentFee;
use App\Models\Background;
use App\Models\Batch;
use App\Models\CampusSemesterConfig;
use App\Models\Config;
use App\Models\File;
use App\Models\PlatformCharge;
use App\Models\Resit;
use App\Models\School;
use App\Models\SchoolUnits;
use App\Models\Semester;
use App\Models\Students;
use App\Models\StudentSubject;
use App\Models\Subjects;
use App\Models\User;
use App\Models\Wage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config as FacadesConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use MongoDB\Driver\Session;
use Barryvdh\DomPDF\Facade\Pdf;

use Throwable;
use ZipArchive;
use function PHPUnit\Framework\returnSelf;

class HomeController  extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function set_letter_head()
    {
        # code...
        $data['title'] = __('text.upload_letter_head');
        return view('admin.setting.set-letter-head', $data);
    }

    public function save_letter_head(Request $request)
    {

        # code...
        $check = Validator::make($request->all(), ['file'=>'required|file|mimes:png,jpg,jpeg,gif,tif']);
        if ($check->fails()) {
            # code...
            return back()->with('error', $check->errors()->first());
        }
        
        $file = $request->file('file');
        // return $file->getClientOriginalName();
        if(!($file == null)){
            $ext = $file->getClientOriginalExtension();
            $filename = '_'.random_int(100000, 999999).'_'.time().'.'.$ext;
            $path = 'assets/images/avatars';
            if(!file_exists(url($path))){mkdir(url($path));}
            // $file->move(url($path), $filename);
            $file->move(public_path($path), $filename);
            if(File::where(['name'=>'letter-head'])->count() == 0){
                File::create(['name'=>'letter-head', 'path'=>$filename]);
            }else {
                File::where(['name'=>'letter-head'])->update(['path'=>$filename]);
            }
            return back()->with('success', __('text.word_done'));
        }
        return back()->with('error', __('text.error_reading_file'));
    }

    public function setayear()
    {
        $data['title'] = __('text.set_current_accademic_year');
        return view('admin.setting.setbatch')->with($data);
    }

    public function deletebatch($id)
    {
        if (DB::table('batches')->count() == 1) {
            return redirect()->back()->with('error', __('text.can_not_delete_last_batch'));
        }
        DB::table('batches')->where('id', '=', $id)->delete();
        return redirect()->back()->with('success', __('text.word_done'));
    }



    public function setAcademicYear($id)
    {
        // dd($id);
        $year = Config::all()->last();
        $data = [
            'year_id' => $id
        ];
        $year->update($data);

        return redirect()->back()->with('success', __('text.word_done'));
    }

    
    public function download_index()
    {
        $data['title'] = "Download Data";
        return view('admin.download.index', $data);
    }

    public function students()
    {
        $data['title'] = "Students";
        $data['students'] = Students::orderBy('name')->get();
        return view('admin.students.index', $data);
    }

    public function import_students()
    {
        $data['title'] = "Import Students";
        return view('admin.students.import', $data);
    }

    public function save_students(Request $request){
        $validator = Validator::make($request->all(), ['file'=>'required|file|mimes:csv']);
        if($validator->fails()){
            return back()->with('error', $validator->errors()->first());
        }

        // save uploaded file
        try{
            $path = public_path('uploads');
            $fname = 'f__'.time().'students'.random_int(1000, 9999).'.csv';
            $file = $request->file('file');
            $file->move($path, $fname);
            $fstream = fopen("$path/$fname", 'r');
    
            $file_data = [];
            while(($row = fgetcsv($fstream, 1000, ',')) != null){
                $file_data[] = ['name'=>$row[0], 'matric'=>$row[1], 'dob'=>$row[2], 'pob'=>$row[3], 'level'=>$row[4], 'program'=>$row[5], 'gender'=>$row[6], 'nationality'=>$row[7], 'campus'=>$row[8]??0,'created_at'=>date('Y-m-d H:i:s', time()), 'updated_at'=>date('Y-m-d H:i:s', time())];
            }
            Students::insert($file_data);
            fclose($fstream);
            unlink("$path/$fname");
            return back()->wit('success', "Done");
        }catch(Throwable $th){
            return back()->with('error', $th->getMessage());
        }
    }

    public function download_students(Request $request)
    {
        $data['title'] = "Download Students";
        return view('admin.download.index', $data);
    }

    public function download_students_save(Request $request)
    {

        $path = public_path('uploads');
        $fname = 'f__'.time().'students'.random_int(1000, 9999).'.csv';
        
        

        $students = Students::all()->filter(function($row)use($request){
            return Carbon::parse($request->start_date)->isBefore(Carbon::parse($row->updated_at)) && Carbon::parse($request->end_date)->isAfter(Carbon::parse($row->updated_at));
        });
        
        $fstream = fopen("$path/$fname", 'x');

        $images = $students->pluck('img_url');
        foreach($students as $stud){
            fputcsv(
                $fstream, 
                ['name'=>$stud->name, 'matric'=>$stud->matric, 'dob'=>$stud->dob, 'pob'=>$stud->pob, 'level'=>$stud->level, 'program'=>$stud->program, 'gender'=>$stud->gender, 'nationality'=>$stud->nationality, 'campus'=>$stud->campus, 'school_id'=>$school->name, 'imgage'=>$stud->img_url]
            );
        }
        fclose($fstream);

        $zip = new ZipArchive();
        $zip_name = '__'.time().'.zip';
        $files_folder = public_path('uploads/id_images/');

        // Opening zip file to load files
        if($zip->open($zip_name, ZIPARCHIVE::CREATE)!==TRUE)
        { 
            return back()->with('error', "* Sorry ZIP creation failed at this time");
        }
        $zip->addFile("$path/$fname");
        foreach($images as $file)
        { 
            $zip->addFile($files_folder.$file); // Adding files into zip
        }
        $zip->close();

        if(file_exists($zip_name))
        {
            // push to download the zip
            header('Content-type: application/zip');
            header('Content-Disposition: attachment; filename="'.$zip_name.'"');
            readfile($zip_name);
            // remove zip file is exists in temp path
            unlink($zip_name);
        }
    }
}
