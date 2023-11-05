@extends('student.layout')
@section('section')
    <div class="container">
        <div class="d-flex justify-content-end">
            <img id="preview_img" class="img-responsive" style="width: 12rem; height: 12rem; border-radius: 0.6rem;">
        </div>
        <form class="container form" method="POST" action="{{ route('student.add_image') }}">
            @csrf
            <div class="my">
                <h1>Upload Image</h1>
                <input type="file" name="image" class="img form-control" onchange="preview(event)">
            </div>
            <div class="d-flex justify-contet-end">
                <input class="btn btn-primary btn-sm" type="submit">
            </div>
        </form>
    </div>
    
    {{-- <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Laravel Cropper Js - Crop Image Before Upload - Tutsmake.com</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="img-container">
                        <div class="row">
                            <div class="col-md-8">
                                <img id="image" src="https://avatars0.githubusercontent.com/u/3456749">
                            </div>
                            <div class="col-md-4">
                                <div class="preview"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="crop">Crop</button>
                </div>
            </div>
        </div>
    </div> --}}
@endsection
@section('script')
    <script>
    
        var $modal = $('#modal');
        var image = document.getElementById('image');
        var cropper;
    
        // $("body").on("change", ".image", function(e){
        //     var files = e.target.files;
        //     var done = function (url) {
        //         image.src = url;
        //         $modal.modal('show');
        //     };
        //     var reader;
        //     var file;
        //     var url;
        
        //     if (files && files.length > 0) {
        //     file = files[0];
        
        //     if (URL) {
        //         done(URL.createObjectURL(file));
        //     } else if (FileReader) {
        //         reader = new FileReader();
        //         reader.onload = function (e) {
        //         done(reader.result);
        //         };
        //         reader.readAsDataURL(file);
        //     }
        //     }
        // });
        
        // $modal.on('shown.bs.modal', function () {
        //     cropper = new Cropper(image, {
        //     aspectRatio: 1,
        //     viewMode: 3,
        //     preview: '.preview'
        //     });
        // }).on('hidden.bs.modal', function () {
        // cropper.destroy();
        // cropper = null;
        // });
        
        // $("#crop").click(function(){
        //     canvas = cropper.getCroppedCanvas({
        //         width: 160,
        //         height: 160,
        //     });
        
        //     canvas.toBlob(function(blob) {
        //         url = URL.createObjectURL(blob);
        //         var reader = new FileReader();
        //         reader.readAsDataURL(blob); 
        //         reader.onloadend = function() {
        //             var base64data = reader.result; 
        
        //             $.ajax({
        //                 // type: "POST",
        //                 method: "POST",
        //                 // dataType: "json",
        //                 url: "{{ route('student.add_image') }}",
        //                 data: {'_token': $('meta[name="_token"]').attr('content'), 'image': base64data},
        //                 success: function(data){
        //                     console.log(data);
        //                     $modal.modal('hide');
        //                     alert("Crop image successfully uploaded");
        //                     window.location = "{{ route('student.home') }}";
        //                 }
        //             });
        //         }
        //     });
        // })

        let preview = function(event){
            let file = event.target.files[0];
            let url = URL.createObjectURL(file);
            $('#preview_img').prop('src', url);
        }
    </script>
@endsection
