@extends('layouts.app')

@section('content')

    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Users /</span> All Users
    </h4>

        {{-- <div class="demo-inline-spacing mb-4">
            <a href="{{ url('/testimonials/create') }}" class="btn btn-primary btn-lg"><span class="fas fa-plus m-2"></span>Add Testimonials</a>
        </div> --}}

<!-- Select -->

    <div class="card">
    <h5 class="card-header"></h5>
    <div class="card-datatable dataTable_select text-nowrap table-responsive">
        <table id="userTable" class="table">
        <thead>
            <tr>
                <th></th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Registered On</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        </table>
    </div>
    </div>

<!--/ Select -->





@endsection