<?php

namespace App\Http\Controllers\Student;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Students;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    
    public function index()
    {
        $user = auth('student')->user();
        $img_url = $user->img_path.'/'.$user->photo;
        $pos = stripos($img_url, 'uploads/id_images');
        $asset_relative_url = substr($img_url, -(strlen($img_url)-$pos));
        $data['programs'] = Students::distinct()->pluck('program')->toArray();
        $data['campuses'] = Students::distinct()->pluck('campus')->toArray();
        $data['levels'] = Students::distinct()->pluck('level')->toArray();
        $data['img_url'] = asset($asset_relative_url);
        // dd($data);
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
            'matricule' => 'required',
            'sex' => 'required',
            'pob' => 'required',
            'dob' => 'required',
            'program' => 'required',
            'campus' => 'required',
            'level' => 'required',
            'image' => 'file|nullable',
            'nationality' => 'required',
        ]);
        if ($validator->fails()) {
            session()->flash('error', $validator->errors()->first());
            return back()->withInput();
        }


        $stud = auth('student')->user();

        try {
            //code...
            $update = $request->all();
            if($request->image != null){
                $img_path = public_path('uploads/id_images/'.now()->format('Y-m')).'/day-'.now()->format('d');
                $file = $request->file('image');
                
                $file_type = $file->getClientOriginalExtension();
                $fname = 'ph'.time().random_int(1000, 9999).'.'.$file_type;
                $file->move($img_path, $fname);
    
                $update['photo'] = $fname;
                $update['img_path'] = $img_path;
                $update['link'] = url('/');
                // return 1234;
            }
            $stud->update($update);
    
            return redirect(route('student.home'))->with('success', 'Record updated successfully');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            session()->flash('error', "F:: {$th->getFile()}, L:: {$th->getLine()}, M:: {$th->getMessage()}");
            return back();
        }
        
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

                $file_parts = explode(';base64,', $request->image);
                $file_type_aux = explode('image/', $file_parts[0]);

                $file_type = $file_type_aux[1];
                $fname = 'ph'.time().random_int(1000, 9999).'.'.$file_type;
                $file_base64 = base64_decode($file_parts[1]);
                file_put_contents($img_path.$fname, $file_base64);

                $stud->img_url = $fname;
                $stud->save();
                return redirect()->back()->with(['success' => 'Record updated successfully']);
            }
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function drop_image(Request $request){
        $student = auth('student')->user();
        try {
            //code...
            DB::beginTransaction();
            if($student->photo != null){
                $path = $student->img_path.'/'.$student->photo;
                if(file_exists($path)){
                    unlink($path);
                }else{
                    // dd($path);
                }
            }
    
            $student->photo = null;
            $student->img_path = null;
            $student->save();
            DB::commit();
    
            return back()->with('success', "Operation complete");
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            session()->flash('error', "F:: {$th->getFile()}, L:: {$th->getLine()}, M:: {$th->getMessage()}");
            return back();
        }
    }

    public function __construct()
    {
        
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
