@extends('admin.layout')
@section('section')
    <div class="py-3">
        <div class="container-fluid card">
            <div class="card-header text-center text-uppercase h4 text-primary py-2"><b>{{$title??''}}</b></div>
            <div class="card-body py-2">
                <form method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-xl-5">
                            <small class="text-info text-capitalize"><i><b>@lang('text.word_from')</b></i></small>
                            <div class="pt-2">
                                <input type="date" name="start_date" class="form-control rounded border-top-0 border-left-0 border-right-0" value="{{now()->addDays(-10)->format('Y-m-d')}}">
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-5">
                            <small class="text-info text-capitalize"><i><b>@lang('text.word_to')</b></i></small>
                            <div class="pt-2">
                                <input type="date" name="end_date" class="form-control rounded border-top-0 border-left-0 border-right-0" value="{{now()->format('Y-m-d')}}">
                            </div>
                        </div>
                        <div class="col-xl-2">
                            <button type="submit" class="btn btn-sm rounded btn-primary form-control text-capitalize">@lang('text.word_download')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection