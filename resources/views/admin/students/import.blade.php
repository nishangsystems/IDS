@extends('admin.layout')
@section('section')
    <div class="mx-3">
        <div class="form-panel row">
            <div class="col-md-6 py-3">
                <form class="form-horizontal" enctype="multipart/form-data" role="form" method="POST">
                    @csrf
    
                    <div class="form-group @error('file') has-error @enderror text-capitalize">
                        <label for="cname" class="control-label col-lg-2">{{__('text.csv_file')}} ({{__('text.word_required')}})</label>
                        <div class="col-lg-10">
                            <input class=" form-control" name="file" value="{{old('file')}}" type="file" required/>
                            @error('file')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
    
                    <div class="form-group">
                        <div class="d-flex justify-content-end col-lg-12">
                            <button id="save" class="btn btn-xs btn-primary mx-3" type="submit">{{__('text.word_save')}}</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-6 py-3 px-2">
                <div class="text-center text-capitalize text-primary py-3">CSV data must be in this order: <br>
                    name, matricule, date-of-birth, place-of-birth, level, program, gender, nationality, campus
                </div>
                {{-- <div class="text-center text-capitalize text-primary py-3">{{__('text.file_format_csv')}}</div> --}}
                {{-- <table class="bg-light">
                    <thead class="text-capitalize bg-dark text-light fs-6">
                        <th>name <span class="text-danger">*</span></th>
                        <th>matric <span class="text-danger">*</span></th>
                        <th>dob <span class="text-danger">*</span></th>
                        <th>pob <span class="text-danger">*</span></th>
                        <th>gender</th>
                    </thead>
                    <tbody>
                        @for($i=0; $i < 4; $i++)
                        <tr class="border-bottom">
                            <td>---</td>
                            <td>---</td>
                            <td>---</td>
                        @endfor
                    </tbody>
                </table> --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>

    $(document).ready(function(){
        loadPrograms(document.getElementById('campus_id'), 'program_id');
    });

</script>
@endsection
