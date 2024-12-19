@extends('admin.layout')
@section('section')
    <div class="py-4 container-fluid">
        <div class="d-flex justify-content-end py-3">
            <button class="btn btn-primary btn-sm rounded h4 text-capitalize" onclick="printStats()">@lang('text.word_print')</button>
        </div>
        <div id="print-box">
            <table class="table">
                <thead class="text-capitalize text-primary">
                    <th>@lang('text.sn')</th>
                    <th>@lang('text.word_campus')</th>
                    <th>@lang('text.word_admitted')</th>
                    <th>@lang('text.word_printed')</th>
                </thead>
                <tbody>
                    @foreach ($stats->groupBy('campus') as $campus => $campus_stats)
                        <tr class="">
                            <th class="text-dark h4 text-capitalize border border-black" colspan="2">{{$campus}}</th>
                            <th class="text-dark h4 text-capitalize border border-black">{{number_format(collect($campus_stats)->sum('admitted_students'))}}</th>
                            <th class="text-dark h4 text-capitalize border border-black">{{number_format(collect($campus_stats)->sum('size'))}}</th>
                        </tr>
                        @php
                            $counter = 1;
                        @endphp
                        @foreach ($campus_stats as $stat_record)
                            <tr>
                                <td class="border-left">{{$counter++}}</td>
                                <td class="border-left">{{$stat_record['program']}}</td>
                                <td class="border-left border-right">{{$stat_record['admitted_students']}}</td>
                                <td class="border-left border-right">{{$stat_record['size']}}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('script')
    <script>
        let printStats = ()=>{
            let printContent = $('#print-box');
            let originalDoc = $(document.body).html();
            $(document.body).html(printContent);
            window.print();
            $(document.body).html(originalDoc);
        }
    </script>
@endsection