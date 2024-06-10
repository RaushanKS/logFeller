<?php

namespace App\Http\Controllers\Api;

use Exception;
use Stripe\Stripe;
use App\Models\Cart;
use App\Models\User;
use App\Models\Orders;
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
                        $discount->description = ($discount->description_en) ? strip_tags($discount->description ) : "";
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
                    $couponInfo[] = array('discountId' => "", 'couponId' => "", 'subtotalAmount' => $salePrice, 'discountAmount' => 0.00, 'grandTotal' => $salePrice, 'applyCouponInfo' => (object) array());
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
                    
                    if($discountType == 'percentage') {
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
            $threeNumberLast = rand(10000000000000, 99999999999999);
            $rest = 'LF' . $userId . $threeNumberLast;

            if ($inputs['payment_method'] == 'stripe') {

                $order = Orders::create([
                    'user_id' => $userId,
                    'couponId' => $inputs['couponId'],
                    'order_id' => $rest,
                    'address_id' => $inputs['addressId'],
                    'total_amount' => $inputs['totalAmount'],
                    'pay_amount' => $inputs['payAmount'],
                    'discount_amount' => $inputs['discountAmount'],
                    'transaction_id' => '',
                    'payment_type' => $inputs['payment_method'],
                    // 'payment_status' => '',
                    // 'status' => (!empty($inputs['payment_status'])) ? 2 : 1,   //1 = 
                    'status' => 1,   //1 = Order Confirmed, 2 = Order Cancelled
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
                        }
                    }

                    Stripe::setApiKey(env('STRIPE_SECRET'));

                    // Create a Stripe Checkout session 
                    $session = \Stripe\Checkout\Session::create([
                        'payment_method_types' => ['card'],
                        'line_items' => [
                            [
                                'price_data' => [
                                    'currency' => 'usd',
                                    'product_data' => [
                                        'name' => 'TheLogFeller Order',
                                    ],
                                    'unit_amount' => $inputs['payAmount'] * 100,
                                ],
                                'quantity' => 1,
                            ],
                        ],

                        'mode' => 'payment',
                        'success_url' => 'http://127.0.0.1:8000/success/orderId=' . $orderId,
                        'cancel_url' => 'http://127.0.0.1:8000/cancel/orderId=' . $orderId,


                        // 'success_url' => 'https://yaliweweb.webrifesolution.com/success/orderId=' . $orderId,
                        // 'cancel_url' => 'https://yaliweweb.webrifesolution.com/cancel/orderId=' . $orderId,

                        // Add metadata to store order ID
                        'metadata' => [
                            'order_id' => $orderId,
                        ],
                    ]);

                    $transactionId = $session['id'];
                    $orderCreate = Orders::find($orderId);
                    if ($orderCreate) {
                        $orderCreate->transaction_id = $transactionId;
                        $orderCreate->save();
                    }


                    // Redirect to Stripe Checkout page
                    print_r($session->url);
                    exit;
                    return redirect()->to($session->url);

                    // $notifications = Notification::create([
                    //     "user_id" => $userId,
                    //     "order_id" => $rest,
                    //     "notification_type" => "Order Placed",
                    //     "status" => 1,
                    // ]);

                    // if ($notifications) {
                    //     $user = User::where('id', $userId)->first();
                    //     $email = $user->email;
                    //     $name = $user->name;
                    //     $subject = 'Order Placed Successfully';
                    //     define('TO_EMAIL_VERIFICATION', $email);
                    //     define('TO_NAME_VERIFICATION', $name);
                    //     define('TO_SUBJECT_VERIFICATION', $subject);
                    //     $data = array('name' => $name, 'email' => $email, 'link' => env('APP_URL'));
                    //     Mail::send('order-create', $data, function ($message) {
                    //         $message->to(TO_EMAIL_VERIFICATION, TO_NAME_VERIFICATION)->subject(TO_SUBJECT_VERIFICATION);
                    //         $message->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
                    //     });

                    //     $orderData = array('order_id' => $order->order_id);
                    //     return response()->json(['status' => 'success', 'url' => $session->url, 'orderId' => $orderId, 'data' => $orderData], 200);
                    // }
                } else {
                    return response()->json(['status' => 'failed', 'message' => 'Something went wrong!'], 422);
                }
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function fetchPaymentDetails(Request $request)
    {
        try {
            $inputs = $request->all();

            $userId = $this->user_id;

            $order = Orders::where('id', $inputs['orderId'])->first();

            $session_id = $order->transaction_id;
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $session = \Stripe\Checkout\Session::retrieve($session_id);

            $customer = null;
            $paymentIntent = null;

            if (isset($session->customer)) {
                $customer = \Stripe\Customer::retrieve($session->customer);
            }

            if (isset($session->payment_intent)) {
                $paymentIntent = \Stripe\PaymentIntent::retrieve($session->payment_intent);

                $orderId = $inputs['orderId'];
                $orderCreate = Orders::find($orderId);
                if ($orderCreate) {
                    $orderCreate->transaction_id = $paymentIntent->id;
                    $order->payment_status = $paymentIntent->status;
                    $orderCreate->save();

                    $orderItems = OrderItems::where('order_id', $orderId)->get();

                    foreach ($orderItems as $cartItem) {
                        // Remove item from cart after adding to order
                        $deleteFromCart = Cart::where('user_id', $userId)
                            ->where('productId', $cartItem->product_id)
                            ->delete();
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                // 'session' => $session,
                // 'customer' => $customer,
                'paymentIntent' => $paymentIntent,
            ], 200);
        } catch (ApiErrorException $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
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
