<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StudentCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(($student = $request->user('student')) != null){
            if($student->admission_batch_id == \App\Helpers\Helpers::instance()->getCurrentAccademicYear()){
                // check if student has not paid registration fee and block if so
                if($student->reg_payment_status == 0){
                    auth('student')->logout();
                    session()->flush();
                    session()->flash('error', "Access denied. You must pay registration fee to proceed");
                    return redirect()->route('login');
                }
            }
            elseif ($student->admission_batch_id != null) {
                // check if student has paid returning student ID card fee and redirect to pay if not done
                if(($student->card_payment_year_id != \App\Helpers\Helpers::instance()->getCurrentAccademicYear())){
                    session()->flash('error', "All returning students are required to pay a sum of 3000 FCFA to obtain a new ID card.");
                    return redirect(route('student.make_payment'));
                }
            }else{
                // redirect student to login as to capture the admission_batch_id
                session()->flash('error', "Missing admission year data. Try to login again for the system to capture missing info");
                session()->flush();
                return redirect(route('login'));
            }
            
        }
        return $next($request);
    }
}
