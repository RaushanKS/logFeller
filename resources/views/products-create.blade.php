@extends('layouts.app')

@section('content')

    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Product /</span> Add Product
    </h4>


    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Add Product</h5>
                </div>

                <div class="card-body">
                    {{-- <form action="{{ url('/products/store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">Product Name</label>
                            <input name="product_name" type="text" class="form-control" id="basic-default-fullname" placeholder="Bag of Logs" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="basic-default-company">Product Price</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">£</span>
                                <input name="product_price" type="number" class="form-control" placeholder="100" aria-label="Amount (to the nearest GBP)" />
                                <span class="input-group-text">.00</span>
                            </div>

                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="basic-default-email">Product Image (s)</label>
                            <div class="input-group">
                                <label class="input-group-text" for="inputGroupFile01">Upload</label>
                                <input name="product_image" type="file" class="form-control" id="inputGroupFile01" multiple>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="basic-default-message">Product Description</label>
                            <textarea name="product_description" id="basic-default-message" class="form-control" placeholder="Hi, Do you have a moment to talk Joe?"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form> --}}

                    

                    <form action="{{ url('/products/store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="col-form-label" for="product_name">Product Name</label>
                            <input name="product_name" type="text" class="form-control" id="product_name" placeholder="Bag of Logs" />
                        </div>
                        <div class="mb-3">
                            <label class="col-form-label" for="product_price">Product Price</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">£</span>
                                <input name="product_price" type="text" class="form-control" onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode &gt;= 48 && event.charCode &lt;= 57" placeholder="100" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="col-form-label" for="product_image">Product Image(s)</label>
                            <div class="input-group">
                                <label class="input-group-text" for="product_image">Upload</label>
                                <input name="product_image[]" type="file" class="form-control" id="product_image" accept="image/png, image/jpeg, image/jpg, image/svg, image/gif" required multiple>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="col-form-label" for="product_description">Product Description</label>
                            <textarea name="product_description" id="product_description" class="form-control" placeholder="Hi, Do you have a moment to talk Joe?"></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md">
                                <small class="text-bold fw-medium d-block col-form-label">Variations</small>
                                <div class="form-check mt-3">
                                    <input name="has_variants" class="form-check-input" name="has_variants" type="checkbox" id="has_variants" onchange="toggleVariantsSection(this)" />
                                    <label class="form-check-label" for="has_variants">
                                        This product has variants
                                    </label>
                                </div>
                            </div>
                            <div class="col-md">
                                <small class="text-bold fw-medium d-block col-form-label">Status</small>
                                <div class="form-check form-check-inline mt-3">
                                    <input class="form-check-input" type="radio" name="product_status" id="inlineRadio1" value="1" />
                                    <label class="form-check-label" for="inlineRadio1">Active</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="product_status" id="inlineRadio2" value="0" />
                                    <label class="form-check-label" for="inlineRadio2">Inactive</label>
                                </div>
                            </div>
                            <div class="col-md">
                                <small class="text-bold fw-medium d-block col-form-label">Is Important ?</small>
                                <div class="form-check form-check-inline mt-3">
                                    <input class="form-check-input" type="radio" name="product_important" id="inlineRadio1" value="1" />
                                    <label class="form-check-label" for="inlineRadio1">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="product_important" id="inlineRadio2" value="0" />
                                    <label class="form-check-label" for="inlineRadio2">No</label>
                                </div>
                            </div>
                        </div>
                        <div id="variants-section" style="display:none;">
                            <h3>Variants</h3>
                            <div class="row variant">
                                <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                    <label class="col-form-label" for="variant_name">Variant Name</label>
                                    <input type="text" name="variant_name[]" class="form-control" placeholder="Variant Name" />
                                </div>
                                <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                    <label class="col-form-label" for="variant_price">Variant Price</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">£</span>
                                        <input type="text" name="variant_price[]" class="form-control" placeholder="100" onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode &gt;= 48 && event.charCode &lt;= 57" />
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="addVariant()">Add Another Variant</button>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-primary"  style="float: right;">Save</button>
                    </form>

                </div>
            </div>
        </div>
    </div>




@endsection