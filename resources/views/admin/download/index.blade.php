@extends('admin.layout')
@section('section')
    <div class="py-2">
        <form method="post">
            @csrf
            <div class="inout-group d-flex py-3">
                <select class="form-control" name="campus">
                    <option>select campus</option>
                    @foreach (\App\Models\Students::where('school_id', $school->id)->distinct()->pluck('campus_id')->toArray() as $cmp)
                        <option value="{{ $cmp }}">{{ $cmp }}</option>
                    @endforeach
                </select>
            </div>
            <div class="inout-group d-flex py-3">
                <input type="date" class="form-control" name="range_from" placeholder="Start date">
                <input type="date" class="form-control" name="rage_to" placeholder="end date">
            </div>
            <div class="py-3 d-flex justify-content-end">
                <button class="btn btn-sm btn-primary" type="submit">Downlaod</button>
            </div>
        </form>
    </div>
@endsection