<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\AuthAdmin;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Http\Request;


Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/shop',[ShopController::class,'index'])->name('shop.index');
Route::get('/shop/{product_slug}',[ShopController::class,'product_details'])->name("shop.product.details");

Route::get('/cart',[CartController::class,'index'])->name('cart.index');
Route::post('/cart/store', [CartController::class, 'addToCart'])->name('cart.add');
Route::put('/cart/increase-qunatity/{rowId}',[CartController::class,'increase_item_quantity'])->name('cart.increase.qty');
Route::put('/cart/reduce-qunatity/{rowId}',[CartController::class,'reduce_item_quantity'])->name('cart.reduce.qty');
Route::delete('/cart/remove/{rowId}',[CartController::class,'remove_item_from_cart'])->name('cart.remove');
Route::delete('/cart/clear',[CartController::class,'empty_cart'])->name('cart.empty');

Route::post('/cart/apply-coupon',[CartController::class,'apply_coupon_code'])->name('cart.coupon.apply');
Route::delete('/cart/remove-coupon',[CartController::class,'remove_coupon_code'])->name('cart.coupon.remove');
Route::post('/wishlist/add',[WishlistController::class,'add_to_wishlist'])->name('wishlist.add');
Route::get('/wishlist',[WishlistController::class,'index'])->name('wishlist.index');
Route::delete('/wishlist/remove/{rowId}',[WishlistController::class,'remove_item_from_wishlist'])->name('wishlist.remove');
Route::delete('/wishlist/clear',[WishlistController::class,'empty_wishlist'])->name('wishlist.empty');
Route::post('/wishlist/move-to-cart/{rowId}',[WishlistController::class,'move_to_cart'])->name('wishlist.move.to.cart');

Route::get('/contact-us',[HomeController::class,'contact'])->name('contact.index');
Route::post('/contact/store',[HomeController::class,'contact_store'])->name('contact.send');
Route::get('/about',[HomeController::class,'about'])->name('about.index');

Route::get('/checkout',[CartController::class,'checkout'])->name('cart.checkout');
Route::post('/place-order',[CartController::class,'place_order'])->name('cart.place.order');
Route::get('/order-confirmation',[CartController::class,'confirmation'])->name('cart.confirmation');
Route::get('/payment-success', function () {
    return view('payment-success');
})->name('payment.success');

Route::get('/search',[HomeController::class,'search'])->name('home.search');


Route::get('/get-snap-token/{order_id}', [PaymentController::class, 'getSnapToken']);
Route::post('/update-payment-status/{order_id}', [PaymentController::class, 'updatePaymentStatus']);
Route::post('/midtrans-notification', [PaymentController::class, 'handleMidtransNotification'])
    ->withoutMiddleware([VerifyCsrfToken::class]);


Route::middleware(['auth'])->group(function(){

    Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');
    Route::get('/account-orders',[UserController::class,'account_orders'])->name('user.account.orders');
    Route::get('/account-order-detials/{order_id}',[UserController::class,'account_order_details'])->name('user.acccount.order.details');
    Route::put('/account-order/cancel-order',[UserController::class,'account_cancel_order'])->name('user.account_cancel_order');
    Route::get('/account-address', [UserController::class, 'showAddress'])->name('acc.address');
    Route::post('/account-address/store', [UserController::class, 'address_store'])->name('account.address.store');
    Route::post('/account-address/update/{id}', [UserController::class, 'address_update'])->name('account.address.update');
    Route::get('/account-address/edit/{id}', [UserController::class, 'address_edit'])->name('account.address.edit');
    Route::get('/account-address/add',[UserController::class,'address_add'])->name('account.address.add');
    Route::post('/account-detail/update', [UserController::class, 'updateProfile'])->name('account.update');
    Route::get('/account-detail',[UserController::class,'account_updt_pw'])->name('account.details');
});

