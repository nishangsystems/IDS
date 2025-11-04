<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helpers;
use App\Models\Batch;
use App\Models\Students;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    //
    private $years;
    private $batch_id;


    public function __construct()
    {
        $this->batch_id = Helpers::instance()->getCurrentAccademicYear();
        $this->years = Batch::all();
    }

    // PAYMENTS FOR PLATFORM CHARGES, SEMESTER RESULT CHARGES AND TRANSCRIPT CHARGES(FOR FORMER STUDENTS ONLY) INTO THE COMPANY's ACCOUNT

    public function make_payment(Request $request)
    {
        # code...
        try {
            //code...
            $student = auth('student')->user();
            if($student->admission_batch_id == Helpers::instance()->getCurrentAccademicYear()){
                session()->flash('error', 'Only returning students are permitted to pay for thier ID cards');
                return redirect()->route('student.home');
            }
            $data['student'] = $student;
            $data['title'] = "Pay For ID Card";
            $data['amount'] = 5;
            $data['purpose'] = 'IDCARD';
            $data['year_id'] = Helpers::instance()->getCurrentAccademicYear();
            $data['student_id'] = $student->id;
            $data['payment_id'] = 0;
            return view('student.platform.charges', $data);
        } 
        catch(ConnectException $th){
            Log::error($th);
            session()->flash('error', "Operation failed. Could not connect to the momo servers. Check logs for details on this error");
            return back();
        }
        catch (\Throwable $th) {
            //throw $th;
            Log::error($th);
            session()->flash('error', "Operation failed. See log file for details");
            return back();
        }
    }

    public function save_payment(Request $request)
    {
        # code...
        try {
            
            $validator = Validator::make($request->all(),
            [
                'tel'=>'required|numeric|min:9',
                'amount'=>'required|numeric',
                'student_id'=>'required|numeric',
                'year_id'=>'required|numeric',
                'payment_purpose'=>'required',
                'payment_id'=>'required|numeric'
            ]);
            
            if ($validator->fails()) {
                # code...
                return back()->with('error', $validator->errors()->first());
            }

            //code...
            $data = $request->all();
            $response = Http::post(env('CHARGES_PAYMENT_URL'), $data);
            // dd($response->json());
            
            if($response->ok()){
            
                $_data['title'] = "Pending Confirmation";
                $_data['transaction_id'] = $response->collect()->first();
                // return $_data;
                return view('student.platform.payment_waiter', $_data);
            }else{
                return back()->with('error', 'Operation failed. ');
            }
        } 
        catch(\Throwable $th){
            // throw $e;
            Log::error($th);
            return back()->with('error', $th->getMessage());
        }
    }

    public function complete_charges_transaction(Request $request, $ts_id)
    {
        # code...
        try {
            //code...
            $transaction = Transaction::where(['transaction_id'=>$ts_id])->first();
            if($transaction != null){
                // update transaction
                $transaction->status = "completed";
                $transaction->is_charges = true;
                $transaction->financialTransactionId = $request->financialTransactionId;
                // dd($transaction);
                $transaction->save();
                switch($transaction->payment_purpose){
                    case 'IDCARD':
                        // dd($transaction->payment_purpose);
                        $data = [
                            "card_payment_transaction_id"=>$transaction->id,
                            "card_payment_year_id"=>$transaction->year_id
                        ];
                        $student = Students::find($transaction->student_id);
                        $student->update($data);
                        // dd($student);
                        return redirect( route('student.home'))->with('success', 'Payment complete');
                        break;
    
                }
            }else{
                session()->flash('error', 'No transaction was initialised for this payment');
                return back();
            }
        } catch (\Throwable $th) {
            Log::error($th);
            throw $th;
            session()->flash('error', 'Operation failed. '.$th->getMessage());
            return back();
        }
    }

    public function failed_charges_transaction(Request $request, $ts_id)
    {
        # code...
        $transaction = Transaction::where(['transaction_id'=>$ts_id])->first();
        if($transaction != null){
            // update transaction
            $transaction->status = "FAILED";
            $transaction->financialTransactionId = $request->financialTransactionId;
            $transaction->is_charges = 'true';
            $transaction->save();
            switch($transaction->payment_purpose){
                case 'IDCARD':
                    return redirect(route('student.make_payment'))->with('error', 'Operation Failed');
                    break;
            }

            // redirect user
            return redirect(route('student.home'))->with('error', 'Operation failed.');
        }else{
            session()->flash('error', 'No transaction was initialised for this payment');
            return back();
        }
    }

    public function online_payment_history(Request $request)
    {
        # code...
        $year = $request->year ?? null;
        $filter = $request->fiilter ?? null;
        $user = auth('student')->user();
        $data['user'] = $user;
        $data['title'] = "My Transactions";
        $data['transactions'] = $user->transactions()->where(function($row){
            $row->where('payment_purpose', '=', 'PLATFORM')
                ->orWhere('payment_purpose', '=', 'RESULTS')
                ->orWhere('payment_purpose', '=', 'TRANSCRIPT');
        })
        ->where(['status'=>'SUCCESSFUL'])
        ->get();
        $data['fees'] = Payments::where('student_id', auth('student')->id())->whereNotNull('transaction_id')->get();
        $data['other_payments'] = PayIncome::where('student_id', auth('student')->id())->whereNotNull('transaction_id')->get();
        $data['transcripts'] = Transcript::where('student_id', auth('student')->id())->whereNotNull('transaction_id')->get();
        $data['charges'] = Charge::where('student_id', auth('student')->id())->whereNotNull('financialTransactionId')->get();
        $data['resits'] = \App\Models\Resit::join('student_courses', 'student_courses.resit_id', '=', 'resits.id')
            ->whereNotNull('student_courses.paid')->where('student_courses.student_id', $user->id)
            ->join('tranzak_transactions', 'tranzak_transactions.id', '=', 'student_courses.paid')
            ->groupBy('resits.id')
            ->select(['resits.*', 'tranzak_transactions.amount', 'tranzak_transactions.created_at as transaction_date', 'tranzak_transactions.transaction_id',])->distinct()->get();
        // dd($data);
        return view('student.online_payment_history', $data);
    }

}
