@extends('admin.layout')

@section('section')
    <div class="col-sm-12">
        <p class="text-muted">
            <a href="{{route('admin.users.create')}}?type={{$type}}" class="btn btn-info btn-xs">Add {{request('type')}}</a>
        </p>

        <div class="content-panel">
            <div class="adv-table table-responsive">
                <table cellpadding="0" cellspacing="0" border="0" class="table" id="hidden-table-info">
                    <thead>
                    <tr class="text-capitalize">
                        <th>#</th>
                        <th>{{__('text.word_name')}}</th>
                        <th>{{__('text.word_email')}}</th>
                        <th>{{__('text.word_phone')}}</th>
                        <th>{{__('text.word_matricule')}}</th>
                        <th>{{__('text.word_campus')}}</th>
                        <th>{{__('text.word_gender')}}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                        @php($k = 1)
                        @foreach($users as $user)
                            @if((auth()->user()->campus_id == null) || ($user->campus_id == auth()->user()->campus_id))
                            <tr>
                                <td>{{$k+1}}</td>
                                <td>{{$user->name}}</td>
                                <td>{{$user->email}}</td>
                                <td>{{$user->phone}}</td>
                                <td>{{$user->matric}}</td>
                                <td>{{!($user->campus_id == null) ? $campuses->where('id', $user->campus_id)->first()->name ?? '' : ''}}</td>
                                <td>{{$user->gender}}</td>
                                <td  class="d-flex justify-content-end align-items-center" >
                                    <a class="btn btn-xs btn-primary" href="{{route('admin.users.show',[$user->id])}}"><i class="fa fa-eye"> Profile</i></a> |
                                    <a class="btn btn-xs btn-success" href="{{route('admin.users.edit',[$user->id])}}"><i class="fa fa-edit"> Edit</i></a> |
                                    <a onclick="event.preventDefault();
                                            confirm(`You are about to delete {{$user->type}}; {{$user->name}}`) ? document.getElementById('delete{{$user->id}}').submit() : null;" class=" btn btn-danger btn-xs m-2">Delete</a>
                                    <form id="delete{{$user->id}}" action="{{route('admin.users.destroy',$user->id)}}" method="POST" style="display: none;">
                                        @method('DELETE')
                                        {{ csrf_field() }}
                                    </form>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-end">
                </div>
            </div>
        </div>
    </div>
@endsection
