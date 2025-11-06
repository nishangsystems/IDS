@extends('student.layout')
@section('section')
@php
    $c_year = \App\Helpers\Helpers::instance()->getCurrentAccademicYear();
@endphp
<div class="py-3">
        <div class="alert alert-info">
            Dear Students, <br>
            Please be informed that ID card production is completely FREE for all new students. However, returning students are required to make payment before their ID cards can be reprinted, in accordance with the school’s policies.<br><br>
            Thank you for your understanding and cooperation.
        </div>
        @if ($year_id == null)
            <form method="get">
                <input type="hidden" name="student_id" value="{{$student_id}}">
                <div class="row my-2">
                    <label class="text-capitalize col-sm-3">{{__('text.academic_year')}}</label>
                    <div class="col-sm-9"><select class="form-control" name="year_id" required>
                        <option></option>
                        @foreach (\App\Models\Batch::all() as $batch)
                            <option value="{{$batch->id}}" {{$batch->id == $c_year ? 'selected' : ''}}>{{$batch->name}}</option>
                        @endforeach
                    </select></div>
                </div>
                @if ($purpose == 'RESULTS')
                    <div class="row my-2">
                        <label class="text-capitalize col-sm-3">{{__('text.word_semester')}}</label>
                        <div class="col-sm-9"><select class="form-control" name="semester_id" required>
                            <option>----------</option>
                            @isset($student)
                                @foreach ($student->_class()->program->background->semesters as $sem)
                                    <option value="{{$sem->id}}">{{$sem->name}}</option>
                                @endforeach
                            @endisset
                        </select></div>
                    </div>
                @endif
                <div class="d-flex justify-content-end my-2 py-2">
                    <button type="submit" class="btn btn-sm btn-primary">{{__('text.word_proceed')}}</button>
                </div>
            </form>
        @else
            <!-- check if student has already paid the request  -->
            <div class="">
                
                <form method="post" id="poster-form">
                    <!-- SET REQUIRED HIDDEN INPUT FIELDS HERE -->
                    @csrf
                    <input type="hidden" name="purpose" value="{{$purpose}}">
                    <input type="hidden" name="payment_purpose" value="{{$purpose}}">
                    <input type="hidden" name="student_id" value="{{auth('student')->id()}}">
                    <input type="hidden" name="payment_id" value="{{$payment_id}}">
                    <input type="hidden" name="amount" value="{{$amount}}">
                    <input type="hidden" name="year_id" value="{{$year_id}}">
                    <div class="">
                        <div class="py-4 text-info text-center" style="font-size: x-large;"> You are about to make a payment of {{ $amount }} FCFA for your student ID card </div>
                    </div>
                    <div class="py-3 row">
                        <label for="cname" class="control-label col-lg-2 text-capitalize">{{__('text.word_amount')}} <span style="color:red">*</span></label>
                        <div class="col-lg-10">
                            <input class=" form-control" name="amount" value="{{ $amount }}" type="number" required readonly/>
                            @error('amount')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="py-3 row">
                        <label for="cname" class="control-label col-lg-2 text-capitalize">{{__('text.payment_number')}}<span style="color:red">*</span></label>
                        <div class="col-lg-10">
                            <input class=" form-control" name="tel" value="{{$student->phone??null}}" type="number" required />
                        </div>
                    </div>
                    <div class="py-3 row">
                        <div class="d-flex justify-content-end col-lg-12">
                            <button id="save" class="btn btn-xs btn-primary mx-3 text-capitalize" type="submit">{{__('text.make_payment')}}</button>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    </div>
@endsection