@extends('admin.layout')

@section('section')
@php
    $year = request('year') ?? \App\Helpers\Helpers::instance()->getCurrentAccademicYear();
@endphp

<div class="col-sm-12">
    
    <div class=" my-3">
        <input class="form-control" id="search_field" placeholder="search by name or matricule">
    </div>
    <div class="">
        <div class=" ">
            <table cellpadding="0" cellspacing="0" border="0" class="table table-stripped" id="hidden-table-info">
                <thead>
                    <tr class="text-capitalize">
                        <th>#</th>
                        <th>{{__('text.word_name')}}</th>
                        <th>{{__('text.word_matricule')}}</th>
                        <th>{{__('text.word_program')}}</th>
                        <th>{{__('text.word_validity')}}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="table_body">
                </tbody>
            </table>
            <div class="d-flex justify-content-end">

            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $('#search_field').on('keyup', function() {
        let value = $(this).val();
        url = "{{route('search_students')}}";
        // console.log(url);
        $.ajax({
            type: 'GET',
            url: url,
            data: {
                'key': value
            },
            success: function(response) {
                let html = '';
                let k = 1;
                console.log(response);
                response.forEach(element => {
                    // console.log(element);
                    html += `
                    <tr>
                        <td>${k++}</td>
                        <td>${element.name}</td>
                        <td>${element.matricule}</td>
                        <td>${element.program}</td>
                        <td>${element.valid}</td>
                        <td class="d-flex justify-content-end  align-items-start text-capitalize">
                            <a class="btn btn-sm btn-primary m-1" href="{{route('admin.reset_student_data', '__STID__')}}"><i class="fa fa-info-circle text-capitalize"> {{__('text.word_reset')}}</i></a> |                            
                        </td>
                    </tr>
                    `.replace('__STID__', element.id);
                }); 
                $('#table_body').html(html);
            },
            error: function(e) {
                console.log(e)
            }
        })
    })

</script>
@endsection