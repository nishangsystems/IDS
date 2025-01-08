@extends('admin.layout')
@section('section')
    <div class="container-fluid">
        <table class="table-stripped border">
            <thead class="text-capitalize border-bottom">
                <th>@lang('text.sn')</th>
                <th class="border-left">@lang('text.word_program')</th>
                <th class="border-left"></th>
            </thead>
            <tbody>
                @php
                    $counter = 1;
                @endphp
                @foreach ($students->groupBy('program') as $prog => $program_group)
                    <tr class="border-bottom">
                        <td>{{$counter++}}</td>
                        <td class="border-left">{{$prog}}</td>
                        <td class="border-left">
                            <button class="btn btn-sm rounded btn-primary text-capitalize" onclick="printData('#group{{$counter}}')">@lang('text.word_print')</button>
                            <div class="d-none">
                                <div id="group{{$counter}}">
                                    <table>
                                        <thead class="text-capitalize">
                                            <tr class="border-bottom"><th colspan="3"><h4 class="text-center text-capitalize">{{$prog}}</h4></th></tr>
                                            <tr class="border-bottom">
                                                <th>@lang('text.sn')</th>
                                                <th>@lang('text.word_name')</th>
                                                <th>@lang('text.word_matricule')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $k = 1;
                                            @endphp
                                            @foreach ($program_group as $item)
                                                <tr  class="border-bottom">
                                                    <td class="border-left">{{$k++}}</td>
                                                    <td class="border-left">{{$item['name']}}</td>
                                                    <td class="border-left">{{$item['matric']}}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
@section('script')
    <script>
        let printData = (elem)=>{
            let printable = $(elem);
            let doc = $(document.body).html();
            
            $(document.body).html(printable);
            window.print();

            $(document.body).html(doc);
        }
    </script>
@endsection