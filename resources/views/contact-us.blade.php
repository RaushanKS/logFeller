@extends('layouts.app')

@section('content')

    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Enquiry /</span> All Enquiries
    </h4>

        {{-- <div class="demo-inline-spacing mb-4">
            <a href="{{ url('/testimonials/create') }}" class="btn btn-primary btn-lg"><span class="fas fa-plus m-2"></span>Add Testimonials</a>
        </div> --}}

<!-- Select -->

    <div class="card">
    <h5 class="card-header"></h5>
    <div class="card-datatable dataTable_select text-nowrap table-responsive">
        <table id="enquiryTables" class="table">
        <thead>
            <tr>
                <th></th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Action</th>
            </tr>
        </thead>
        </table>
    </div>
    </div>

<!--/ Select -->

    <div class="col-lg-4 col-md-6">
        <!-- Extra Large Modal -->
        <div class="modal fade" id="viewContactModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
              <div class="modal-header">
                {{-- <h5 class="modal-title" id="exampleModalLabel4">Enquiry Details</h5> --}}
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                
              </div>
            </div>
          </div>
        </div>
    </div>





@endsection