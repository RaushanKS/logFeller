@extends('layouts.app')

@section('content')

    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Orders /</span> All Orders
    </h4>

        {{-- <div class="demo-inline-spacing mb-4">
            <a href="{{ url('/testimonials/create') }}" class="btn btn-primary btn-lg"><span class="fas fa-plus m-2"></span>Add Testimonials</a>
        </div> --}}

<!-- Select -->

    <div class="card">
    <h5 class="card-header"></h5>
    <div class="card-datatable dataTable_select text-nowrap table-responsive">
        <table id="ordersTables" class="table">
        <thead>
            <tr>
                <th></th>
                <th>Order Number</th>
                <th>Customer Name</th>
                <th>Total Amount</th>
                <th>Pay Amount</th>
                <th>Discount Amount</th>
                <th>Shipping Amount</th>
                {{-- <th>Payment Method</th> --}}
                <th>Created At</th>
                <th>Status</th>
                <th>Total Items</th>
                <th>Action</th>
            </tr>
        </thead>
        </table>
    </div>
    </div>

<!--/ Select -->





@endsection