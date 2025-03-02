<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function index(){
        return view('user.index');
    }

    public function account_orders()
{
$orders = Order::where('user_id',Auth::user()->id)->orderBy('created_at','DESC')->paginate(10);
return view('user.orders',compact('orders'));
}

public function account_order_details($order_id)
{
        $order = Order::where('user_id',Auth::user()->id)->find($order_id);
        $orderItems = OrderItem::where('order_id',$order_id)->orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id',$order_id)->first();
        return view('user.order-details',compact('order','orderItems','transaction'));
}

public function account_cancel_order(Request $request)
{
    $order = Order::find($request->order_id);
    $order->status = "canceled";
    $order->canceled_date = Carbon::now();
    $order->save();
    return back()->with("status", "Order has been cancelled successfully!");
}

public function showAddress()
    {
        // Ambil alamat dari order terbaru user yang login
        $addresses = Address::where('user_id', Auth::id())->get();
        return view('user.adress', compact('addresses'));
    }
    public function address_store(Request $request)
    {
        $user_id = Auth::user()->id;
        $request->validate([
            'name'	=> 'required|max:100',
            'phone'	=> 'required|numeric|digits:12',
            'zip'	=> 'required|numeric|digits:6',
            'state'	=> 'required',
            'city'	=> 'required',
            'address'	=> 'required',
            'locality'	=> 'required',
            'landmark'	=> 'required',
        ]);

        $address = new Address();
        $address->name = $request->name;
        $address->phone = $request->phone;
        $address->zip = $request->zip;
        $address->state = $request->state;
        $address->city = $request->city;
        $address->address = $request->address;
        $address->locality = $request->locality;
        $address->landmark = $request->landmark;
        $address->country = '';
        $address->user_id = $user_id;
        $address->isdefault = true;
        $address->save();

        return redirect()->route('acc.address')->with('status', 'Address added successfully.');

    }

    public function address_update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'locality' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'landmark' => 'nullable|string|max:255',
            'zip' => 'required|string|max:20',
        ]);

        $addresses = Address::where('user_id', Auth::id())->where('id', $id)->first();
        $addresses->update($request->all());

        return redirect()->route('acc.address')->with('status', 'Address updated successfully.');


    }
    public function address_edit($id)
    {
        $addresses = Address::where('user_id', Auth::id())->where('id', $id)->get();
        return view('user.address-edit', compact('addresses'));
    }
    public function address_add(){
        $addresses = Address::where('user_id', Auth::id())->get();

        return view('user.address-add', compact('addresses'));
    }
    public function updateProfile(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'old_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Periksa apakah password lama cocok
        if (!Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'Password lama tidak sesuai.']);
        }

        // Update password baru
        User::where('id', $user->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->back()->with('success', 'Password successfully updated!');

    }
    public function account_updt_pw(){
        return view('user.account-detail');
    }


    }


