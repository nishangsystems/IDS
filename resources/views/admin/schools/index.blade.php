@extends('admin.layout')
@section('section')
    <div class="py-2">
        <table class="table">
            <thead class="border-bottom shadow-md">
                <th class="border-left border-right">#</th>
                <th class="border-left border-right">Name</th>
                <th class="border-left border-right">
                </th>
            </thead>
            <tbody>
                @php
                    $k = 1
                @endphp
                @foreach(\App\Models\School::all() as $school)
                    <tr class="border-bottom">
                        <td class="border-left border-right">{{ $k++ }}</td>
                        <td class="border-left border-right">{{ $school->name }}</td>
                        <td class="border-left border-right">
                            <a href="{{ route('admin.schools.edit', $school->id) }}" class="btn btn-dark btn-sm">edit</a>
                            <a href="{{ route('admin.schools.students', $school->id) }}" class="btn btn-primary btn-sm">students</a>
                            <a href="{{ route('admin.schools.download_students', $school->id) }}" class="btn btn-success btn-sm">download</a>
                            <a href="{{ route('admin.schools.students.import', $school->id) }}" class="btn btn-info btn-sm">import students</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection