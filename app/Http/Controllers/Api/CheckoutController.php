<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use Stripe\Stripe;
use App\Models\Cart;
use App\Models\User;
use App\Models\Orders;
use App\Helpers\helpers;
use App\Models\Discount;
use App\Models\Products;
use App\Models\Shipping;
use App\Models\OrderItems;
use App\Models\CouponApply;
use App\Models\UserAddress;
use Illuminate\Support\Arr;
use App\Models\OrderCoupons;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use App\Models\ProductVariant;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class CheckoutController extends Controller
{
    private $user_id;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['addToCart', 'updateCart', 'removeFromCart', 'fetchCarts', 'orderCreate', 'paymentSuccess', 'orderUpdate', 'couponApply', 'couponRemove', 'orderList', 'orderItemsList', 'orderCancel', 'fetchPaymentDetails', 'fetchCoupons']]);
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
    //             'quantity' => 'required|integer|min:1'
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
    //         $productId = $inputs['product_id'];
    //         $variantId = Arr::get($inputs, 'variant_id'); // Use Arr::get to safely access 'variant_id'
    //         $quantity = $inputs['quantity'];

    //         $productExists = Products::where('is_important', 1)->where('status', 1)->whereNull('deleted_at')->get();
    //         foreach($productExists as $productExist) {
    //             $cartItems = Cart::where('productId', $productExist->id)->first();
    //             // print_r($cartItems); exit;
    //             $productName = $productExist->name;
    //             if(empty($cartItems)) {
    //                 return response()->json(['status' => 'failed', 'message' => 'Please add ' . $productName . ' first to proceed.'], 422);
    //             }
    //         }


    //         // Fetch the product to check if it has variants 
    //         $product = Products::find($productId);

    //         if ($product->has_variant == 1) {
    //             if (empty($variantId)) {
    //                 return response()->json(['status' => 'failed', 'message' => 'Variant ID is required for products with variants.'], 422);
    //             }

    //             $variant = ProductVariant::where('product_id', $productId)->where('id', $variantId)->first();

    //             if (empty($variant)) {
    //                 return response()->json(['status' => 'failed', 'message' => 'This product variant combination does not exist.'], 422);
    //             }

    //             $price = $variant->sale_price;
    //             $name = $product->name . ' - ' . $variant->name;
    //         } else {
    //             if (!empty($variantId)) {
    //                 return response()->json(['status' => 'failed', 'message' => 'Variant ID should not be provided for products without variants.'], 422);
    //             }

    //             $price = $product->sale_price;
    //             $name = $product->name;
    //         }

    //         $cartData = Cart::where('productId', $productId)
    //             ->where('user_id', $userId)
    //             ->where('variationData', $variantId)
    //             ->first();

    //         if ($cartData) {
    //             $cartData->quantity += $quantity;
    //             $cartData->total = $cartData->quantity * $price;

    //             if ($cartData->save()) {
    //                 return response()->json(['status' => 'success', 'message' => 'Product added to cart successfully', 'data' => $cartData], 201);
    //             } else {
    //                 return response()->json(['status' => 'failed', 'message' => 'Something went wrong, please try again.'], 422);
    //             }
    //         } else {
    //             $cart = Cart::create([
    //                 'user_id' => $userId,
    //                 'productId' => $productId,
    //                 'variationData' => $variantId,
    //                 'name' => $name,
    //                 'price' => $price,
    //                 'quantity' => $quantity,
    //                 'total' => $price * $quantity,
    //             ]);

    //             if ($cart) {
    //                 return response()->json(['status' => 'success', 'message' => 'Product added to cart successfully', 'data' => $cart], 201);
    //             } else {
    //                 return response()->json(['status' => 'failed', 'message' => 'Something went wrong, please try again.'], 422);
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
                return response()->json(['status' => 'failed', 'message' => $validatedData->errors()->all()], 422);
            }

            $userId = $this->user_id;
            $productId = $inputs['product_id'];
            $variantId = Arr::get($inputs, 'variant_id');
            $quantity = $inputs['quantity'];

            $importantProducts = Products::where('is_important', 1)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->get();
            $cartItems = Cart::where('user_id', $userId)->get();
            $hasImportantProductInCart = $cartItems->contains(function ($cartItem) use ($importantProducts) {
                return $importantProducts->contains('id', $cartItem->productId);
            });
            // print_r($importantProducts); exit;
            if ($cartItems->isEmpty() || !$hasImportantProductInCart) {
                if ($importantProducts->contains('id', $productId)) {
                    return $this->addProductToCart($userId, $productId, $variantId, $quantity);
                } else {
                    $importantProductNames = $importantProducts->pluck('name')->toArray();
                    $message = 'Please add ' . implode(', ', $importantProductNames) . ' first to proceed.';
                    return response()->json(['status' => 'failed', 'message' => $message], 422);
                }
            }
            return $this->addProductToCart($userId, $productId, $variantId, $quantity);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    private function addProductToCart($userId, $productId, $variantId, $quantity)
    {
        $product = Products::find($productId);
        if ($product->has_variant) {
            if (empty($variantId)) {
                return response()->json(['status' => 'failed', 'message' => 'Variant ID is required for products with variants.'], 422);
            }

            $variant = ProductVariant::where('product_id', $productId)->where('id', $variantId)->first();
            if (!$variant) {
                return response()->json(['status' => 'failed', 'message' => 'This product variant combination does not exist.'], 422);
            }

            $price = $variant->sale_price;
            $name = $product->name . ' - ' . $variant->name;
        } else {
            if ($variantId) {
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

                if ($cartItemRemove) {
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

    public function fetchCarts(Request $request)
    {
        try {
            $userId = $this->user_id;
            $currentDate = date('Y-m-d');

            $couponApply = CouponApply::where('user_id', $userId)->first();
            $couponInfo = array();

            $cartItems = Cart::where('user_id', $userId)->get();

            $cartDataArr = array();
            $subTotal = 0;
            if ($cartItems->isNotEmpty()) {
                $salePrice = 0;


                foreach ($cartItems as $cItem) {

                    $product = Products::where('id', $cItem->productId)->first();
                    $variant = $cItem->variationData ? ProductVariant::where('product_id', $cItem->productId)->where('id', $cItem->variationData)->first() : null;
                    $prodImage = ProductImage::where('product_id', $cItem->productId)->first();

                    $productPrice = $product->has_variant ? ($variant ? $variant->sale_price : $product->sale_price) : $product->sale_price;
                    $totalPrice = $cItem->quantity * $productPrice;

                    $subTotal += $totalPrice;

                    $cartDataArr[] = array(
                        'cart_id' => $cItem->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_description' => $product->description,
                        'product_price' => $product->has_variant ? ($variant ? $variant->sale_price : $product->sale_price) : $product->sale_price,
                        'variant_id' => $variant ? $variant->id : null,
                        'variant_name' => $variant ? $variant->name : null,
                        'quantity' => $cItem->quantity,
                        'total_price' => $totalPrice,
                        'product_image' => $prodImage ? url($prodImage->image_path) : null,
                    );
                }



                if ($couponApply) {
                    $couponId = $couponApply->coupon_id;
                    $discount = Discount::where('id', $couponApply->coupon_id)->where('end_date', '>=', $currentDate)->where('status', 1)->first();
                    if ($discount) {
                        $discount->deleted_at = "";
                        $discount->max_discount = ($discount->max_discount) ? $discount->max_discount : "";
                        $discount->min_order_amount = ($discount->min_order_amount) ? $discount->min_order_amount : "";
                        $discount->discount_amount = ($discount->discount_amount) ? $discount->discount_amount : "";
                        $discount->discount_percent = ($discount->discount_percent) ? $discount->discount_percent : "";
                        $discount->description = ($discount->description_en) ? strip_tags($discount->description) : "";
                        $discountType = $discount->discount_type;
                        $max_discount = $discount->max_discount;
                        $min_order_amount = $discount->min_order_amount;
                        $discount_amount = $discount->discount_amount;
                        $discount_percent = $discount->discount_percent;
                        $discountAmount = 0;

                        foreach ($cartItems as $key => $cartPro) {
                            $salePrice = $salePrice + $cartPro->total;
                        }

                        if ($discountType == 'percentage') {
                            if (!empty($min_order_amount)) {
                                if ($min_order_amount <= $salePrice) {
                                    $discountAmount = ($salePrice * $discount_percent) / 100;

                                    if ($discountAmount > $max_discount && !empty($max_discount)) {
                                        $discountAmount = $max_discount;
                                    }
                                } else {
                                    return response()->json(['status' => 'failed', 'message' => 'Minimum order amount ' . $min_order_amount . ' required ', 'data' => ''], 200);
                                }
                            } else {
                                $discountAmount = ($salePrice * $discount_percent) / 100;
                                if ($discountAmount > $max_discount && !empty($max_discount)) {
                                    $discountAmount = $max_discount;
                                }
                            }
                        } else {
                            if (!empty($min_order_amount)) {
                                if ($min_order_amount <= $salePrice) {
                                    $discountAmount = $salePrice - $discount_amount;

                                    if ($discountAmount > $max_discount && !empty($max_discount)) {
                                        $discountAmount = $max_discount;
                                    }
                                } else {
                                    return response()->json(['status' => 'failed', 'message' => 'Minimum order amount ' . $min_order_amount . ' required ', 'data' => ''], 200);
                                }
                            } else {
                                $discountAmount = $salePrice - $discount_amount;
                                if ($discountAmount > $max_discount && !empty($max_discount)) {
                                    $discountAmount = $max_discount;
                                }
                            }
                        }

                        $grandTotal = $salePrice - $discountAmount;
                        $couponApplyExistData = CouponApply::where('user_id', $userId)->first();
                        if ($couponApplyExistData) {
                            $couponApplyExistData->coupon_id = (int) $couponId;
                            $couponApplyExistData->sale_price = $salePrice;
                            $couponApplyExistData->discount_amount = $discountAmount;
                            $couponApplyExistData->save();
                            $discountId = $couponApplyExistData->id;
                        } else {
                            $couponApplyData = CouponApply::create(['user_id' => $userId, 'coupon_id' => (int) $couponId, 'sale_price' => $salePrice, 'discount_amount' => $discountAmount]);
                            $discountId = $couponApplyData->id;
                        }

                        $couponInfo[] = array('discountId' => $discountId, 'couponId' => (int) $couponId, 'subtotalAmount' => $salePrice, 'discountAmount' => $discountAmount, 'grandTotal' => $grandTotal, 'applyCouponInfo' => $discount);
                    }
                } else {
                    $couponInfo[] = array('discountId' => "", 'couponId' => "", 'subtotalAmount' => $subTotal, 'discountAmount' => 0.00, 'grandTotal' => $subTotal, 'applyCouponInfo' => (object) array());
                }

                return response()->json(['status' => 'success', 'message' => 'Cart items retrieved successfully', 'subtotalAmount' => $subTotal, 'data' => $cartDataArr, 'couponInfo' => $couponInfo], 200);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'No product found in cart', 'data' => []], 200);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function couponApply(Request $request)
    {
        try {
            $inputs = $request->all();
            $userId = $this->user_id;
            $currentDate = date('Y-m-d');
            $couponId = $inputs['couponId'];
            if ($couponId == '') {
                return response()->json(['status' => 'failed', 'message' => 'Please select coupon', 'data' => ''], 403);
            }
            $cartsProducts = Cart::join('products', 'products.id', '=', 'carts.productId')->where('carts.user_id', $this->user_id)->get(['products.name', 'products.slug', 'products.sale_price', 'carts.*']);
            if ($cartsProducts) {
                $discount = Discount::where('id', $inputs['couponId'])->where('end_date', '>=', $currentDate)->where('status', 1)->first();
                if ($discount) {
                    $discount->deleted_at = "";
                    $discount->max_discount = ($discount->max_discount) ? $discount->max_discount : "";
                    $discount->min_order_amount = ($discount->min_order_amount) ? $discount->min_order_amount : "";
                    $discount->discount_amount = ($discount->discount_amount) ? $discount->discount_amount : "";
                    $discount->discount_percent = ($discount->discount_percent) ? $discount->discount_percent : "";
                    $discount->description = ($discount->description) ? strip_tags($discount->description) : "";
                    $discountType = $discount->discount_type;
                    $max_discount = $discount->max_discount;
                    $min_order_amount = $discount->min_order_amount;
                    $discount_amount = $discount->discount_amount;
                    $discount_percent = $discount->discount_percent;
                    // $basePrice = 0;
                    $salePrice = 0;
                    $discountAmount = 0;
                    foreach ($cartsProducts as $key => $cartPro) {
                        $salePrice = $salePrice + $cartPro->total;
                    }

                    if ($discountType == 'percentage') {
                        if (!empty($min_order_amount)) {
                            if ($min_order_amount <= $salePrice) {
                                $discountAmount = ($salePrice * $discount_percent) / 100;

                                if ($discountAmount > $max_discount && !empty($max_discount)) {
                                    $discountAmount = $max_discount;
                                }
                            } else {
                                return response()->json(['status' => 'failed', 'message' => 'Minimum order amount ' . $min_order_amount . ' required ', 'data' => ''], 200);
                            }
                        } else {
                            $discountAmount = ($salePrice * $discount_percent) / 100;
                            if ($discountAmount > $max_discount && !empty($max_discount)) {
                                $discountAmount = $max_discount;
                            }
                        }
                    } else {
                        if (!empty($min_order_amount)) {
                            if ($min_order_amount <= $salePrice) {
                                $discountAmount = $salePrice - $discount_amount;

                                if ($discountAmount > $max_discount && !empty($max_discount)) {
                                    $discountAmount = $max_discount;
                                }
                            } else {
                                return response()->json(['status' => 'failed', 'message' => 'Minimum order amount ' . $min_order_amount . ' required ', 'data' => ''], 200);
                            }
                        } else {
                            $discountAmount = $salePrice - $discount_amount;
                            if ($discountAmount > $max_discount && !empty($max_discount)) {
                                $discountAmount = $max_discount;
                            }
                        }
                    }

                    $grandTotal = $salePrice - $discountAmount;
                    $couponApplyExistData = CouponApply::where('user_id', $userId)->first();
                    if ($couponApplyExistData) {
                        $couponApplyExistData->coupon_id = (int) $couponId;
                        $couponApplyExistData->sale_price = $salePrice;
                        // $couponApplyExistData->base_price = $basePrice;
                        $couponApplyExistData->discount_amount = $discountAmount;
                        $couponApplyExistData->save();
                        $discountId = $couponApplyExistData->id;
                    } else {
                        $couponApplyData = CouponApply::create(['user_id' => $userId, 'coupon_id' => (int) $couponId, 'sale_price' => $salePrice, 'discount_amount' => $discountAmount]);
                        $discountId = $couponApplyData->id;
                    }

                    $couponData = array('discountId' => $discountId, 'couponId' => (int) $couponId, 'subtotalAmount' => $salePrice, 'discountAmount' => $discountAmount, 'grandTotal' => $grandTotal);
                    return response()->json(['status' => 'success', 'message' => 'Coupon applied successfully.', 'data' => $couponData], 200);
                } else {
                    return response()->json(['status' => 'failed', 'message' => 'Invalid coupon.', 'data' => ''], 403);
                }
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Please add products to cart first.', 'data' => ''], 403);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function couponRemove(Request $request)
    {
        try {
            $inputs = $request->all();
            $userId = $this->user_id;
            $currentDate = date('Y-m-d');
            $removeId = $inputs['removeId'];
            if ($removeId == '') {
                return response()->json(['status' => 'failed', 'message' => 'Please select coupon', 'data' => ''], 403);
            }
            $res = CouponApply::where('user_id', $userId)->where('id', $removeId)->forcedelete();
            return response()->json(['status' => 'success', 'message' => 'Coupon remove successfully', 'data' => []], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function orderCreate(Request $request)
    {
        try {
            $inputs = $request->all();
            $rules = [
                'addressId' => 'required',
                'totalAmount' => 'required',
                'payAmount' => 'required',
                'payment_method' => 'required',
                'item' => 'required',
            ];
            // echo ($inputs['payAmount']); exit; 
            $customMessages = [
                'required' => 'Some information messing Please Try again!',
            ];
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                // Session::flash('error', __($validator->errors()->first()));
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $address = UserAddress::where('id', $inputs['addressId'])->first();
            $applyCoupon = CouponApply::where('id', $inputs['couponId'])->first();

            $userId = $this->user_id;
            $userName = User::where('id', $userId)->pluck('name');
            $threeNumberLast = rand(10000000000000, 99999999999999);
            $rest = 'LF' . $userId . $threeNumberLast;

            if ($inputs['payment_method'] == 'stripe') {
                $stripe = new \Stripe\StripeClient('sk_test_51P3tGMIVnUY0WCEQxHNL3YHoZrqknskGyhCxvoNYgp3WXlwqMdpwZlY4XrdXDydWEokpgNMGNDwPysZ4lSCQ9l2o00JU1IB5hx');

                $address = UserAddress::where('id', $inputs['addressId'])->first();

                // Calculate the distance from Cardiff
                $distance = calculateDistanceFromCardiff($address->code);
                $deliveryCharge = 0;

                if ($distance > 20) {
                    // If the distance is greater than 20 miles, do not proceed with the order
                    return response()->json(['status' => 'failed', 'message' => 'Delivery address is too far from the shipment office.'], 422);
                } elseif ($distance > 0 && $distance <= 20) {
                    // If the distance is within 20 miles, apply a delivery charge of £10 per bag
                    $deliveryCharge = 10 * 100; // in pence
                }

                $customer = $stripe->customers->create([
                    'name' => $userName,
                    'address' => [
                        'line1' => $address->street . ' ' . $address->landmark,
                        'postal_code' => $address->code,
                        'city' => $address->city,
                        'state' => $address->state,
                        'country' => 'UK',
                    ],
                ]);

                $ephemeralKey = $stripe->ephemeralKeys->create([
                    'customer' => $customer->id,
                ], [
                    'stripe_version' => '2023-10-16',
                ]);

                // Convert payAmount from pounds to pence
                $payAmountInPence = intval($inputs['payAmount'] * 100);

                // Add delivery charge to payAmount
                $payAmountInPence += $deliveryCharge;

                $paymentIntent = $stripe->paymentIntents->create([
                    'amount' => $payAmountInPence, // amount in pence
                    'currency' => 'gbp',
                    'customer' => $customer->id,
                    'setup_future_usage' => 'off_session',
                    'automatic_payment_methods' => [
                        'enabled' => true,
                    ],
                ]);

                $paymentData = [
                    'paymentIntentId' => $paymentIntent->id,
                    'paymentIntent' => $paymentIntent->client_secret,
                    'ephemeralKey' => $ephemeralKey->secret,
                    'customer' => $customer->id,
                    'publishableKey' => 'pk_test_51P3tGMIVnUY0WCEQUFadAdAUGWREQ5j7pZbzu70S6jWy8hQ9W7xCHwDPYf8TP9XHGLc9Yra0UNdisNroSq3pbXML00lkwC29gm'
                ];

                return response()->json(['status' => 'success', 'paymentDetails' => $paymentData, 'paymentIntent' => $paymentIntent], 200);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Something went wrong!'], 422);
            }


            // if ($inputs['payment_method'] == 'stripe') {

            //     $stripe = new \Stripe\StripeClient('sk_test_51P3tGMIVnUY0WCEQxHNL3YHoZrqknskGyhCxvoNYgp3WXlwqMdpwZlY4XrdXDydWEokpgNMGNDwPysZ4lSCQ9l2o00JU1IB5hx');

            //     $customer = $stripe->customers->create([
            //         'name' => $userName,
            //         'address' => [
            //             'line1' => $address['street'] . '&nbsp;' . $address['landmark'],
            //             'postal_code' => $address['code'],
            //             'city' => $address['city'],
            //             'state' => $address['state'],
            //             'country' => 'UK',
            //         ],
            //     ]);
            //     $ephemeralKey = $stripe->ephemeralKeys->create([
            //         'customer' => $customer->id,
            //     ], [
            //         'stripe_version' => '2023-10-16',
            //     ]);

            //     $payAmountInPence = intval($inputs['payAmount'] * 100);

            //     $paymentIntent = $stripe->paymentIntents->create([
            //         'amount' => $payAmountInPence,
            //         'currency' => 'gbp',
            //         'customer' => $customer->id,
            //         'setup_future_usage' => 'off_session',
            //         'automatic_payment_methods' => [
            //             'enabled' => 'true',
            //         ],
            //     ]);

            //     $paymentData = (
            //         [
            //             'paymentIntentId' => $paymentIntent->id,
            //             'paymentIntent' => $paymentIntent->client_secret,
            //             'ephemeralKey' => $ephemeralKey->secret,
            //             'customer' => $customer->id,
            //             'publishableKey' => 'pk_test_51P3tGMIVnUY0WCEQUFadAdAUGWREQ5j7pZbzu70S6jWy8hQ9W7xCHwDPYf8TP9XHGLc9Yra0UNdisNroSq3pbXML00lkwC29gm'
            //         ]
            //     );
            //     return response()->json(['status' => 'success', 'paymentDetails' => $paymentData, 'paymentIntent' => $paymentIntent], 200);
            // } else {
            //     return response()->json(['status' => 'failed', 'message' => 'Something went wrong!'], 422);
            // }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function paymentSuccess(Request $request)
    {
        try {
            $inputs = $request->all();
            $rules = [
                'addressId' => 'required',
                'totalAmount' => 'required',
                'payAmount' => 'required',
                'payment_method' => 'required',
                'item' => 'required',
            ];
            // echo ($inputs['payAmount']); exit;
            $customMessages = [
                'required' => 'Some information messing Please Try again!',
            ];
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                // Session::flash('error', __($validator->errors()->first()));
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $address = UserAddress::where('id', $inputs['addressId'])->first();
            $applyCoupon = CouponApply::where('coupon_id', $inputs['couponId'])->first();
            print_r($applyCoupon);
            exit;

            $userId = $this->user_id;
            $userName = User::where('id', $userId)->pluck('name');
            $threeNumberLast = rand(10000000000000, 99999999999999);
            $rest = 'LF' . $userId . $threeNumberLast;

            Stripe::setApiKey('sk_test_51P3tGMIVnUY0WCEQxHNL3YHoZrqknskGyhCxvoNYgp3WXlwqMdpwZlY4XrdXDydWEokpgNMGNDwPysZ4lSCQ9l2o00JU1IB5hx');
            $paymentIntentId = $request->input('txn_id');

            // Retrieve the payment intent from Stripe
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            // $paymentDetails = [
            //     'amount' => $paymentIntent->amount,
            //     'status' => $paymentIntent->status,
            //     'id' => $paymentIntent->id
            // ];
            // return response()->json(['status' => 'success', 'message' => 'Order placed successfully', 'data' => $paymentDetails], 200);

            if ($paymentIntent->status == 'succeeded') {

                $order = Orders::create([
                    'user_id' => $userId,
                    'couponId' => $inputs['couponId'],
                    'order_id' => $rest,
                    'address_id' => $inputs['addressId'],
                    'total_amount' => $inputs['totalAmount'],
                    'pay_amount' => $inputs['payAmount'],
                    'discount_amount' => $inputs['discountAmount'],
                    'transaction_id' => $paymentIntent->id,
                    'payment_type' => $inputs['payment_method'],
                    'payment_status' => $paymentIntent->status,
                    'status' => ($paymentIntent->status == 'succeeded') ? 1 : 2,   //1 = 
                    // 'status' => 1,   //1 = Order Confirmed, 2 = Order Cancelled
                ]);

                if ($order) {
                    $orderId = $order['id'];
                    if (!empty($applyCoupon)) {
                        $discount = Discount::where('id', $applyCoupon->coupon_id)->first();
                        $orderCoupon = OrderCoupons::create([
                            "user_id" => $userId,
                            "order_id" => $orderId,
                            "couponId" => $discount->id,
                            "name" => $discount->name,
                            "discount_type" => $discount->discount_type,
                            "max_discount" => $discount->max_discount,
                            "min_order_amount" => $discount->min_order_amount,
                            "discount_amount" => $discount->discount_amount,
                            "discount_percent" => $discount->discount_percent,
                            "description" => $discount->description,
                            "start_date" => $discount->start_date,
                            "end_date" => $discount->end_date,
                        ]);
                    }
                    if (!empty($address)) {
                        $shippings = Shipping::create([
                            "user_id" => $userId,
                            "address_id" => $address->id,
                            "order_id" => $orderId,
                            "name" => $address->name,
                            "mobile" => $address->mobile,
                            "phone_code" => $address->phone_code,
                            "phone_country" => $address->phone_country,
                            "street" => $address->street,
                            "landmark" => $address->landmark,
                            "state" => $address->state,
                            "city" => $address->city,
                            "code" => $address->code,
                            "address_type" => $address->address_type,
                        ]);
                    }
                    if (!empty($inputs['item'])) {
                        foreach ($inputs['item'] as $productData) {
                            $orderNumber = rand(10000000000000, 99999999999999);
                            $odNumber = 'LF' . $userId . $orderNumber;
                            $productId = $productData['productId'];
                            $product = Products::where('id', $productId)->first();
                            $items = OrderItems::create([
                                'product_id' => $productData['productId'],
                                'order_id' => $orderId,
                                'order_number' => $odNumber,
                                'variation_id' => !empty($productData['variationId']) ? $productData['variationId'] : null,
                                'quantity' => $productData['quantity'],
                                'sale_price' => $productData['sale_price'],
                                'status' => 1,
                            ]);

                            // delete coupon
                            $res = CouponApply::where('user_id', $userId)->where('coupon_id', $discount->id)->forceDelete();
                            // Remove item from cart after adding to order
                            $deleteFromCart = Cart::where('user_id', $userId)
                                ->where('productId', $productId)
                                ->delete();
                        }
                    }
                    $orderData = array('order_id' => $order->order_id);
                    return response()->json(['status' => 'success', 'message' => 'Order placed successfully', 'data' => $orderData], 200);
                } else {
                    return response()->json(['status' => 'failed', 'message' => 'Something went wrong'], 422);
                }
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Something went wrong'], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 422);
        }
    }

    // public function fetchPaymentDetails(Request $request)
    // {
    //     try {
    //         $inputs = $request->all();

    //         $userId = $this->user_id;

    //         $order = Orders::where('id', $inputs['orderId'])->first();

    //         $session_id = $order->transaction_id;
    //         Stripe::setApiKey(env('STRIPE_SECRET'));
    //         $session = \Stripe\Checkout\Session::retrieve($session_id);

    //         $customer = null;
    //         $paymentIntent = null;

    //         if (isset($session->customer)) {
    //             $customer = \Stripe\Customer::retrieve($session->customer);
    //         }

    //         if (isset($session->payment_intent)) {
    //             $paymentIntent = \Stripe\PaymentIntent::retrieve($session->payment_intent);

    //             $orderId = $inputs['orderId'];
    //             $orderCreate = Orders::find($orderId);
    //             if ($orderCreate) {
    //                 $orderCreate->transaction_id = $paymentIntent->id;
    //                 $order->payment_status = $paymentIntent->status;
    //                 $orderCreate->save();

    //                 $orderItems = OrderItems::where('order_id', $orderId)->get();

    //                 foreach ($orderItems as $cartItem) {
    //                     // Remove item from cart after adding to order
    //                     $deleteFromCart = Cart::where('user_id', $userId)
    //                         ->where('productId', $cartItem->product_id)
    //                         ->delete();
    //                 }
    //             }
    //         }

    //         return response()->json([
    //             'status' => 'success',
    //             // 'session' => $session,
    //             // 'customer' => $customer,
    //             'paymentIntent' => $paymentIntent,
    //         ], 200);
    //     } catch (ApiErrorException $e) {
    //         return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
    //     }
    // }

    public function fetchCoupons(Request $request)
    {
        try {
            $inputs = $request->all();
            $couponArr = [];
            $currencySymbol = '£';
            $percentSymbol = '%';
            $now = date('Y-m-d');
            $totalCoupons = Discount::where('status', 1)
                ->where('end_date', '>=', $now)
                ->whereNull('deleted_at')
                ->count();

            // Fetch products with images and variants 
            $coupons = Discount::where('status', 1)
                ->whereNull('deleted_at')
                ->where('end_date', '>=', $now)
                ->get();

            foreach ($coupons as $coupon) {
                if ($coupon->discount_type == 'percentage') {
                    $discount = "{$coupon->discount_percent}{$percentSymbol}";
                } elseif ($coupon->discount_type == 'fixed') {
                    $discount = "{$currencySymbol}{$coupon->discount_amount}";
                }

                $maxDiscount = "{$currencySymbol}{$coupon->max_discount}";
                $minOrderAmount = "{$currencySymbol}{$coupon->min_order_amount}";

                $couponData = [
                    'id' => $coupon->id,
                    'name' => $coupon->name,
                    'discount_type' => $coupon->discount_type,
                    'discount' => $discount,
                    'max_discount' => $maxDiscount,
                    'min_order_amount' => $minOrderAmount,
                    'description' => $coupon->description,
                    'start_date' => $coupon->start_date,
                    'end_date' => $coupon->end_date,
                ];

                $couponArr[] = $couponData;
            }
            return response()->json(['status' => 'success', 'message' => 'Record Found', 'totalCoupons' => $totalCoupons, 'data' => ['coupons' => $couponArr]], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function orderList(Request $request)
    {
        try {
            $inputs = $request->all();
            $userId = $this->user_id;
            $currencySymbol = '£';
            $percentSymbol = '%';
            $orderArr = array();
            $orders = OrderItems::join('products', 'products.id', '=', 'order_items.product_id')->join('orders', 'orders.id', '=', 'order_items.order_id')->where('orders.user_id', $userId)->select(['order_items.*', 'products.id as productId', 'products.name', 'products.slug', 'products.description', 'orders.user_id',])->orderBy('order_items.id', 'desc')->get();
            // echo '<pre>';
            // print_r($orders);
            // exit;

            if ($orders) {
                foreach ($orders as $order) {
                    $ordersData = Orders::where('id', $order->order_id)->first();
                    $totalAmount = $ordersData->pay_amount;
                    $totalPayAmount = $ordersData->total_amount;
                    $payPercent = ($totalPayAmount * 100) / $totalAmount;
                    $payPercentRound = number_format((float) $payPercent, 2, '.', '');
                    $discountPercent = 100 - $payPercentRound;
                    $saleAmount = ($discountPercent > 0) ? ($order->quantity * $order->sale_price * $discountPercent) / 100 : number_format((float) $order->quantity * $order->sale_price, 2, '.', '');
                    // echo $order->user_id; exit;
                    //echo'<pre>';print_r($saleAmount);exit;

                    $salePrice = "{$currencySymbol}{$order->sale_price}";
                    $orderAmount = "{$currencySymbol}{$saleAmount}";


                    $firstProductImage = ProductImage::where('product_id', $order->product_id)->first();
                    if ($firstProductImage) {
                        $prodImg = $firstProductImage->image_path;
                    } else {
                        $prodImg = null;
                    }

                    $orderArr[] = array(
                        'id' => $order->id,
                        'user_id' => $order->user_id,
                        'orderId' => $order->order_id,
                        'order_number' => $order->order_number,
                        'quantity' => $order->quantity,
                        'sale_price' => $salePrice,
                        'order_amount' => $orderAmount,
                        'featured_image' => ($prodImg) ? url($prodImg) : null,
                        // 'sku' => $order->sku,
                        'name' => $order->name,
                        'slug' => $order->slug,
                        'status' => $order->status,
                        'productId' => $order->productId,
                        'createdAt' => Carbon::createFromFormat('Y-m-d H:i:s', $order->created_at)->format('Y-m-d'),
                        'updateAt' => Carbon::createFromFormat('Y-m-d H:i:s', $order->updated_at)->format('Y-m-d'),
                    );
                }

                $couponDetails = [];
                if (!empty($ordersData->couponId)) {
                    $discountCoupon = Discount::where('id', $ordersData->couponId)->first();

                    if ($discountCoupon->discount_type == 'percentage') {
                        $discount = "{$discountCoupon->discount_percent}{$percentSymbol}";
                    } elseif ($discountCoupon->discount_type == 'fixed') {
                        $discount = "{$currencySymbol}{$discountCoupon->discount_amount}";
                    }

                    $maxDiscount = "{$currencySymbol}{$discountCoupon->max_discount}";
                    $minOrderAmount = "{$currencySymbol}{$discountCoupon->min_order_amount}";

                    $couponDetails[] = array(
                        'couponName' => $discountCoupon->name,
                        'discountType' => $discount,
                        'max_discount' => $maxDiscount,
                        'min_order_amount' => $minOrderAmount,
                        'description' => $discountCoupon->description,
                        'start_date' => $discountCoupon->start_date,
                        'end_date' => $discountCoupon->end_date,
                    );
                }

                $paymentArr[] = array(
                    'id' => $ordersData->id,
                    'orderId' => $ordersData->order_id,
                    'orderTotalAmount' => $ordersData->total_amount,
                    'payAmont' => $ordersData->pay_amount,
                    'discountAmount' => $ordersData->discount_amount,
                    'transactionId' => $ordersData->transaction_id,
                    'paymentType' => $ordersData->payment_type,
                    'paymentStatus' => $ordersData->payment_status,
                    'discountCoupon' => $couponDetails,
                );
            }
            return response()->json(['status' => 'success', 'message' => '', 'data' => ['orders' => $orderArr, 'paymentDetails' => $paymentArr]], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function orderItemsList(Request $request)
    {
        try {
            $inputs = $request->all();
            $userId = $this->user_id;
            $orderId = $inputs['orderId'];
            $orderNumber = $inputs['orderNumber'];

            $orders = Orders::where('user_id', $userId)->where('id', $orderId)->first();
            $orderArr = null;
            $itemArr = null;

            if ($orders) {

                $orderIdGet = $orders->id;
                $addressId = $orders->address_id;

                $userAddress = UserAddress::where('id', $addressId)->withTrashed()->first();

                $OrderItems = OrderItems::join('products', 'products.id', '=', 'order_items.product_id')->where('order_number', $orderNumber)->orderBy('order_items.id', 'desc')->get(['order_items.*', 'products.name', 'products.slug', 'products.description']);
                if ($OrderItems) {
                    foreach ($OrderItems as $orderItem) {
                        // $vendorId = $orderItem->vendor_id;
                        // 
                        $variationId = $orderItem->variation_id;
                        // $vendor = User::where('id', $vendorId)->first(['name_en', 'email', 'image']);
                        $variation = ProductVariant::where('id', $variationId)->first();
                        $variationData = array();
                        if ($variation) {
                            $variationData = array(
                                'name' => ($variation) ? $variation->name : null,
                                'sale_price' => ($variation) ? $variation->sale_price : null,
                                'quantity' => ($variation) ? $variation->quantity : null,
                            );
                        }

                        $ordersData = Orders::where('id', $orderItem->order_id)->first();
                        $oid = $ordersData->order_id;
                        $totalAmount = $ordersData->total_amount;
                        $totalPayAmount = $ordersData->pay_amount;

                        $payPercent = ($totalPayAmount * 100) / $totalAmount;
                        $payPercentRound = number_format((float) $payPercent, 2, '.', '');
                        $discountPercent = 100 - $payPercentRound;

                        $saleAmount = $orderItem->quantity * $orderItem->sale_price;

                        $discountAmount = ($discountPercent > 0) ? ($saleAmount * $discountPercent) / 100 : 0;
                        $discountAmount = number_format((float) $discountAmount, 2, '.', '');

                        $finalAmount = $saleAmount - $discountAmount;
                        $finalAmount = number_format((float) $finalAmount, 2, '.', '');
                        // echo($discountAmount); exit;

                        $firstProductImage = ProductImage::where('product_id', $orderItem->product_id)->first();
                        if ($firstProductImage) {
                            $prodImg = $firstProductImage->image_path;
                        } else {
                            $prodImg = null;
                        }

                        $itemArr = array(
                            'id' => $orderItem->id,
                            'user_id' => $orders->user_id,
                            'payment_type' => $orders->payment_type,
                            'payment_status' => $orders->payment_status,
                            'payment_type' => $orders->payment_type,
                            'order_id' => $orderItem->order_id,
                            'product_id' => $orderItem->product_id,
                            'order_id' => $oid,
                            'order_number' => $orderItem->order_number,
                            'variation_id' => $orderItem->variation_id,
                            'quantity' => $orderItem->quantity,
                            'sale_price' => $orderItem->sale_price,
                            'order_amount' => $saleAmount,
                            'discount_amount' => $discountAmount,
                            'final_amount' => $finalAmount,
                            'status' => $orderItem->status,
                            'productName' => ($orderItem->name) ? $orderItem->name : null,
                            'productDescription' => $orderItem->description,
                            'productImage' => ($prodImg) ? url($prodImg) : null,
                            'variation' => $variationData,
                            'addressShipping' => array(
                                'id' => $userAddress->id,
                                'name' => $userAddress->name,
                                'mobile' => $userAddress->mobile,
                                'phone_code' => $userAddress->phone_code,
                                'phone_country' => $userAddress->phone_country,
                                'street' => $userAddress->street,
                                'landmark' => $userAddress->landmark,
                                'state' => $userAddress->state,
                                'city' => $userAddress->city,
                                'code' => $userAddress->code,
                                'address_type' => $userAddress->address_type,
                            ),
                        );
                    }
                }
            }
            return response()->json(['status' => 'success', 'message' => '', 'data' => ['orders' => $itemArr]], 200);
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
