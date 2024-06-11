@extends('layouts.app')

@section('content')

    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Product /</span> Edit Product
    </h4>


    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Product</h5>
                </div>

                <div class="card-body">
                    <form action="{{ url('product/update') }}/{{ $product->id }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="col-form-label" for="product_name">Product Name</label>
                            <input name="product_name" type="text" class="form-control" value="{{ $product->name }}" id="product_name" placeholder="Bag of Logs" />
                        </div>
                        <div class="mb-3">
                            <label class="col-form-label" for="product_price">Product Price</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">£</span>
                                <input name="product_price" type="text" class="form-control" value="{{ $product->sale_price }}" onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode &gt;= 48 && event.charCode &lt;= 57" placeholder="100" />
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-form-label" for="product_image">Product Image(s)</label>
                            <div class="input-group">
                                <label class="input-group-text" for="product_image">Upload</label>
                                <input name="product_image[]" type="file" class="form-control" id="product_image" accept="image/png, image/jpeg, image/jpg, image/svg, image/gif" multiple>
                            </div>

                            <div class="rounded-2 img-wrap">
                                @if (!empty($productImages))
                                    @foreach ($productImages as $image)
                                        <input class="form-control" name="oldDisplay_image" type="hidden" value="{{ $image->image_path }}" />

                                        <div class="image-container" id="serviceImageRemove<?= $image['id'] ?>">
                                            <img class="editImg me-3" src="{{ asset($image->image_path) }}" alt="tutor image 1" />

                                            <a href="javascript:void(0);" class="serviceImageDelete"
                                                onclick="removeServiceImage(this.id)"
                                                id="serviceImageDelete<?= $image['id'] ?>"
                                                data-id="<?= $image['id'] ?>"
                                                data-src="<?= $image['image_path'] ?>"
                                                data-url="{{ URL::to('/') }}/product/image/delete"
                                                data-type="serviceImageRemove<?= $image['id'] ?>"
                                                data-name=""><i class="fa fa-trash deleteImage" aria-hidden="true"></i>
                                            </a>

                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="col-form-label" for="product_description">Product Description</label>
                            <textarea name="product_description" id="product_description" class="form-control" placeholder="Enter product description">{{ $product->description }}</textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md">
                                <small class="text-bold fw-medium d-block col-form-label">Variations</small>
                                <div class="form-check">
                                    <input name="has_variants" class="form-check-input" name="has_variants" type="checkbox" id="has_variants" onchange="toggleVariantsSection(this)" {{$product->has_variant == 1 ? 'checked' : ''}}/>
                                    <label class="form-check-label" for="has_variants">
                                        This product has variants
                                    </label>
                                </div>
                            </div>
                            <div class="col-md">
                                <small class="text-bold fw-medium d-block col-form-label">Status</small>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="product_status" id="inlineRadio1" value="1" {{$product->status == 1 ? 'checked' : ''}} />
                                    <label class="form-check-label" for="inlineRadio1">Active</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="product_status" id="inlineRadio2" value="0" {{$product->status == 0 ? 'checked' : ''}} />
                                    <label class="form-check-label" for="inlineRadio2">Inactive</label>
                                </div>
                            </div>
                            <div class="col-md">
                                <small class="text-bold fw-medium d-block col-form-label">Is Important ?</small>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="product_important" id="inlineRadio1" value="1" {{$product->is_important == 1 ? 'checked' : ''}} />
                                    <label class="form-check-label" for="inlineRadio1">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="product_important" id="inlineRadio2" value="0" {{$product->is_important == 0 ? 'checked' : ''}} />
                                    <label class="form-check-label" for="inlineRadio2">No</label>
                                </div>
                            </div>
                        </div>
                        <div id="variants-section" style="display: {{$product->has_variant == 1 ? 'block' : 'none'}};">
                            @foreach ($productVariant as $index=>$variant)
                            <div class="row variant">
                                <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                    <label class="col-form-label" for="variant_name">Variant Name</label>
                                    <input type="text" name="variant_name[]" class="form-control" value="{{ $variant->name }}" placeholder="Variant Name" />
                                </div>
                                <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                    <label class="col-form-label" for="variant_price">Variant Price</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">£</span>
                                        <input type="text" name="variant_price[]" class="form-control" value="{{ $variant->sale_price }}" placeholder="100" onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode >= 48 && event.charCode <= 57" />
                                    </div>
                                </div>
                                <div class="mb-3 col-lg-12 col-xl-12 col-12 mb-0">
                                    <button type="button" class="btn btn-danger" onclick="removeVariant(this)">Remove Variant</button>
                                    {{-- @if($index == 0)
                                        <button type="button" class="btn btn-primary" onclick="addVariant()">Add Another Variant</button>
                                    @else
                                        <button type="button" class="btn btn-danger" onclick="removeVariant(this)">Remove Variant</button>
                                    @endif --}}
                                </div>
                            </div>
                            @endforeach
                            <div class="mb-3 col-lg-12 col-xl-12 col-12 mb-0">
                                <button type="button" class="btn btn-primary" onclick="addVariant()">Add Variant</button>
                            </div>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-primary" style="float: right;">Update</button>
                    </form>

                </div>
            </div>
        </div>
    </div>




@endsection