Route::middleware(['auth', AuthAdmin::class])->group(function(){
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/brands',[AdminController::class,'brands'])->name('admin.brands');
    Route::get("/admin/brand/add",[AdminController::class,'add_brand'])->name('admin.brand.add');
    Route::post("/admin/brand/store",[AdminController::class,'brand_store'])->name('admin.brand.store');
    Route::get("/admin/brand/edit/{id}",[AdminController::class,'brand_edit'])->name('admin.brand.edit');
    Route::put('/admin/brand/update',[AdminController::class,'brand_update'])->name('admin.brand.update');
    Route::get('/admin/brand/{brand_slug}/products', [AdminController::class, 'products'])
    ->name('admin.brand.products');
    Route::delete("/admin/brand/{id}/delete",[AdminController::class,'brand_delete'])->name('admin.brand.delete');
    Route::get('/admin/categories',[AdminController::class,'categories'])->name('admin.categories');
    Route::get('/admin/category/add',[AdminController::class,'add_category'])->name('admin.category.add');
    Route::post('/admin/category/store',[AdminController::class,'add_category_store'])->name('admin.category.store');
    Route::get('/admin/category/{id}/edit',[AdminController::class,'edit_category'])->name('admin.category.edit');
    Route::put('/admin/category/update',[AdminController::class,'update_category'])->name('admin.category.update');
    Route::get('/admin/category/{category_slug}/products', [AdminController::class, 'products'])
    ->name('admin.category.products');
    Route::delete('/admin/category/{id}/delete',[AdminController::class,'delete_category'])->name('admin.category.delete');
    Route::get('/admin/products',[AdminController::class,'products'])->name('admin.products');
    Route::get('/admin/product/add',[AdminController::class,'add_product'])->name('admin.product.add');
    Route::post('/admin/product/store',[AdminController::class,'product_store'])->name('admin.product.store');
    Route::get('/admin/product/{id}/edit',[AdminController::class,'edit_product'])->name('admin.product.edit');
    Route::put('/admin/product/update',[AdminController::class,'update_product'])->name('admin.product.update');
    Route::get('/admin/product/{id}', [AdminController::class, 'show'])->name('admin.product.detail');
    Route::delete('/admin/product/{id}/delete',[AdminController::class,'delete_product'])->name('admin.product.delete');
    Route::get('/admin/coupons',[AdminController::class,'coupons'])->name('admin.coupons');
    Route::get('/admin/coupon/add',[AdminController::class,'add_coupon'])->name('admin.coupon.add');
    Route::post('/admin/coupon/store',[AdminController::class,'add_coupon_store'])->name('admin.coupon.store');
    Route::put('/admin/coupon/update',[AdminController::class,'update_coupon'])->name('admin.coupon.update');
    Route::get('/admin/coupon/{id}/edit',[AdminController::class,'edit_coupon'])->name('admin.coupon.edit');
    Route::delete('/admin/coupon/{id}/delete',[AdminController::class,'delete_coupon'])->name('admin.coupon.delete');
    Route::get('/admin/orders',[AdminController::class,'orders'])->name('admin.orders');
    Route::get('/admin/order/items/{order_id}',[AdminController::class,'order_items'])->name('admin.order.items');
    Route::put('/admin/order/update-status',[AdminController::class,'update_order_status'])->name('admin.order.status.update');
    Route::get('/admin/slides',[AdminController::class,'slides'])->name('admin.slides');
    Route::get('/admin/slide/add', [AdminController::class,'slide_add'])->name('admin.slide.add');
    Route::post('/admin/slide/store', [AdminController::class,'slide_store'])->name('admin.slide.store');
    Route::get('/admin/slide/{id}/edit', [AdminController::class,'slide_edit'])->name('admin.slide.edit');
    Route::put('/admin/slide/update', [AdminController::class,'slide_update'])->name('admin.slide.update');
    Route::delete('/admin/slide/{id}/delete', [AdminController::class,'slide_delete'])->name('admin.slide.delete');
    Route::get('/admin/user/{id}/edit', [AdminController::class, 'admin_edit'])->name('admin.edit');
    Route::post('/admin/user/{id}/update', [AdminController::class, 'admin_update'])->name('admin.update');
    Route::get('/admin/users',[AdminController::class,'admin_user'])->name('admin.users');
    Route::delete('/admin/users/delete/{id}', [AdminController::class, 'admin_delete'])->name('admin.delete');
    Route::post('/admin/setting/update-password', [AdminController::class, 'admin_update_password'])->name('admin.update.password')->middleware('auth');
    Route::get('/admin/setting',[AdminController::class,'admin_setting'])->name('admin.setting');
    Route::get('/admin/contact',[AdminController::class,'contacts'])->name('admin.contacts');
    Route::delete('/admin/contact/{id}/delete',[AdminController::class,'contact_delete'])->name('admin.contact.delete');
    Route::get('/admin/search',[AdminController::class,'search'])->name('admin.search');
    Route::get('/admin/contact/{id}',[AdminController::class,'show_contact'])->name('admin.show.contact');


});
