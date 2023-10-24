<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Students;
use App\Models\User;
// use Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use \Cookie;
use Illuminate\Support\Facades\Auth;

class CustomLoginController extends Controller
{
    public function __construct(){
        $this->middleware('guest:web', ['except'=>['logout']]);
    }

    public function showLoginForm(){
        return view('auth.login');
    }
    
     public function registration(){
        return $this->registration();
    } 

    public function showRegistrationForm()
    {
        # code...
        return view('auth.registration');
    }
 
    public function createAccount(Request $request){
        if (Students::where('matric', $request->username)->exists()) {  
            $update['phone'] = $request->phone;
            $update['email'] = $request->email;
            $update['password'] = Hash::make($request->password);
            
            $up = Students::where('matric', $request->username)->update($update);
             if (User::where('username', $request->username)->exists()) {  
            $update1['name'] = $request->name;
            $update1['email'] = $request->email;
            $update1['username'] = $request->username;
            $update1['type'] = 'student';
            $update1['password'] = Hash::make($request->password);
            
            $up1 = User::where('username', $request->username)->update($update1);
             }else{
                 $insert['name'] = $request->name;
                $insert['email'] = $request->email;
                $insert['username'] = $request->username;
                $insert['type'] = 'student';
                $insert['gender'] = '';
                $insert['password'] = Hash::make($request->password);
            
            $up2 = User::create($insert);
             }
        //      if( Auth::guard('student')->attempt(['matric'=>$request->username,'password'=>$request->password], $request->remember)){
        //     // return "Spot 1";
        //     return redirect()->intended(route('student.home'));
        // }else{
        //     return redirect()->route('login')->with('s','Account created successfully.');   
        // }
            return redirect()->route('login')->with('s','Account created successfully.');   
            //return redirect()->route('student.home')->with('s','Account created successfully.');   
            
          }
          
    }

    public function detail(Request $request){
        $type = Cookie::get('iam');
        $user = Cookie::get('iamuser');
        $data['type'] = $type;

        if($type != '' && $user != ''){
            if($type == 0){
                $data['user'] = \App\StudentInfo::find($user);
        }else{
                $data['user'] = \App\Teacher::find($user);
        }
            return view('auth.register')->with($data);
        }else{
            return redirect()->route('register');
        }
    }

    public function login(Request $request){
        session()->flush();
         //return $request->all();
        //validate the form data
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required|min:2'
        ]);
        // Update login: student can login with matric and phone/password, admin login with email and password
        if( (Auth::guard('student')->attempt(['matric'=>$request->username,'password'=>$request->password], $request->remember))){
            // return "Spot 1";
            return redirect()->intended(route('student.home'));
        }else{
            if( Auth::attempt(['username'=>$request->username,'password'=>$request->password])){
                return redirect()->route('admin.home')->with('success','Welcome to Admin Dashboard '.Auth::user()->name);
            }
        }
        // return "Spot 3";
        $request->session()->flash('error', 'Invalid Username or Password');
        return redirect()->route('login')->withInput($request->only('username','remember'));
    }

    public function logout(Request $request){
        Auth::logout();
        Auth::guard('student')->logout();
        session()->flush();
        return redirect(route('login'));
    }

}
