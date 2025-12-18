@extends('admin.layout')
@section('section')
    <div class="container-fluid">
        <table class="table">
            <thead class="text-capitalize">
                <th>@lang('text.sn')</th>
                <th>@lang('text.word_name')</th>
                <th>@lang('text.word_matricule')</th>
                <th>@lang('text.word_phone')</th>
            </thead>
            <tbody>
                @php
                    $counter = 1;
                @endphp
                @foreach ($students as $student)
                    <tr>
                        <td>{{ $counter++ }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->matricule }}</td>
                        <td>{{ $student->phone }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection