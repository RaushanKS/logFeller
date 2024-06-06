@extends('layouts.app')

@section('content')

    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Testimonial /</span> Edit Testimonial
    </h4>


    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Testimonial</h5>
                </div>

                <div class="card-body">
                    <form action="{{ url('/testimonial/update') }}/{{ $testimonial->id }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="col-form-label" for="user_name">Name</label>
                            <input name="user_name" type="text" class="form-control" id="user_name" value="{{ $testimonial->name }}" placeholder="Enter User Name" />
                        </div>
                        <div class="mb-3">
                            <label class="col-form-label" for="user_rating">Ratings</label>
                            <div class="input-group input-group-merge">
                                <input name="user_rating" type="text" class="form-control" id="user_rating" value="{{ $testimonial->ratings }}" onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode &gt;= 48 && event.charCode &lt;= 57" min="1" max="5" placeholder="1 to 5" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="col-form-label" for="testimonials_image">Image</label>
                            <div class="input-group">
                                <label class="input-group-text" for="testimonials_image">Upload</label>
                                <input name="testimonials_image" type="file" class="form-control" id="testimonials_image" accept="image/png, image/jpeg, image/jpg, image/svg, image/gif">
                            </div>
                            <input class="form-control" name="oldTestimonials_image" type="hidden" value="{{ $testimonial->image }}" />
                            <img class=" mt-3" src="{{ asset($testimonial->image) }}" height="100px" style="margin-right: 10px">
                        </div>
                        <div class="mb-3">
                            <label class="col-form-label" for="testimonial_desc">Testimonials Message</label>
                            <textarea name="testimonial_desc" id="testimonial_desc" class="form-control" placeholder="Hi, Do you have a moment to talk Joe?" required>{{ $testimonial->message }}</textarea>
                        </div>
                        
                        <hr>
                        <button type="submit" class="btn btn-primary" id="submit_testimonial_btn" style="float: right;">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>




@endsection