@extends('layouts.app')

@section('content')
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">



        <h4 class="py-3 mb-2">
            <span class="text-muted fw-light">The Log Feller /</span> Order Details
        </h4>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">

            <div class="d-flex flex-column justify-content-center gap-2 gap-sm-0">
                <h5 class="mb-1 mt-3 d-flex flex-wrap gap-2 align-items-end">Order #{{$order->order_id}} 
                    <span class="badge bg-label-success">Paid</span> <span class="badge bg-label-info">Ready to Pickup</span>
                </h5>
                <p class="text-body">Aug 17, <span id="orderYear"></span>, 5:48 (ET)</p>
            </div>
            <div class="d-flex align-content-center flex-wrap gap-2">
                <button class="btn btn-label-danger delete-order">Delete Order</button>
            </div>
        </div>

        <!-- Order Details Table -->

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title m-0">Order details</h5>
                        <h6 class="m-0"><a href=" javascript:void(0)">Edit</a></h6>
                    </div>
                    <?php
                    $subTotalAmount = 0.00;
                    ?>
                    <div class="card-datatable table-responsive">
                        {{-- <table class="datatables-order-details table border-top">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th class="w-50">products</th>
                                    <th class="w-25">payment method</th>
                                    <th class="w-25">price</th>
                                    <th class="w-25">qty</th>
                                    <th>total</th>
                                    <th>action</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @if($orderItems)
                                @foreach($orderItems as $items)
                                    <tr>
                                        <td><i class="ti ti-brand-angular ti-lg text-danger me-3"></i> <span class="fw-medium">Angular Project</span></td>
                                        <td>Albert Cook</td>
                                        <td>
                                            <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
                                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Lilian Fuller">
                                                <img src="{{$items->product->image_path}}" alt="Avatar" class="rounded-circle">
                                            </li>
                                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Sophia Wilkerson">
                                                <img src="../../assets/img/avatars/6.png" alt="Avatar" class="rounded-circle">
                                            </li>
                                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Christina Parker">
                                                <img src="../../assets/img/avatars/7.png" alt="Avatar" class="rounded-circle">
                                            </li>
                                            </ul>
                                        </td>
                                        <td><span class="badge bg-label-primary me-1">Active</span></td>
                                        <td>
                                            <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="javascript:void(0);"><i class="ti ti-pencil me-1"></i> Edit</a>
                                                <a class="dropdown-item" href="javascript:void(0);"><i class="ti ti-trash me-1"></i> Delete</a>
                                            </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table> --}}
                        <table class="datatables-order-details table border-top">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th class="w-50">Products</th>
                                    <th class="w-25">Payment Method</th>
                                    <th class="w-25">Price</th>
                                    <th class="w-25">Qty</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @if($orderItems)
                                    @foreach($orderItems as $items)
                                        <tr>
                                            <td>
                                                <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
                                                    @if(optional($items->product)->images)
                                                        @foreach($items->product->images as $image)
                                                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="{{ $items->product->name }}">
                                                                <img src="{{ asset($image->image_path) }}" alt="Product Image" class="rounded-circle">
                                                            </li>
                                                        @endforeach
                                                    @else
                                                        <li>No Images</li>
                                                    @endif
                                                </ul>
                                            </td>
                                            <td>
                                                <span class="fw-medium">{{ optional($items->product)->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>{{ $order->payment_type ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-label-primary me-1">£{{ number_format($items->sale_price, 2) }}</span>
                                            </td>
                                            <td>{{ $items->quantity }}</td>
                                            <td>£{{ number_format($items->quantity * $items->sale_price, 2) }}</td>
                                            <?php
                                                $subTotalAmount = $subTotalAmount + ($items->quantity * $items->sale_price);
                                            ?>
                                            <td>
                                                <a class="action-class view-access editIcon" data-url="{{ URL::to('/') }}/orders/items/view/{{ $items->id }}" href="#" onclick="viewOrderDetailModal(this)" id="view_{{ $items->id }}">
                                                    <span class="badge bg-label-success"><i class="far fa-eye" aria-hidden="true"></i></span>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>

                        <hr>
                        <div class="d-flex justify-content-end align-items-center m-3 mb-2 p-1">
                            <div class="order-calculations">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="w-px-100 text-heading">Total:</span>
                                    <h6 class="mb-0">£{{$subTotalAmount}}</h6>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="w-px-100 text-heading">Discount:</span>
                                    <h6 class="mb-0">£{{($order->discount_amount) ? $order->discount_amount : 0}}</h6>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <h6 class="w-px-100 mb-0">Subtotal:</h6>
                                    <h6 class="mb-0">£{{$subTotalAmount-$order->discount_amount}}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title m-0">Shipping activity</h5>
                    </div>
                    <div class="card-body">
                        <ul class="timeline pb-0 mb-0">
                            <li class="timeline-item timeline-item-transparent border-primary">
                                <span class="timeline-point timeline-point-primary"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header">
                                        <h6 class="mb-0">Order was placed (Order ID: #32543)</h6>
                                        <span class="text-muted">Tuesday 11:29 AM</span>
                                    </div>
                                    <p class="mt-2">Your order has been placed successfully</p>
                                </div>
                            </li>
                            <li class="timeline-item timeline-item-transparent border-primary">
                                <span class="timeline-point timeline-point-primary"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header">
                                        <h6 class="mb-0">Pick-up</h6>
                                        <span class="text-muted">Wednesday 11:29 AM</span>
                                    </div>
                                    <p class="mt-2">Pick-up scheduled with courier</p>
                                </div>
                            </li>
                            <li class="timeline-item timeline-item-transparent border-primary">
                                <span class="timeline-point timeline-point-primary"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header">
                                        <h6 class="mb-0">Dispatched</h6>
                                        <span class="text-muted">Thursday 11:29 AM</span>
                                    </div>
                                    <p class="mt-2">Item has been picked up by courier</p>
                                </div>
                            </li>
                            <li class="timeline-item timeline-item-transparent border-primary">
                                <span class="timeline-point timeline-point-primary"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header">
                                        <h6 class="mb-0">Package arrived</h6>
                                        <span class="text-muted">Saturday 15:20 AM</span>
                                    </div>
                                    <p class="mt-2">Package arrived at an Amazon facility, NY</p>
                                </div>
                            </li>
                            <li class="timeline-item timeline-item-transparent border-left-dashed">
                                <span class="timeline-point timeline-point-primary"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header">
                                        <h6 class="mb-0">Dispatched for delivery</h6>
                                        <span class="text-muted">Today 14:12 PM</span>
                                    </div>
                                    <p class="mt-2">Package has left an Amazon facility, NY</p>
                                </div>
                            </li>
                            <li class="timeline-item timeline-item-transparent border-transparent pb-0">
                                <span class="timeline-point timeline-point-secondary"></span>
                                <div class="timeline-event pb-0">
                                    <div class="timeline-header">
                                        <h6 class="mb-0">Delivery</h6>
                                    </div>
                                    <p class="mt-2 mb-0">Package will be delivered by tomorrow</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div> --}}
            </div>
            <div class="col-12 col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title m-0">Customer details</h6>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-start align-items-center mb-4">
                            <div class="avatar me-2">
                                <img src="{{url($customer->image)}}" alt="Avatar" class="rounded-circle">
                            </div>
                            <div class="d-flex flex-column">
                                <a href="app-user-view-account.html" class="text-body text-nowrap">
                                    <h6 class="mb-0">{{$shippings->name}}</h6>
                                </a>
                                {{-- <small class="text-muted">Customer ID: #58909</small> --}}
                            </div>
                        </div>
                        <div class="d-flex justify-content-start align-items-center mb-4">
                            <span
                                class="avatar rounded-circle bg-label-success me-2 d-flex align-items-center justify-content-center"><i
                                    class='ti ti-shopping-cart ti-sm'></i></span>
                            <h6 class="text-body text-nowrap mb-0">{{$orderCount}} Orders</h6>
                        </div>
                        <div class="d-flex justify-content-between">
                            <h6>Contact info</h6>
                            {{-- <h6><a href=" javascript:void(0)" data-bs-toggle="modal" data-bs-target="#editUser">Edit</a></h6> --}}
                        </div>
                        <hr>
                        <p class=" mb-1">Email: {{$customer->email}}</p>
                        <p class=" mb-0">Mobile: {{$shippings->phone_code}} {{$shippings->mobile}}</p>
                    </div>
                </div>

                <div class="card mb-4">

                    <div class="card-header d-flex justify-content-between">
                        <h6 class="card-title m-0">Shipping address</h6>
                        {{-- <h6 class="m-0"><a href=" javascript:void(0)" data-bs-toggle="modal" data-bs-target="#addNewAddress">Edit</a></h6> --}}
                    </div>
                    <hr>
                    <div class="card-body">
                        <p class="mb-0">{{$shippings->street}} <br>{{$shippings->landmark}} <br>{{$shippings->city}}, {{$shippings->code}}<br>{{$shippings->state}}</p>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- / Content -->

        <div class="col-lg-4 col-md-6">
            <!-- Extra Large Modal -->
            <div class="modal fade" id="viewOrderDetailModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-simple" role="document">
                    <div class="modal-content p-3 p-md-5">
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
