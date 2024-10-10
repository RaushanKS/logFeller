@extends('layouts.app')

@section('content')

    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Products /</span> Products list
    </h4>

<!-- Card Border Shadow -->

    <div class="row">
        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="card card-border-shadow-primary h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                <div class="avatar me-2">
                    <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-git-fork ti-md"></i></span>
                </div>
                <h4 class="ms-1 mb-0">{{$totalRecords}}</h4>
                </div>
                <p class="mb-1">Total Products</p>
                {{-- <p class="mb-0">
                <span class="fw-medium me-1">+18.2%</span>
                <small class="text-muted">than last week</small>
                </p> --}}
            </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="card card-border-shadow-success h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                <div class="avatar me-2">
                    <span class="avatar-initial rounded bg-label-success"><i class='ti ti-check ti-md'></i></span>
                </div>
                <h4 class="ms-1 mb-0">{{$activated}}</h4>
                </div>
                <p class="mb-1">Active Products</p>
                {{-- <p class="mb-0">
                <span class="fw-medium me-1">-8.7%</span>
                <small class="text-muted">than last week</small>
                </p> --}}
            </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="card card-border-shadow-danger h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                <div class="avatar me-2">
                    <span class="avatar-initial rounded bg-label-danger"><i class='ti ti-alert-triangle ti-md'></i></span>
                </div>
                <h4 class="ms-1 mb-0">{{$inactivated}}</h4>
                </div>
                <p class="mb-1">Inactive Products</p>
                {{-- <p class="mb-0">
                <span class="fw-medium me-1">+4.3%</span>
                <small class="text-muted">than last week</small>
                </p> --}}
            </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="card card-border-shadow-info h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 pb-1">
                <div class="avatar me-2">
                    <span class="avatar-initial rounded bg-label-info"><i class='ti ti-clock ti-md'></i></span>
                </div>
                <h4 class="ms-1 mb-0">{{$recentProducts}}</h4>
                </div>
                <p class="mb-1">Recently Added</p>
                {{-- <p class="mb-0">
                <span class="fw-medium me-1">-2.5%</span>
                <small class="text-muted">than last week</small>
                </p> --}}
            </div>
            </div>
        </div>
    </div>

<!--/ Card Border Shadow -->

        <div class="demo-inline-spacing mb-4">
            <a href="{{ url('/products/create') }}" class="btn btn-primary btn-lg"><span class="fas fa-plus m-2"></span>Add Products</a>
            {{-- <button type="button" class="btn btn-primary btn-lg">Add Product</button> --}}
        </div>

<!-- Select -->

    <div class="card">
    <h5 class="card-header"></h5>
    <div class="card-datatable dataTable_select text-nowrap table-responsive">
        <table id="productsTable" class="table">
        <thead>
            <tr>
                <th></th>
                <th>Image</th>
                <th>Product Name</th>
                <th>Price (in £)</th>
                <th>Description</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        </table>
    </div>
    </div>

<!--/ Select this is given in bu -->





@endsection