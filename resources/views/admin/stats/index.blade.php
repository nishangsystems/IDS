@extends('admin.layout')
@section('section')
    <div class="py-4 container-fluid">
        <table class="table">
            <thead class="text-capitalize text-primary">
                <th>@lang('text.sn')</th>
                <th>@lang('text.word_campus')</th>
                <th>@lang('text.word_count')</th>
            </thead>
            <tbody>
                @foreach ($stats->groupBy('campus') as $campus => $campus_stats)
                    <tr class="">
                        <th class="text-dark h4 text-capitalize border border-black" colspan="3">{!! $campus !!}</th>
                    </tr>
                    @php
                        $counter = 1;
                    @endphp
                    @foreach ($campus_stats as $stat_record)
                        <tr>
                            <td class="border-left">{{$counter++}}</td>
                            <td class="border-left">{{$stat_record->program}}</td>
                            <td class="border-left border-right">{{$stat_record->size}}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
@endsection