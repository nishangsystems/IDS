@extends('student.layout')
@section('section')
@php
$user = auth('student')->user();
$user = $user == null ? auth()->user() : $user;
@endphp
    {{-- <div class="text-center py-3 alert-warning h4">
        <b>Warning:</b> You can only update your information/image once. So make sure your information is correct before updating.
    </div> --}}
    <div class="d-flex justify-content-center justify-items-center align-items-middle">
        <form method="POST" action="{{ route('student.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="col-md-11 col-lg-11 row mx-auto my-5 py-4 px-3">
                <div class="col-md-6 col-lg-4 py-3 text-capitalize">
                    <label class="col-sm-12">{{ __('text.word_name') }}</label>
                    <div class="col-sm-12">
                        <input class="form-control" required name="name" value="{{ old('name', $user->name) }}">
                    </div>
                </div> 
                <div class="col-md-6 col-lg-4 py-3 text-capitalize">
                    <label class="col-sm-12">{{ __('text.word_gender') }}</label>
                    <div class="col-sm-12">
                        <select class="form-control" required name="sex">
                            <option></option>
                            <option value="male" {{ old('sex', $user->sex) == 'male' ? 'selected' : '' }}>male</option>
                            <option value="female" {{ old('sex', $user->sex) == 'female' ? 'selected' : '' }}>female</option>
                        </select>
                    </div>
                </div> 
                <div class="col-md-6 col-lg-4 py-3 text-capitalize">
                    <label class="col-sm-12">{{ __('text.date_of_birth') }} <span class="text-danger">({{ old('dob', $user->dob)==null ? null : now()->parse(old('dob', $user->dob))->format('Y-m-d') }})</span></label>
                    <div class="col-sm-12">
                        <input type="date" required class="form-control" name="dob" value="{{ old('dob', $user->dob)==null ? null : now()->parse(old('dob', $user->dob))->format('Y-m-d') }}">
                    </div>
                </div> 
                <div class="col-md-6 col-lg-4 py-3 text-capitalize">
                    <label class="col-sm-12">{{ __('text.place_of_birth') }}</label>
                    <div class="col-sm-12">
                        <input class="form-control" required name="pob" value="{{ old('pob', $user->pob) }}">
                    </div>
                </div> 
                <div class="col-md-6 col-lg-4 py-3 text-capitalize">
                    <label class="col-sm-12">{{ __('text.word_matricule') }}</label>
                    <div class="col-sm-12">
                        <input class="form-control" required name="matricule" value="{{ $user->matricule }}" readonly>
                    </div>
                </div> 
                <div class="col-md-6 col-lg-4 py-3 text-capitalize">
                    <label class="col-sm-12">{{ __('text.word_program') }}</label>
                    <div class="col-sm-12">
                        <select class="form-control" required name="program">
                            <option></option>
                            @foreach ($programs as $prog)
                                <option {{ old('program', $user->program??null)  == $prog ? 'selected' : ''}}>{{ $prog }}</option>
                            @endforeach
                        </select>
                    </div>
                </div> 
                <div class="col-md-6 col-lg-4 py-3 text-capitalize">
                    <label class="col-sm-12">{{ __('text.word_campus') }}</label>
                    <div class="col-sm-12">
                        <select class="form-control" required name="campus">
                            <option></option>
                            @foreach ($campuses as $campus)
                                <option value="{{ $campus }}" {{ old('campus', $user->campus) == $campus ? 'selected' : '' }}>{{ $campus }}</option>
                            @endforeach
                        </select>
                    </div>
                </div> 
                <div class="col-md-6 col-lg-4 py-3 text-capitalize">
                    <label class="col-sm-12">{{ __('text.word_level') }}</label>
                    <div class="col-sm-12">
                        <select class="form-control" required name="level">
                            <option></option>
                            @foreach ($levels as $level)
                                <option value="{{ $level }}" {{ old('level', $user->level) == $level ? 'selected' : '' }}>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                </div> 
                <div class="col-md-6 col-lg-4 py-3 text-capitalize">
                    <label class="col-sm-12">{{ __('text.word_nationality') }}</label>
                    <div class="col-sm-12">
                        <select class="form-control" required name="nationality">
                            <option></option>
                            @foreach (config('all_countries.list') as $country)
                                <option value="{{ $country['name'] }}" {{ old('nationality', $user->nationality??null) == $country['name'] ? 'selected' : '' }}>{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div> 
                <div class="col-md-6 col-lg-4 py-3 text-capitalize">
                    <label class="col-sm-12">{{ __('text.word_photo') }}</label>
                    <div class="col-sm-12">
                        <input class="form-control" required name="image" type="file" accept="image/*" onchange="preview(event)">
                    </div>
                </div>
                <div class="col-md-12 col-lg-12 py-3 d-flex justify-content-center">
                    @if($user->status != 0)
                        <img class="img-responsive my-3 mx-auto img-rounded" style="height: 12rem; width: 12rem; border-radius: 0.6rem;" src="{{ $user->link }}">
                    @else
                        <div class="d-flex justify-content-end col-12">
                            <img id="preview_img" class="img-responsive" style="width: 12rem; height: 12rem; border-radius: 0.6rem;">
                        </div>
                        <input class="btn btn-md btn-primary" value="UPDATE" type="submit">
                    @endif
                </div> 
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script>
    
        let preview = function(event){
            let file = event.target.files[0];
            let url = URL.createObjectURL(file);
            $('#preview_img').prop('src', url);
        }
    </script>
@endsection