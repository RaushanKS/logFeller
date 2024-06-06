@extends('layouts.app')

@section('content')

    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Discount Coupon /</span> Edit Discount Coupon
    </h4>


    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Discount Coupon</h5>
                </div>
                <hr>

                <div class="card-body">
                    <form action="{{ url('/coupon/update') }}/{{$discounts->id}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="col-form-label" for="coupon_title">Coupon Title</label>
                            <input name="coupon_title" type="text" class="form-control" id="coupon_title" value="{{ $discounts->name }}" placeholder="Enter title" />
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="select2Basic" class="col-form-label">Discount Type</label>
                                <select id="discount_type" name="discount_type" class="select2 form-select" onchange="discountType(this.id);" required="true" data-allow-clear="true">
                                    <option value="fixed" @if($discounts->discount_type=='fixed') selected @endif>Fixed</option>
                                    <option value="percentage" @if($discounts->discount_type=='percentage') selected @endif>Percentage</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="fixedDiscount" @if($discounts->discount_type=='percentage') style="display:none;" @endif>
                                <label class="col-form-label" for="discount_amount">Discount Amount (£)</label>
                                <input name="discount_amount" type="text" class="form-control" id="discount_amount" value="{{ $discounts->discount_amount }}" onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode &gt;= 48 && event.charCode &lt;= 57" placeholder="Enter discount amount here..." />
                            </div>
                            <div class="col-md-6" id="percentageDiscount" @if($discounts->discount_type=='fixed') style="display:none;" @endif>
                                <label class="col-form-label" for="discount_percent">Discount Percentage (%)</label>
                                <input type="text" class="form-control" name="discount_percent" id="discount_percent" value="{{ $discounts->discount_percent }}" onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode &gt;= 48 && event.charCode &lt;= 57" placeholder="Enter discount percent here..." />
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="col-form-label" for="max_discount">Max Discount Amount (£)</label>
                                <input name="max_discount" type="text" class="form-control" id="max_discount" value="{{ $discounts->max_discount }}" onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode &gt;= 48 && event.charCode &lt;= 57" placeholder="00" />
                            </div>
                            <div class="col-md-6">
                                <label class="col-form-label" for="min_order_amount">Min Order Amount (£)</label>
                                <input name="min_order_amount" type="text" class="form-control" id="min_order_amount" value="{{ $discounts->min_order_amount }}" onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode &gt;= 48 && event.charCode &lt;= 57" placeholder="00" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col-md-6">
                                <?php $today = date('Y-m-d');?>
                                <label for="html5-date-input" class="col-form-label">Start Date</label>
                                <input class="form-control" type="date" name="start_date" min="<?php echo $today; ?>" value="{{$discounts->start_date}}" id="html5-date-input" />
                            </div>
                            <div class="col-md-6">
                                <?php $today = date('Y-m-d');?>
                                <label for="html5-date-input" class="col-form-label">End Date</label>
                                <input class="form-control" type="date" name="end_date" min="<?php echo $today; ?>" value="{{$discounts->end_date}}" id="html5-date-input" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="col-form-label" for="coupon_description">Description</label>
                            <textarea name="coupon_description" id="coupon_description" class="form-control" placeholder="Enter Coupon description">{{$discounts->description}}</textarea>
                        </div>
                        
                        <hr>
                        <button type="submit" class="btn btn-primary"  style="float: right;">Update</button>
                    </form>

                </div>
            </div>
        </div>
    </div>




@endsection