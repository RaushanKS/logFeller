<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ProductAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['prefix' => 'v1', 'namespace' => 'Api', 'middleware' => 'api'], function () {
    //User Application Routes
    //Auth Routes
    Route::post('/login', [AuthController::class, 'Login']);
    Route::post('/social-login', [AuthController::class, 'socialLogin']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/otp-verification', [AuthController::class, 'otpVerification']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/profile-update', [UserController::class, 'profileUpdate']);
    Route::post('/info-update', [UserController::class, 'infoUpdate']);
    Route::get('/fetch-user', [UserController::class, 'fetchUser']);
    Route::post('/password-change', [UserController::class, 'changePassword']);

    //Delivery Address
    Route::post('/add-address', [UserController::class, 'addAddress']);
    Route::get('/fetch-single-address/{id}', [UserController::class, 'fetchSingleAddress']);
    Route::Post('/update-address', [UserController::class, 'updateAddress']);
    Route::get('/fetch-all-address', [UserController::class, 'fetchAllAddress']);
    Route::Post('/delete-address', [UserController::class, 'deleteAddress']);

    // Route::post('/delete-account', [UserController::class, 'deleteUserAccount']);

    // Wishlist
    Route::post('/add-to-wishlist', [ProductAuthController::class, 'addToWishlist']);
    Route::post('/remove-from-wishlist', [ProductAuthController::class, 'removefromWishlist']);
    Route::post('/fetch-wishlist-products', [ProductAuthController::class, 'fetchWishlistProducts']);

    //Cart
    Route::post('/add-to-cart', [CheckoutController::class, 'addToCart']);
    Route::post('/update-cart', [CheckoutController::class, 'updateCart']);
    Route::post('/remove-from-cart', [CheckoutController::class, 'removeFromCart']);
    Route::post('/fetch-carts', [CheckoutController::class, 'fetchCarts']);
    Route::post('/coupon-apply', [CheckoutController::class, 'couponApply']);
    Route::post('/coupon-remove', [CheckoutController::class, 'couponRemove']);
    Route::post('/fetch-coupons', [CheckoutController::class, 'fetchCoupons']);

    //Order
    Route::post('/order-create', [CheckoutController::class, 'orderCreate']);
    Route::post('/payment-success', [CheckoutController::class, 'paymentSuccess']);
    Route::post('/order-update', [CheckoutController::class, 'orderUpdate']);
    Route::post('/order-list', [CheckoutController::class, 'orderList']);
    Route::post('/order-items-fetch', [CheckoutController::class, 'orderItemsList']);
    Route::post('/order-cancel', [CheckoutController::class, 'orderCancel']);
    Route::post('/order-payment-details',  [CheckoutController::class, 'fetchPaymentDetails']);

    // Without Auth routes

    // Products
    Route::post('/fetch-products', [ProductController::class, 'fetchProducts']);
    Route::post('/single-product', [ProductController::class, 'singleProduct']);

    // Testimonials
    Route::post('/testimonials', [ProductController::class, 'fetchTestimonials']);

    // Contact Us
    Route::post('/contact-us', [ProductController::class, 'storeContactUs']);

});