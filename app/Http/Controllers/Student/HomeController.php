<?php

namespace App\Http\Controllers\Student;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TransactionController;
use App\Http\Services\ApiService;
use App\Http\Services\SMSHelpers;
use App\Models\ApplicationForm;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\CampusProgram;
use App\Models\CampusSemesterConfig;
use App\Models\Charge;
use App\Models\ClassSubject;
use App\Models\Config;
use App\Models\CourseNotification;
use App\Models\Income;
use App\Models\Material;
use App\Models\NonGPACourse;
use App\Models\Notification;
use App\Models\PayIncome;
use App\Models\Payments;
use App\Models\PlatformCharge;
use App\Models\ProgramLevel;
use App\Models\Resit;
use App\Models\Result;
use App\Models\SchoolUnits;
use App\Models\Semester;
use App\Models\Sequence;
use App\Models\StudentClass;
use App\Models\Students;
use App\Models\StudentStock;
use App\Models\StudentSubject;
use App\Models\SubjectNotes;
use App\Models\Subjects;
use App\Models\Topic;
use App\Models\Transaction;
use App\Models\Transcript;
use App\Models\TranzakCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    private $years;
    private $batch_id;
    private $select = [
        'students.id as student_id',
        'collect_boarding_fees.id',
        'students.name',
        'students.matric',
        'collect_boarding_fees.amount_payable',
        'collect_boarding_fees.status',
        'school_units.name as class_name'
    ];

    private $select_boarding = [
        'students.id as student_id',
        'students.name',
        'students.matric',
        'collect_boarding_fees.id',
        'boarding_amounts.created_at',
        'boarding_amounts.amount_payable',
        'boarding_amounts.total_amount',
        'boarding_amounts.status',
        'boarding_amounts.balance'
    ];

    public function index()
    {
        $data['programs'] = Students::distinct()->pluck('program')->toArray();
        $data['campuses'] = Students::distinct()->pluck('campus')->toArray();
        $data['levels'] = Students::distinct()->pluck('level')->toArray();
        return view('student.dashboard', $data);
    }

    public function profile()
    {
        return view('student.edit_profile');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'matric' => 'required',
            'gender' => 'required',
            'pob' => 'required',
            'dob' => 'required',
            'program' => 'required',
            'campus' => 'required',
            'level' => 'required',
            'nationality' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with(['error' => $validator->errors()->first()])->withInput();
        }

        $stud = auth('student')->user();
        $data = ['name'=>$request->name, 'pob'=>$request->pob, 'dob'=>$request->dob, 'gender'=>$request->gender, 'campus'=>$request->campus, 'level'=>$request->level, 'nationality'=>$request->nationality];
        $stud->update($data);
        return redirect(route('student.add_image'))->with(['success' => 'Record updated successfully']);
        
    }

    public function update_image_save(Request $request){

        // return 1234;
        $validator = Validator::make($request->all(), ['image'=>'required']);            
        if ($validator->fails()) {
            return  $validator->errors()->first();
        }

        try{
            $stud = auth('student')->user();
            $data = [];
            if($request->image != null){
                $img_path = public_path('uploads/id_images/');
                // $fname = 'photo__'.time().'__'.random_int(1000, 9999).'.'.$file->getClientOriginalExtension();
                $file_parts = explode(';base64,', $request->image);
                $file_type_aux = explode('image/', $file_parts[0]);
                // return $file_type_aux;
                $file_type = $file_type_aux[1];
                $fname = 'ph'.time().random_int(1000, 9999).'.'.$file_type;
                $file_base64 = base64_decode($file_parts[1]);
                file_put_contents($img_path.$fname, $file_base64);

                // if($stud->img_url != null && file_exists($img_path.$stud->img_url)){
                //     unlink($img_path.$stud->img_url);
                // }
                $request_data = $request->data;
                // return $request_data;
                $stud->img_url = $fname;
                // return 1234;
                $stud->save();
                return redirect()->back()->with(['success' => 'Record updated successfully']);
            }
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function update_image(Request $request){
        $data['title'] = "Update Photo";
        return view('student.add_image', $data);
    }

    public function __construct()
    {
        // $this->middleware('isStudent');
        // $this->boarding_fee =  BoardingFee::first();
        //  $this->year = Batch::find(Helpers::instance()->getCurrentAccademicYear())->name;
        $this->batch_id = Batch::find(Helpers::instance()->getCurrentAccademicYear())->id;
        $this->years = Batch::all();
    }
 
    public function edit_profile()
    {
        # code...
        $data['title'] = "Edit Profile";
        return view('student.edit_profile', $data);
    }

    public function update_profile(Request $request)
    {
        # code...
        if(
            Students::where([
                'email' => $request->email, 'phone' => $request->phone
            ])->count() > 0 && (auth('student')->user()->phone != $request->phone || auth('student')->user()->email != $request->email)
        ){
            return back()->with('error', __('text.validation_phrase1'));
        }
        
        $data = $request->all();
        Students::find(auth('student')->id())->update($data);
        return redirect(route('student.home'))->with('success', __('text.word_Done'));
    }

}
