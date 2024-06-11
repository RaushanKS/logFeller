<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Products;
use App\Models\Wishlist;
use App\Models\ContactUs;
use App\Models\ProductImage;
use App\Models\Testimonials;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function fetchProducts(Request $request)
    {
        try {
            $inputs = $request->all();
            $productArr = [];
            $currencySymbol = '£';

            $totalProducts = Products::where('status', 1)
                ->whereNull('deleted_at')
                ->count();

            // Fetch products with images and variants 
            $products = Products::where('status', 1)
                ->whereNull('deleted_at')
                ->with(['images', 'variants'])
                // ->take(10)
                ->get(['id', 'name', 'slug', 'description', 'sale_price', 'has_variant']);

            foreach ($products as $product) {
                $wishlist = 'no';
                if (isset($inputs['uid']) && !empty($inputs['uid'])) {
                    $wishlistExist = Wishlist::where('product_id', $product->id)->where('user_id', $inputs['uid'])->first();
                    if ($wishlistExist) {
                        $wishlist = 'yes';
                    }
                }

                if ($product->has_variant && $product->variants->isNotEmpty()) {
                    $variantPrices = $product->variants->pluck('sale_price');
                    $minPrice = $variantPrices->min();
                    $maxPrice = $variantPrices->max();
                    $price = "{$currencySymbol}{$minPrice} - {$currencySymbol}{$maxPrice}";
                } else {
                    $price = "{$currencySymbol}{$product->sale_price}";
                }

                $productData = [
                    "id" => $product->id,
                    "name" => $product->name,
                    "slug" => $product->slug,
                    "price" => $price,
                    "description" => $product->description,
                    "wishlist" => $wishlist,
                    "images" => $product->images->map(function ($image) {
                        return url($image->image_path);
                    }),
                    "variants" => $product->has_variant ? $product->variants : []
                ];

                $productArr[] = $productData;
            }

            return response()->json(['status' => 'success', 'message' => 'Record Found','totalProducts' => $totalProducts, 'data' => ['homeProduct' => $productArr]], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function singleProduct(Request $request)
    {
        try {
            $inputs = $request->all();
            $productId = $inputs['productId'];
            $productArr = array();
            $currencySymbol = '£';
            $products = Products::select('id', 'name', 'slug', 'sale_price', 'status', 'has_variant', 'description', 'seo_title', 'seo_description')->where('id', $productId)->with(['images', 'variants'])->first();

            $wishlist = 'no';
            if (isset($inputs['uid']) && !empty($inputs['uid'])) {
                $wishlistExist = Wishlist::where('product_id', $products->id)->where('user_id', $inputs['uid'])->first();
                if ($wishlistExist) {
                    $wishlist = 'yes';
                }
            }

            if ($products->has_variant && $products->variants->isNotEmpty()) {
                $variantPrices = $products->variants->pluck('sale_price');
                $minPrice = $variantPrices->min();
                $maxPrice = $variantPrices->max();
                $price = "{$currencySymbol}{$minPrice} - {$currencySymbol}{$maxPrice}";
            } else {
                $price = "{$currencySymbol}{$products->sale_price}";
            }

            if ($products) {
                $wishlistArr = array();
                $productArr[] = array(
                    "id" => $products['id'],
                    "name" => $products['name'],
                    "slug" => $products['slug'],
                    "description" => $products['description'],
                    "sale_price" => $price,
                    "wishlist" => $wishlist,
                    "images" => $products->images->map(function ($image) {
                        return url($image->image_path);
                    }),
                    "variants" => $products->has_variant ? $products->variants : []
                );
            }
            return response()->json(['status' => 'success', 'message' => 'Record Found', 'data' => array('length' => 1, 'product' => $productArr)], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function fetchTestimonials(Request $request)
    {
        try {
            $inputs = $request->all();
            $testimonialsArr = array();

            $totalTestimonials = Testimonials::where('status', 1)
                ->whereNull('deleted_at')
                ->count();

            $testimonials = Testimonials::where('status', 1)->whereNull('deleted_at')->get();

            foreach($testimonials as $testimonial) {
                $testimonialData = [
                    "id" => $testimonial->id,
                    "name" => $testimonial->name,
                    "ratings" => $testimonial->ratings,
                    "message" => $testimonial->message,
                    "featured_image" => $testimonial->image ? url($testimonial->image) : '',

                ];

                $testimonialsArr[] = $testimonialData;
            }
            return response()->json(['status' => 'success', 'message' => 'Record Found', 'totalTestimonials' => $totalTestimonials, 'data' => ['testimonials' => $testimonialsArr]], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function storeContactUs(Request $request)
    {
        try {
            $data = $request->all();

            $validatedData = Validator::make($data, [
                'fullName' => 'required',
                'email' => 'required',
                'phone' => 'required',
                'message' => 'string|required',
                'subject' => 'required',
            ]);

            if ($validatedData->fails()) {
                $errors = $validatedData->errors();

                $transformed = [];
                foreach ($errors->all() as $message) {
                    $transformed[] = $message;
                }
                return response()->json(['status' => 'failed', 'message' => $transformed], 422);
            }

            $quotation = ContactUs::create([
                'name' => $data['fullName'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'subject' => $data['subject'],
                'message' => $data['message'],
            ]);


            if ($quotation) {
                return response()->json(['status' => 'success', 'message' => 'Enquiry submitted Successfully.', 'data' => array()], 200);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Something went wrong please try again', 'data' => array()], 200);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

}
