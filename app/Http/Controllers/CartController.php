<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Surfsidemedia\Shoppingcart\Facades\Cart;

class CartController extends Controller
{

public function index()
{
    $cartItems = Cart::instance('cart')->content();
    return view('cart',compact('cartItems'));
}

public function addToCart(Request $request)
{
    Cart::instance('cart')->add($request->id,$request->name,$request->quantity,$request->price)->associate('App\Models\Product');
    session()->flash('success', 'Product is Added to Cart Successfully !');
    return redirect()->back();
}
public function increase_item_quantity($rowId)
{
    $product = Cart::instance('cart')->get($rowId);
    $qty = $product->qty + 1;
    Cart::instance('cart')->update($rowId,$qty);
    return redirect()->back();
}

public function reduce_item_quantity($rowId){
    $product = Cart::instance('cart')->get($rowId);
    $qty = $product->qty - 1;
    Cart::instance('cart')->update($rowId,$qty);
    return redirect()->back();
}
public function remove_item_from_cart($rowId)
{
    Cart::instance('cart')->remove($rowId);
    return redirect()->back();
}
public function empty_cart()
{
    Cart::instance('cart')->destroy();
    return redirect()->back();
}

public function apply_coupon_code(Request $request)
{
    $coupon_code = $request->coupon_code;
    if(isset($coupon_code))
    {
        $coupon = Coupon::where('code',$coupon_code)->where('expiry_date','>=',Carbon::today())->where('cart_value','<=',Cart::instance('cart')->subtotal())->first();
        if(!$coupon)
        {
            return back()->with('error','Invalid coupon code!');
        }
        session()->put('coupon',[
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'cart_value' => $coupon->cart_value
        ]);
        $this->calculateDiscounts();
        return back()->with('success','Coupon code has been applied!');
    }
    else{
        return back()->with('error','Invalid coupon code!');
    }
}

public function calculateDiscounts()
{
    $discount = 0;
    if(session()->has('coupon'))
    {
        if(session()->get('coupon')['type'] == 'fixed')
        {
            $discount = session()->get('coupon')['value'];
        }
        else
        {
            $discount = (Cart::instance('cart')->subtotal() * session()->get('coupon')['value'])/100;
        }

        $subtotalAfterDiscount = Cart::instance('cart')->subtotal() - $discount;
        $taxAfterDiscount = ($subtotalAfterDiscount * config('cart.tax'))/100;
        $totalAfterDiscount = $subtotalAfterDiscount + $taxAfterDiscount;

        session()->put('discounts',[
            'discount' => number_format(floatval($discount),2,'.',''),
            'subtotal' => number_format(floatval(Cart::instance('cart')->subtotal() - $discount),2,'.',''),
            'tax' => number_format(floatval((($subtotalAfterDiscount * config('cart.tax'))/100)),2,'.',''),
            'total' => number_format(floatval($subtotalAfterDiscount + $taxAfterDiscount),2,'.','')
        ]);
    }
}

public function remove_coupon_code()
{
    session()->forget('coupon');
    session()->forget('discounts');
    return back()->with('success','Coupon has been removed!');
}

}
