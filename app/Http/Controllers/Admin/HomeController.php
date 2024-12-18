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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use MongoDB\Driver\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
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
        $data['title'] = "Upload Printed Student IDs";
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
    
            while(($row = fgetcsv($fstream, 1000, ',')) != null){
                try {
                    //code...
                    $data = ['name'=>$row[0], 'matricule'=>$row[1]];
                    if(($instance = Students::where($data)->first()) != null){
                        $instance->printed_at = now();
                        $instance->save();
                    }
                } catch (\Throwable $th) {
                    // throw $th;
                }
            }
            fclose($fstream);
            unlink("$path/$fname");
            return back()->with('success', "Done");
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

        $validator = Validator::make($request->all(), ['start_date'=>'required|date', 'end_date'=>'required|date']);
        if($validator->fails()){
            return back()->with('error', $validator->errors()->first())->withInput();
        }

        // dd($request->all());
        $path = public_path('uploads');
        $fname = 'f__'.time().'students'.random_int(1000, 9999).'.csv';

        $downloaded_file_name = "ID_data-From_".now()->parse($request->start_date)->format('Y-m-d').'_to_'.now()->parse($request->end_date)->format('Y-m-d').'.csv';
        $downloaded_zip_name = "ID_data-From_".now()->parse($request->start_date)->format('Y-m-d').'_to_'.now()->parse($request->end_date)->format('Y-m-d').'.zip';
        
        $students = Students::whereNotNull('photo')->whereNotNull('img_path')->whereDate('date', '>=', $request->start_date)->whereDate('date', '<=', $request->end_date)->get();
        // dd($students);
        $fstream = fopen("$path/$fname", 'x');
        fputcsv(
            $fstream, [
                'NAME', 'MATRICULE', 'DoB', 'PoB', 'LEVEL', 'PROGRAM', 'GENDER', 'NATIONALITY', 'CAMPUS', 'IMAGE', 'VALIDITY', 'IMAGE_PATH'
                ]
        );
        foreach($students as $stud){
            fputcsv(
                $fstream, [
                    'name'=>$stud->name, 
                    'matric'=>$stud->matricule, 
                    'dob'=>$stud->dob->format('d/m/Y'), 
                    'pob'=>$stud->pob, 
                    'level'=>$stud->level, 
                    'program'=>$stud->program, 
                    'gender'=>$stud->sex, 
                    'nationality'=>$stud->nationality, 
                    'campus'=>$stud->campus, 
                    'image'=>$stud->photo, 
                    'validity'=>$stud->valid,
                    'image_path'=>$stud->img_path
                    ]
            );
        }
        fclose($fstream);
        
        $students->each(function($rec){
            $rec->update(['downloaded_at'=>now()]);
        });

        if($request->with_photos != 'YES')
            return response()->download("$path/$fname", $downloaded_file_name)->deleteFileAfterSend(true);

        $zip = new ZipArchive();
        $zip_name = '__'.time().'.zip';
        $files_folder = public_path('uploads/id_images/');

        // Opening zip file to load files
        if($zip->open($zip_name, ZIPARCHIVE::CREATE)!==TRUE)
        { 
            return back()->with('error', "* Sorry ZIP creation failed at this time");
        }
        $zip->addFile("$path/$fname", $downloaded_file_name);
        foreach($students as $stud)
        { 
            $zip->addFile($stud->img_path.'/'.$stud->photo, $stud->photo); // Adding files into zip
        }
        $zip->close();

        if(file_exists($zip_name))
        {
            // push to download the zip
            $headers = ['Content-type'=>'application/zip', 'Content-Disposition'=>'attachment; filename="'.$zip_name.'"'];
            return response()->download($zip_name, $downloaded_zip_name, $headers)->deleteFileAfterSend(true);
        }
    }

    public function reset_student_data(Request $request, $record_id) {
        $student = Students::find($record_id);
        if($student->img_path != null and $student->photo != null){
            $image_path = $student->img_path.'/'.$student->photo;
            if(file_exists($image_path)){unlink($image_path);}
        }
        $student->updated_at = null;
        $student->downloaded_at = null;
        $student->printed_at = null;
        $student->save();
        return back()->with('success', "Operation complete");
    }

    public function printed_ids_index(){
        $data['title'] = "Download Student List for Printed IDs";
        return view('admin.students.printed_list', $data);
    }

    public function download_printed_ids(Request $request){
        try {
            $request->validate(['start_date'=>'required|date', 'end_date'=>'required|date']);
            $start_date = now()->parse($request->start_date)->hour(00)->minute(00)->second(00);
            $end_date = now()->parse($request->end_date)->hour(23)->minute(59)->second(59);
            $data = Students::whereDate('printed_at', '>=', $request->start_date)->whereDate('printed_at', '<=', $request->end_date)->orderBy('name')->get();
            
            $fname = "printed_students{time()}.csv";
            $file_url = storage_path('app/public/'.$fname);
            // if(!file_exists($file_url)){Storage::disk('public')->put($fname, ' ');}
            $file_stream = fopen($file_url, 'w');
            fputcsv($file_stream, ['MATRICIULE', 'NAME']);
            // write to file
            foreach($data->groupBy('program') as $program => $program_data){
                $rec = ["{$program}>>>>>>>>>>>>>>><<<<<<<<<<<<<<<<<<<<<<<{$program_data->count()}"];
                fputcsv($file_stream, [' ', ' ']);
                fputcsv($file_stream, $rec);
                fputcsv($file_stream, ['============================', '==============================']);
                foreach($program_data as $record){
                    fputcsv($file_stream, [$record->matricule, $record->name]);
                }
            }
    
            fclose($file_stream);
            
            return response()->download($file_url, "PRINTED-ID-CARDS-FROM_{$start_date->format('Y-m-d')}_TO_{$end_date->format('Y-m-d')}.csv")->deleteFileAfterSend(true);
            //code...
        } catch (\Throwable $th) {
            throw $th;
            session()->flash('error', "Error encountered. F:: {$th->getFile()}, L:: {$th->getLine()}, M:: {$th->getMessage()}");
            return back();
        }
    }
}
