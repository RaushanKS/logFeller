<?php

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TestimonialsController;
use App\Http\Controllers\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/email-confirmation/{token}', function ($token) {
    $user = User::where('verification_token', '=', $token)
    ->first();
    if ($user) {
        if ($user->email_verified_at) {
            $message = 'Email verified already';
            return redirect()->route('login')
            ->with('success', 'Email verified already');
        } else {
            $user->email_verified_at = Carbon::now();
            $user->verification_token = null;
            $user->status = 1;
            $user->save();
            $message = 'Email verified';
            return redirect()->route('login')
            ->with('success', 'Email verified');
        }
    } else {
        $message = 'Something went wrong';
        return redirect()->route('login')
        ->with('success', 'Something went wrong');
    }
});



Route::get('/', [LoginController::class, 'index'])->name('login');
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('admin.login');
Route::get('/forgot-password', [RegisterController::class, 'passwordForgot'])->name('admin.passwordForgot');
Route::post('/send-link', [RegisterController::class, 'sendLink'])->name('admin.sendLink');
Route::get('/password/reset/{token}', [RegisterController::class, 'passwordReset'])->name('admin.passwordReset');
Route::post('/password-reset', [RegisterController::class, 'passwordUpdate'])->name('admin.passwordUpdate');
Route::get('/register', [RegisterController::class, 'index'])->name('register');
Route::post('/register', [RegisterController::class, 'create'])->name('register.create');

Route::group(['middleware' => ['auth']], function () {
    // Admin Profile
    Route::post('/logout', [DashboardController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'profile']);
    Route::post('/profile-info-update/{id}', [ProfileController::class, 'infoUpdate']);
    Route::post('/profile-image-update/{id}', [ProfileController::class, 'profileImageUpdate']);
    Route::post('/password-update/{id}', [ProfileController::class, 'passwordUpdate']);

    //Users  
    Route::get('/user', [UserController::class, 'index'])->name('user');
    Route::get('/users/getUsers/', [UserController::class, 'getUsers']);
    Route::get('/user/delete/{id}', [UserController::class, 'destroy'])->name('user.delete');
    Route::post('/update-user-status', [UserController::class, 'updateUserStatus'])->name('user');

    //Products
    Route::get('products', [ProductsController::class, 'index'])->name('products');
    Route::get('products/create', [ProductsController::class, 'create'])->name('products.create');
    Route::post('products/store', [ProductsController::class, 'store'])->name('products.store');
    Route::get('products/getProducts/', [ProductsController::class, 'getProducts']);
    Route::post('products/online-all/{status}', [ProductsController::class, 'onlineProductsAll'])->name('products.onlineProductsAll');
    Route::post('products/delete-all', [ProductsController::class, 'deleteProductsAll'])->name('products.deleteProductsAll');
    Route::get('product/delete/{id}', [ProductsController::class, 'destroy'])->name('product.delete');
    Route::get('product/edit/{id}', [ProductsController::class, 'edit'])->name('products.edit');
    Route::post('product/update/{id}', [ProductsController::class, 'update'])->name('product.update');
    Route::post('product/image/delete', [ProductsController::class, 'destroyImage'])->name('product.image.delete');

    //Discount coupon
    Route::get('coupons', [DiscountController::class, 'index'])->name('coupons');
    Route::get('coupon/create', [DiscountController::class, 'create'])->name('coupon.create');
    Route::post('coupon/store', [DiscountController::class, 'store'])->name('coupon.store');
    Route::get('coupons/getDiscounts/', [DiscountController::class, 'getDiscounts']);
    Route::get('coupon/edit/{id}', [DiscountController::class, 'edit'])->name('coupon.edit');
    Route::post('coupon/update/{id}', [DiscountController::class, 'update'])->name('coupon.update');
    Route::post('coupons/online-all/{status}', [DiscountController::class, 'onlineDiscountsAll'])->name('coupons.onlineDiscountsAll');
    Route::post('coupons/delete-all', [DiscountController::class, 'deleteDiscountsAll'])->name('coupons.deleteDiscountsAll');
    Route::get('coupon/delete/{id}', [DiscountController::class, 'destroy'])->name('coupon.delete');

    // Testimonials
    Route::get('/testimonials', [TestimonialsController::class, 'index'])->name('testimonials');
    Route::get('testimonials/create', [TestimonialsController::class, 'create'])->name('testimonials.create');
    Route::post('testimonials/store', [TestimonialsController::class, 'store'])->name('testimonials.store');
    Route::get('testimonials/getTestimonials/', [TestimonialsController::class, 'getTestimonials']);
    Route::get('testimonial/edit/{id}', [TestimonialsController::class, 'edit'])->name('testimonial.edit');
    Route::post('testimonial/update/{id}', [TestimonialsController::class, 'update'])->name('testimonial.update');
    Route::post('testimonials/online-all/{status}', [TestimonialsController::class, 'onlineTestimonialsAll'])->name('testimonials.onlineTestimonialsAll');
    Route::post('testimonials/delete-all', [TestimonialsController::class, 'deleteTestimonialsAll'])->name('testimonials.deleteTestimonialsAll');
    Route::get('testimonial/delete/{id}', [TestimonialsController::class, 'destroy'])->name('testimonial.delete');

    // Enquiries
    Route::get('/enquiries', [EnquiryController::class, 'index'])->name('enquiries');
    Route::get('enquiries/getEnquiries/', [EnquiryController::class, 'getEnquiries']);
    Route::post('enquiries/delete-all', [EnquiryController::class, 'deleteEnquiriesAll'])->name('enquiries.deleteEnquiriesAll');
    Route::get('enquiry/delete/{id}', [EnquiryController::class, 'destroy'])->name('enquiries.delete');
    Route::get('enquiry/getEnquiryDetails/{contactId}/', [EnquiryController::class, 'getEnquiryDetails']);

    // Orders
    Route::get('/orders', [OrdersController::class, 'index'])->name('orders');
    Route::get('orders/getOrders/', [OrdersController::class, 'getOrders']);
    Route::get('orders/view/{id}', [OrdersController::class, 'orderSingleView']);
    Route::get('orders/items/view/{id}', [OrdersController::class, 'itemsSingleView']);

});
