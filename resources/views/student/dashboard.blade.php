@extends('student.layout')
@section('section')
@php
$user = auth('student')->user();
$user = $user == null ? auth()->user() : $user;
@endphp
    {{-- <div class="px-3 py-3 alert alert-warning h4 rounded border border-warning">
        <b>Warning:</b> <br> 
        <ul style="list-style-type: disc;">
            <li>Half photos only are allowed, from chest level upwards.</li>
            <li>Your half photo must be taken in school uniform</li>
            <li>Cross-check the form to ensure that your information is correct</li>
            <li>Image sample shown below</li>
        </ul>
        <div class="d-flex justify-content-end py-2">
            <img src="{{ asset('icons/sample-half-photo.png') }}" alt="" style="width: 9.8rem; height: 9.8rem; border-radius: 0.3rem; border: 2px solid #fff">
        </div>
        <i>Failing to provide the right information is at your risk. No corrections will be made after the ID card is printed </i>
    </div> --}}
    <!-- Button trigger modal -->
    {{-- <div class="modal modal-lg modal-primary" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle">
        <div class="modal-dialog modal-dialog-centered w-100" role="document">
            <div class="modal-content w-100"> --}}
                <div class="modal-header">
                    <h3 class="modal-title text-capitalize" id="exampleModalCenterTitle"><b>Photo Upload Guide</b></h3>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 text-center text-uppercase text-primary border-b"><u class="h4"><b>Allowed Photo Samples</b></u></div>
                        <div class="col-sm-4 col-md-3 col-lg-3 col-xl-3 p-2">
                            <div class="card rounded border-0 shadow-sm">
                                <img src="{{ asset('icons/sample-half-photo-1.png') }}" alt="Sample Good Image 1" class="card-img-top img-fluid img" style="height: 11rem;">
                                <div class="card-body px-1 py-1">
                                    <p class="text">Half photo only, from chest level upwards</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-md-3 col-lg-3 col-xl-3 p-2">
                            <div class="card rounded border-0 shadow-sm">
                                <img src="{{ asset('icons/sample-half-photo-2.png') }}" alt="Sample Good Image 1" class="card-img-top img-fluid img" style="height: 11rem;">
                                <div class="card-body px-1 py-1">
                                    <p class="text">half photo must be taken in school uniform</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-md-3 col-lg-3 col-xl-3 p-2">
                            <div class="card rounded border-0 shadow-sm">
                                <img src="{{ asset('icons/sample-half-photo-3.png') }}" alt="Sample Good Image 1" class="card-img-top img-fluid img" style="height: 11rem;">
                                <div class="card-body px-1 py-1">
                                    <p class="text">Photo must be taken in plane background, preferrably white.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-md-3 col-lg-3 col-xl-3 p-2">
                            <div class="card rounded border-0 shadow-sm">
                                <img src="{{ asset('icons/sample-half-photo.png-4') }}" alt="Sample Good Image 1" class="card-img-top img-fluid img" style="height: 11rem;">
                                <div class="card-body px-1 py-1">
                                    <p class="text">Face must be upright</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 text-center text-uppercase text-danger border-b"><u class="h4"><b>Disallowed Photo Samples</b></u></div>
                        <div class="col-sm-4 col-md-3 col-lg-3 col-xl-3 p-2">
                            <div class="card rounded border-0 shadow-sm">
                                <img src="{{ asset('icons/sample-bad-photo-1.png') }}" alt="Sample Bad Image 1" class="card-img-top img-fluid img" style="height: 11rem;">
                                <div class="card-body px-1 py-1">
                                    <p class="text text-warning">Full photo not allowed</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-md-3 col-lg-3 col-xl-3 p-2">
                            <div class="card rounded border-0 shadow-sm">
                                <img src="{{ asset('icons/sample-bad-photo-2.png') }}" alt="Sample Bad Image 1" class="card-img-top img-fluid img" style="height: 11rem;">
                                <div class="card-body px-1 py-1">
                                    <p class="text text-warning">Half photo not from chest level upwords</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-md-3 col-lg-3 col-xl-3 p-2">
                            <div class="card rounded border-0 shadow-sm">
                                <img src="{{ asset('icons/sample-bad-photo-3.png') }}" alt="Sample Bad Image 1" class="card-img-top img-fluid img" style="height: 11rem;">
                                <div class="card-body px-1 py-1">
                                    <p class="text text-warning">Mixed color background not allowed</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-md-3 col-lg-3 col-xl-3 p-2">
                            <div class="card rounded border-0 shadow-sm">
                                <img src="{{ asset('icons/sample-bad-photo-4.png') }}" alt="Sample Bad Image 1" class="card-img-top img-fluid img" style="height: 11rem;">
                                <div class="card-body px-1 py-1">
                                    <p class="text text-warning">Photo/Image not upright.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 text-center text-danger pt-4">
                            <i>Failing to provide the right information is at your risk. No corrections will be made after the ID card is printed </i>
                        </div>
                    </div>
                </div>
                {{-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Understood</button>
                </div> --}}
            {{-- </div>
        </div>
    </div> --}}
    <hr>
    <div class="d-flex justify-content-center justify-items-center align-items-middle mx-0">
        <form method="POST" action="{{ route('student.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="row mx-auto my-3">
                <div class="col-md-8 col-lg-6 py-3 text-capitalize px-1">
                    <label class="">{{ __('text.word_name') }}</label>
                    <div class="">
                        <input class="form-control rounded" required name="name" value="{{ old('name', $user->name) }}">
                    </div>
                </div> 
                <div class="col-md-4 col-lg-3 py-3 text-capitalize px-1">
                    <label class="">{{ __('text.word_gender') }}</label>
                    <div class="">
                        <select class="form-control rounded" required name="sex">
                            <option></option>
                            <option value="male" {{ old('sex', $user->sex) == 'male' ? 'selected' : '' }}>male</option>
                            <option value="female" {{ old('sex', $user->sex) == 'female' ? 'selected' : '' }}>female</option>
                        </select>
                    </div>
                </div> 
                <div class="col-md-6 col-lg-3 py-3 text-capitalize px-1">
                    <label class="">{{ __('text.date_of_birth') }} <span class="text-danger">({{ old('dob', $user->dob)==null ? null : now()->parse(old('dob', $user->dob))->format('Y-m-d') }})</span></label>
                    <div class="">
                        <input type="date" required class="form-control rounded" name="dob" value="{{ old('dob', $user->dob)==null ? null : now()->parse(old('dob', $user->dob))->format('Y-m-d') }}">
                    </div>
                </div> 
                <div class="col-md-6 col-lg-4 py-3 text-capitalize px-1">
                    <label class="">{{ __('text.place_of_birth') }}</label>
                    <div class="">
                        <input class="form-control rounded" required name="pob" value="{{ old('pob', $user->pob) }}">
                    </div>
                </div> 
                <div class="col-md-6 col-lg-4 py-3 text-capitalize px-1">
                    <label class="">{{ __('text.word_matricule') }}</label>
                    <div class="">
                        <input class="form-control rounded" required name="matricule" value="{{ $user->matricule }}" readonly>
                    </div>
                </div> 
                <div class="col-md-6 col-lg-4 py-3 text-capitalize px-1">
                    <label class="">{{ __('text.word_program') }}</label>
                    <div class="">
                        <select class="form-control rounded" required name="program">
                            <option></option>
                            @foreach ($programs as $prog)
                                <option {{ old('program', $user->program??null)  == $prog ? 'selected' : ''}}>{{ $prog }}</option>
                            @endforeach
                        </select>
                    </div>
                </div> 
                <div class="col-md-6 col-lg-4 py-3 text-capitalize px-1">
                    <label class="">{{ __('text.word_campus') }}</label>
                    <div class="">
                        <select class="form-control rounded" required name="campus">
                            <option></option>
                            @foreach ($campuses as $campus)
                                <option value="{{ $campus }}" {{ old('campus', $user->campus) == $campus ? 'selected' : '' }}>{{ $campus }}</option>
                            @endforeach
                        </select>
                    </div>
                </div> 
                <div class="col-md-6 col-lg-4 py-3 text-capitalize px-1">
                    <label class="">{{ __('text.word_level') }}</label>
                    <div class="">
                        <select class="form-control rounded" required name="level">
                            <option></option>
                            @foreach ($levels as $level)
                                <option value="{{ $level }}" {{ old('level', $user->level) == $level ? 'selected' : '' }}>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                </div> 
                <div class="col-md-6 col-lg-4 py-3 text-capitalize px-1">
                    <label class="">{{ __('text.word_nationality') }}</label>
                    <div class="">
                        <select class="form-control rounded" required name="nationality">
                            <option></option>
                            @foreach (config('all_countries.list') as $country)
                                <option value="{{ $country['name'] }}" {{ old('nationality', $user->nationality??null) == $country['name'] ? 'selected' : '' }}>{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div> 
                <div class="col-md-6 col-lg-4 py-3 text-capitalize px-1">
                    <label class="">{{ __('text.word_photo') }}</label>
                    <div class="">
                        <input class="form-control rounded" required name="image" type="file" accept="image/*" onchange="preview(event)">
                    </div>
                </div>
                <div class="col-12 px-1 py-3 d-flex justify-content-center bg-dark rounded">
                    @if($user->photo != null)
                        <div class="d-flex justify-content-end col-12">
                            <img class="img-responsive my-3 mx-auto img-rounded" style="height: 12rem; width: 12rem; border-radius: 0.6rem;" src="{{ $img_url }}">
                        </div>
                    @else
                        <div class="d-flex justify-content-end col-12">
                            <img id="preview_img" class="img-responsive" style="width: 12rem; height: 12rem; border-radius: 0.6rem;">
                        </div>
                    @endif
                </div> 
                <dov class="col-12 px-1 d-flex justify-content-end py-2 border-top">
                    @if($user->downloaded_at == null)
                        @if($user->photo != null)
                            @if($user->status == 0)
                                <span class="d-flex flex-column justify-content-end rounded"><a href="{{route('student.drop_image')}}" class="btn btn-md rounded btn-danger text-uppercase ">@lang('text.clear_and_reupload_image')</a></span>
                            @endif
                        @else
                            <input class="btn btn-md btn-primary rounded" value="UPDATE" type="submit">
                        @endif
                    @endif
                </dov>
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

        $(document).ready(()=>{
            $('#exampleModalCenter').modal('show');
        })
    </script>
@endsection