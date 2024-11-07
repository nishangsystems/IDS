<?php

namespace App\Http\Controllers;

use App\Models\Students;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

/**
 * Summary of Controller
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    var $current_accademic_year;
    public function __construct()
    {
        # code...
        // $this->current_accademic_year = Helpers::instance()->getCurrentAccademicYear();
        ini_set('max_execution_time', 360);
    }

    public function set_local(Request $request, $lang)
    {
        # code...
        // return $lang;
        if (array_key_exists($lang, Config::get('languages'))) {
            Session::put('appLocale', $lang);
            App::setLocale($lang);
        }
        return back();
    }

    public function reset_password(Request $request, $id= null)
    {
        # code...
        $data['title'] = "Reset Password";
        if (auth()->guard('student')->check()) {
            return view('student.reset_password', $data);
        }
        else {
            if (auth()->user()->type == 'admin') {
                return view('admin.reset_password', $data);
            }else{
                return view('teacher.reset_password', $data);
            }
        }
    }

    public function reset_password_save(Request $request)
    {
        # code...
        $validator = Validator::make($request->all(), [
            'current_password'=>'required',
            'new_password_confirmation'=>'required_with:new_password|same:new_password|min:6',
            'new_password'=>'required|min:6',
        ]);
        if($validator->fails()){
            return back()->with('error', $validator->errors()->first());
        }
        if (auth()->guard('student')->check()) {
            if(Hash::check($request->current_password, auth('student')->user()->getAuthPassword())){
                $stud = Students::find(auth('student')->id());
                $stud->password = Hash::make($request->new_password);
                $stud->password_reset = true;
                $stud->save();
                return back()->with('success', 'Done');
            }else{
                return back()->with('error', 'Operation failed. Make sure you entered the correct password');
            }
        }
        else{
            if(Hash::check($request->current_password, auth()->user()->getAuthPassword())){
                $user = User::find(auth()->id());
                $user->password = Hash::make($request->new_password);
                $user->password_reset = true;
                $user->save();
                return back()->with('success', 'Done');
            }else{
                return back()->with('error', 'Operation failed. Make sure you entered the correct password');
            }
        }

    }

    /**
     * Summary of sendSmsNotificaition
     * @param string $message_text
     * @param array|Collection $contacts
     * @return bool
     */

    public function search_user(Request $request)
    {
        # code...
        $search_key = $request->key;
        if($search_key == null){
            return null;
        }
        $users = User::where('name', 'LIKE', '%'.$search_key.'%')
                ->orWhere('matric', 'LIKE', '%'.$search_key.'%')
                ->orWhere('email', 'LIKE', '%'.$search_key.'%')
                ->orWhere('username', 'LIKE', '%'.$search_key.'%')
                ->take(10)->get();
        return response()->json(['users'=>$users]);
    }


}
