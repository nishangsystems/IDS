@extends('admin.layout')
@section('section')
    <div class="container-fluid">
        @isset($student)
            <form method="post">
                @csrf
                <label for="" class="form-control rounded mt-4">{{ $student->matricule }} &Rang; {{ $student->name }} </label>
                <span class="text-capitalize">{{ trans_choice('text.word_student', 1) }}</span>
                <textarea rows="2" name="bypass_reason" class="form-control rounded mt-4"></textarea>
                <span class="text-capitalize">{{ trans_choice('text.bypass_reason', 1) }} (optional)</span>
                <div class="d-flex justify-content-end py-4">
                    <button type="submit" class="btn btn-sm btn-primary rounded text-capitalize">@lang('text.word_bypass')</button>
                </div>
            </form>
        @endisset
        <hr>

        <input type="search" name="" class="form-control rounded" id="" placeholder="search student by name or matricule" oninput="searchStudent(this)">
        <hr>
        <table class="table">
            <thead class="text-capitalize">
                <th>@lang('text.sn')</th>
                <th>@lang('text.word_name')</th>
                <th>@lang('text.word_matricule')</th>
                <th></th>
            </thead>
            <tbody id="listing_table"></tbody>
        </table>
    </div>
@endsection
@section('script')
    <script>
        let searchStudent = (elm) =>  {
            let str = $(elm).val();
            let url = "{{ route('search_students') }}";
            let data = { "key": str};
            $.ajax({method: "GET", url: url, data: data, success: response => {
                console.log(response);
                let dom = '';
                let counter = 1;
                response.forEach(item=>{
                    dom += `<tr>
                        <td>${counter++}</td>
                        <td>${item.name}</td>
                        <td>${item.matricule}</td>
                        <td>
                            <a class="btn btn-sm btn-primary rounded text-capitalize" href="{{ route('admin.card_payment.bypass', '__ID__') }}">bypass</a>
                        </td>
                    </tr>`.replace('__ID__', item.id);
                })

                $('#listing_table').html(dom);

            }
            })
        }
    </script>
@endsection