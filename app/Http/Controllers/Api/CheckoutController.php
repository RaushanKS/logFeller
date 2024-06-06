<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Cart;
use App\Models\Products;
use Illuminate\Support\Arr;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class CheckoutController extends Controller
{
    private $user_id;
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['addToCart', 'updateCart', 'removeFromCart', 'fetchCarts', 'orderCreate', 'paymentSuccess', 'orderUpdate', 'couponApply', 'couponRemove', 'orderList', 'orderItemsList', 'orderCancel']]);
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

    // public function addToCart(Request $request)
    // {
    //     try {
    //         $inputs = $request->all();
    //         $validatedData = Validator::make($inputs, [
    //             'product_id' => 'required|exists:products,id',
    //             'variant_id' => 'nullable|exists:product_variants,id',
    //             'quantity' => 'required|integer|min=1'
    //         ]);

    //         if ($validatedData->fails()) {
    //             $errors = $validatedData->errors();

    //             $transformed = [];
    //             foreach ($errors->all() as $message) {
    //                 $transformed[] = $message;
    //             }
    //             return response()->json(['status' => 'failed', 'message' => $transformed], 422);
    //         }

    //         $userId = $this->user_id;
    //         $productId = $inputs['productId'];
    //         $variationId = $inputs['variationId'];
    //         $quantity = $inputs['quantity'];

    //         $productsVariations = ProductVariant::where('product_id', $productId)->where('id', $variationId)->first();
    //         if (empty($productsVariations)) {
    //             return response()->json(['status' => 'failed', 'message' => 'This product combination not exist please select another'], 422);
    //         }

    //         $cartData = Cart::where('productId', $productId)->where('user_id', $userId)->where('variationData', $variationId)->first();
    //         if ($cartData) {
    //             // $qty = $productsVariations->quantity;
    //             // if ($qty < $cartData->quantity + $quantity) {
    //             //     return response()->json(['status' => 'failed', 'message' => 'Only for ' . $qty . ' items left'], 422);
    //             // }

    //             $cartData->quantity = $cartData->quantity + $quantity;
    //             $cartData->save();
    //             if ($cartData->save()) {
    //                 return response()->json(['status' => 'success', 'message' => 'Product added to cart successfully', 'data' => array()], 201);
    //             } else {
    //                 return response()->json(['status' => 'failed', 'message' => 'Something went wrong please try again', 'data' => array()], 422);
    //             }
    //         } else {
    //             // $qty = $productsVariations->quantity;
    //             // if ($qty < $quantity) {
    //             //     return response()->json(['status' => 'failed', 'message' => 'Only for ' . $qty . ' items left'], 422);
    //             // }

    //             $carts = Cart::create(
    //                 [
    //                     'user_id' => $userId,
    //                     'productId' => $productId,
    //                     'variationData' => $variationId,
    //                     'quantity' => $quantity,
    //                 ]
    //             );
    //             if ($carts) {
    //                 return response()->json(['status' => 'success', 'message' => 'Product added to cart successfully', 'data' => array()], 201);
    //             } else {
    //                 return response()->json(['status' => 'failed', 'message' => 'Something went wrong please try again', 'data' => array()], 422);
    //             }
    //         }
    //     } catch (Exception $e) {
    //         return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
    //     }
    // }

    public function addToCart(Request $request)
    {
        try {
            $inputs = $request->all();
            $validatedData = Validator::make($inputs, [
                'product_id' => 'required|exists:products,id',
                'variant_id' => 'nullable|exists:product_variants,id',
                'quantity' => 'required|integer|min:1'
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
            $productId = $inputs['product_id'];
            $variantId = Arr::get($inputs, 'variant_id'); // Use Arr::get to safely access 'variant_id'
            $quantity = $inputs['quantity'];

            // Fetch the product to check if it has variants
            $product = Products::find($productId);

            if ($product->has_variant == 1) {
                if (empty($variantId)) {
                    return response()->json(['status' => 'failed', 'message' => 'Variant ID is required for products with variants.'], 422);
                }

                $variant = ProductVariant::where('product_id', $productId)->where('id', $variantId)->first();

                if (empty($variant)) {
                    return response()->json(['status' => 'failed', 'message' => 'This product variant combination does not exist.'], 422);
                }

                $price = $variant->sale_price;
                $name = $product->name . ' - ' . $variant->name;
            } else {
                if (!empty($variantId)) {
                    return response()->json(['status' => 'failed', 'message' => 'Variant ID should not be provided for products without variants.'], 422);
                }

                $price = $product->sale_price;
                $name = $product->name;
            }

            $cartData = Cart::where('productId', $productId)
                ->where('user_id', $userId)
                ->where('variationData', $variantId)
                ->first();

            if ($cartData) {
                $cartData->quantity += $quantity;
                $cartData->total = $cartData->quantity * $price;

                if ($cartData->save()) {
                    return response()->json(['status' => 'success', 'message' => 'Product added to cart successfully', 'data' => $cartData], 201);
                } else {
                    return response()->json(['status' => 'failed', 'message' => 'Something went wrong, please try again.'], 422);
                }
            } else {
                $cart = Cart::create([
                    'user_id' => $userId,
                    'productId' => $productId,
                    'variationData' => $variantId,
                    'name' => $name,
                    'price' => $price,
                    'quantity' => $quantity,
                    'total' => $price * $quantity,
                ]);

                if ($cart) {
                    return response()->json(['status' => 'success', 'message' => 'Product added to cart successfully', 'data' => $cart], 201);
                } else {
                    return response()->json(['status' => 'failed', 'message' => 'Something went wrong, please try again.'], 422);
                }
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
        
    }

    public function updateCart(Request $request)
    {
        try {
            $inputs = $request->all();
            $validatedData = Validator::make($inputs, [
                'cart_id' => 'required|exists:carts,id',
                'quantity' => 'required|integer|min:1'
            ]);

            if ($validatedData->fails()) {
                $errors = $validatedData->errors();
                $transformed = [];
                foreach ($errors->all() as $message) {
                    $transformed[] = $message;
                }
                return response()->json(['status' => 'failed', 'message' => $transformed], 422);
            }

            $cartId = $inputs['cart_id'];
            $quantity = $inputs['quantity'];

            $cartItem = Cart::find($cartId);
            if (!$cartItem) {
                return response()->json(['status' => 'failed', 'message' => 'Cart item not found.'], 404);
            }

            $product = Products::find($cartItem->productId);
            if (!$product) {
                return response()->json(['status' => 'failed', 'message' => 'Product not found.'], 404);
            }

            $price = $product->sale_price;
            if ($product->has_variant == 1 && $cartItem->variationData) {
                $variant = ProductVariant::where('product_id', $product->id)->where('id', $cartItem->variationData)->first();
                if (!$variant) {
                    return response()->json(['status' => 'failed', 'message' => 'Product variant not found.'], 404);
                }
                $price = $variant->sale_price;
            }

            $cartItem->quantity = $quantity;
            $cartItem->total = $price * $quantity;

            if ($cartItem->save()) {
                return response()->json(['status' => 'success', 'message' => 'Cart updated successfully', 'data' => $cartItem], 200);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Something went wrong, please try again.'], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function removeFromCart(Request $request)
    {
        try {
            $inputs = $request->all();
            $validatedData = Validator::make($inputs, [
                'cartId' => 'required|exists:carts,id',
                'productId' => 'required|integer'
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
            $cartId = $inputs['cartId'];
            $productId = $inputs['productId'];

            $cartItem = Cart::where('id', $cartId)->where('productId', $productId)->first();

            if ($cartItem) {
                $cartItemRemove = Cart::where('id', $cartId)->where('productId', $productId)->where('user_id', $userId)->delete();

                if($cartItemRemove) {
                    return response()->json(['status' => 'success', 'message' => 'Product removed from cart', 'data' => $cartItem], 200);
                } else {
                return response()->json(['status' => 'failed', 'message' => 'Something went wrong, please try again.'], 422);
                }
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Cart item not found.'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    // public function fetchCarts(Request $request)
    // {
    //     try {
    //         $userId = $this->user_id;

    //         $cartItem = Cart::where('user_id', $userId)->get();

    //         $cartDataArr = array();
    //         if($cartItem) {
    //             foreach($cartItem as $cItem) {
    //                 $product = Products::where('id', $cItem->productId)->first();
    //                 $variant = ProductVariant::where('product_id', $cItem->productId)->where('id', $cItem->variationData)->first();
    //                 $prodImage = ProductImage::where('product_id', $cItem->productId)->first();
    //                 $cartDataArr[] = array();  
    //             }
    //             return response()->json(['status' => 'success', 'message' => '', 'data' => $cartItem], 200);
    //         } else {
    //             return response()->json(['status' => 'failed', 'message' => 'No product found', 'data' => $cartItem], 200);
    //         }
    //     } catch (Exception $e) {
    //         return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
    //     }

    // }

    public function fetchCarts(Request $request)
    {
        try {
            $userId = $this->user_id;

            $cartItems = Cart::where('user_id', $userId)->get();

            $cartDataArr = array();
            if ($cartItems->isNotEmpty()) {
                foreach ($cartItems as $cItem) {
                    $product = Products::where('id', $cItem->productId)->first();
                    $variant = $cItem->variationData ? ProductVariant::where('product_id', $cItem->productId)->where('id', $cItem->variationData)->first() : null;
                    $prodImage = ProductImage::where('product_id', $cItem->productId)->first();

                    $cartDataArr[] = array(
                        'cart_id' => $cItem->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_description' => $product->description,
                        'product_price' => $product->has_variant ? ($variant ? $variant->sale_price : $product->sale_price) : $product->sale_price,
                        'variant_id' => $variant ? $variant->id : null,
                        'variant_name' => $variant ? $variant->name : null,
                        'quantity' => $cItem->quantity,
                        'total_price' => $cItem->quantity * ($product->has_variant ? ($variant ? $variant->sale_price : $product->sale_price) : $product->sale_price),
                        'product_image' => $prodImage ? asset($prodImage->image_path) : null,
                    );
                }
                return response()->json(['status' => 'success', 'message' => 'Cart items retrieved successfully', 'data' => $cartDataArr], 200);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'No product found in cart', 'data' => []], 200);
            }
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
