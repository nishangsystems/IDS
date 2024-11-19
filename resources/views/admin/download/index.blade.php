@extends('admin.layout')
@section('section')
    <div class="py-2">
        <form method="post">
            @csrf
            <div class="d-flex justify-content-end py-3">
                <span class="text-capitalize"><i class="fa fa-file p-2">Download With Photos</i> <input type="checkbox" name="with_photos" class="ml-3 rounded" value="YES" id=""></span>
            </div>
            <div class="row">
                <div class="py-3 col-lg-4">
                    <select class="form-control rounded border-top-0 border-left-0 border-right-0" name="campus">
                        <option>select campus</option>
                        @foreach (\App\Models\Students::distinct()->pluck('campus')->toArray() as $cmp)
                            <option value="{{ $cmp }}">{{ $cmp }}</option>
                        @endforeach
                    </select>
                    <small><i class="text-capitalize">@lang('text.word_campus')</i></small>
                </div>
                <div class="my-3 col-lg-4">
                    <input type="date" class="form-control rounded border-top-0 border-left-0 border-right-0" name="start_date" placeholder="Start date" required>
                    <small><i class="text-capitalize">@lang('text.start_date')</i></small>
                </div>
                <div class="my-3 col-lg-4">
                    <input type="date" class="form-control rounded border-top-0 border-left-0 border-right-0" name="end_date" placeholder="end date" required>
                    <small><i class="text-capitalize">@lang('text.end_date')</i></small>
                </div>
            </div>
            <div class="py-3 d-flex justify-content-end">
                <button class="btn btn-sm btn-primary rounded" type="submit">Downlaod</button>
            </div>
        </form>
    </div>
@endsection