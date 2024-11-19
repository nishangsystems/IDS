@extends('admin.layout')
@section('section')
    <div class="mx-3">
        <div class="form-panel row">
            <div class="col-md-6 py-3">
                <form class="form-horizontal" enctype="multipart/form-data" role="form" method="POST">
                    @csrf
    
                    <label for="cname" class="control-label">{{__('text.csv_file')}} ({{__('text.word_required')}})</label>
                    <div class="@error('file') has-error @enderror text-capitalize mb-4">
                        <input class=" form-control rounded border-top-0 border-left-0 border-right-0" name="file" value="{{old('file')}}" type="file" required/>
                        @error('file')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
    
                    <div class="form-group">
                        <div class="d-flex justify-content-end col-lg-12">
                            <button id="save" class="btn btn-sm btn-primary rounded text-capitalize mx-3" type="submit">{{__('text.word_save')}}</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-6 py-3 px-2">
                <div class="text-center text-capitalize text-primary py-3">{{__('text.file_format_csv')}}</div>
                <table class="bg-light">
                    <thead class="text-capitalize bg-dark text-light fs-6">
                        <th>name <span class="text-danger">*</span></th>
                        <th>matric <span class="text-danger">*</span></th>
                    </thead>
                    <tbody>
                        @for($i=0; $i < 4; $i++)
                        <tr class="border-bottom">
                            <td>---</td>
                            <td>---</td>
                        @endfor
                    </tbody>
                </table>
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
