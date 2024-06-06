<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Products;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class ProductAuthController extends Controller
{
    private $user_id;
    
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['addToWishlist', 'removefromWishlist', 'fetchWishlistProducts']]);
        $this->middleware(function ($request, $next) {
            $checkToken = $this->invoke();
            if ($checkToken) {
                $this->user_id = $checkToken;
                return $next($request);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Authorization Failed'], 401);
            }
        });
    }

    public function addToWishlist(Request $request)
    {
        try {
            $inputs = $request->all();
            $validatedData = Validator::make($inputs, [
                'productId' => 'integer|required',
            ]);

            if ($validatedData->fails()) {
                $errors = $validatedData->errors();

                $transformed = [];
                foreach ($errors->all() as $message) {
                    $transformed[] = $message;
                }
                return response()->json(['status' => 'failed', 'message' => $transformed], 422);
            }

            $userId = $this->user_id;
            $productId = $inputs['productId'];
            $wishlistExist = Wishlist::where('product_id', $productId)->where('user_id', $userId)->first();
            if (!empty($wishlistExist)) {
                return response()->json(['status' => 'success', 'message' => 'Product already added to wishlist', 'data' => array()], 200);
            } else {
                $wishlist = Wishlist::create(
                    [
                        'user_id' => $userId,
                        'product_id' => $productId,
                    ]
                );
                if ($wishlist) {
                    return response()->json(['status' => 'success', 'message' => 'Product added to wishlist successfully', 'data' => array()], 200);
                } else {
                    return response()->json(['status' => 'failed', 'message' => 'Something went wrong please try again', 'data' => array()], 200);
                }
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function removefromWishlist(Request $request)
    {
        try {
            $inputs = $request->all();
            $validatedData = Validator::make($inputs, [
                'productId' => 'integer|required',
            ]);

            if ($validatedData->fails()) {
                $errors = $validatedData->errors();

                $transformed = [];
                foreach ($errors->all() as $message) {
                    $transformed[] = $message;
                }
                return response()->json(['status' => 'failed', 'message' => $transformed], 422);
            }
            $userId = $this->user_id;
            $productId = $inputs['productId'];
            if (Wishlist::where('product_id', $productId)->where('user_id', $userId)->forceDelete()) {
                return response()->json(['status' => 'success', 'message' => 'Product removed from wishlist', 'data' => array()], 200);
            } else {
                return response()->json(['status' => 'success', 'failed' => 'Something went wrong please try again', 'data' => array()], 200);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function fetchWishlistProducts(Request $request)
    {
        try {
            $userId = $this->user_id;
            $wishlistProArr = [];

            $totalProducts = Wishlist::where('user_id', $userId)
                ->whereNull('deleted_at')
                ->count();

            // Fetch wishlist products along with product details, images, and variants
            $wishlistProducts = Wishlist::with(['product' => function ($query) {
                $query->with(['images', 'variants']);
            }])->where('user_id', $userId)->get();

            foreach ($wishlistProducts as $list) {
                $product = $list->product;
                if ($product) {
                    $wishlistProArr[] = [
                        "id" => $list->id,
                        "productId" => $product->id,
                        "name" => $product->name,
                        "slug" => $product->slug,
                        "price" => $product->sale_price,
                        "description" => $product->description,
                        // "featured_image" => $product->image_path ? asset($product->image_path) : '',
                        "images" => $product->images->map(function ($image) {
                            return asset($image->image_path);
                        }),
                        "variants" => $product->has_variant ? $product->variants : []
                    ];
                }
            }

            return response()->json(['status' => 'success', 'message' => '', 'totalProducts' => $totalProducts,  'data' => $wishlistProArr], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function invoke()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return false;
            }
        } catch (JWTException $e) {
            return false;
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return false;
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return false;
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return false;
        }
        return $user->id;
    }
}
