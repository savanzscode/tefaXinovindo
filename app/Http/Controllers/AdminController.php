<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Slide;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index(){
        $orders = Order::orderby('created_at','DESC')->get()->take(10);
        $dashboardDatas = DB::select("Select sum(total) As TotalAmount,
                                sum(if(status='ordered',total,0)) As TotalOrderedAmount,
                                sum(if(status='delivered',total,0)) As TotalDeliveredAmount,
                                sum(if(status='canceled',total,0)) As TotalCanceledAmount,
                                Count(*) As Total,
                                sum(if(status='ordered',1,0)) As TotalOrdered,
                                sum(if(status='delivered',1,0)) As TotalDelivered,
                                sum(if(status='canceled',1,0)) As TotalCanceled
                                From Orders
                                ");
                                $monthlyDatas = DB::select("SELECT M.id As MonthNo, M.name As MonthName,
                                IFNULL(D.TotalAmount,0) As TotalAmount,
                                IFNULL(D.TotalOrderedAmount,0) As TotalOrderedAmount,
                                IFNULL(D.TotalDeliveredAmount,0) As TotalDeliveredAmount,
                                IFNULL(D.TotalCanceledAmount,0) As TotalCanceledAmount FROM month_names M
                                LEFT JOIN (Select DATE_FORMAT(created_at, '%b') As MonthName,
                                MONTH(created_at) As MonthNo,
                                sum(total) As TotalAmount,
                                sum(if(status='ordered',total,0)) As TotalOrderedAmount,
                                sum(if(status='delivered',total,0)) As TotalDeliveredAmount,
                                sum(if(status='canceled',total,0)) As TotalCanceledAmount
                                From Orders WHERE YEAR(created_at)=YEAR(NOW()) GROUP BY YEAR(created_at), MONTH(created_at) , DATE_FORMAT(created_at, '%b')
                                Order By MONTH(created_at)) D On D.MonthNo=M.id");

    $AmountM = implode(',', collect($monthlyDatas)->pluck('TotalAmount')->toArray());
    $OrderedAmountM = implode(',', collect($monthlyDatas)->pluck('TotalOrderedAmount')->toArray());
    $DeliveredAmountM = implode(',', collect($monthlyDatas)->pluck('TotalDeliveredAmount')->toArray());
    $CanceledAmountM = implode(',', collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());

    $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
    $TotalOrderedAmount = collect($monthlyDatas)->sum('TotalOrderedAmount');
    $TotalDeliveredAmount = collect($monthlyDatas)->sum('TotalDeliveredAmount');
    $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');
    $users = User::where('utype', 'admin')->get();



        return view('admin.index',compact('orders','dashboardDatas','AmountM','OrderedAmountM','DeliveredAmountM','CanceledAmountM','TotalAmount','TotalOrderedAmount','TotalDeliveredAmount','TotalCanceledAmount','users'));
    }
    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands',compact('brands'));
    }
    public function add_brand(){
        return view("admin.brand-add");
    }

    public function brand_edit($id)
    {
        $brand = Brand::find($id);
        return view('admin.brand-edit',compact('brand'));
    }

    public function brand_update(Request $request)
{
    $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:brands,slug,' . $request->id, // Validasi unik untuk slug, abaikan slug milik brand ini
        'image' => 'mimes:png,jpg,jpeg|max:2048'
    ]);

    $brand = Brand::find($request->id);
    $brand->name = $request->name;
    $brand->slug = Str::slug($request->slug); // Ambil slug langsung dari input form
    if ($request->hasFile('image')) {
        if (File::exists(public_path('uploads/brands') . '/' . $brand->image)) {
            File::delete(public_path('uploads/brands') . '/' . $brand->image);
        }
        $image = $request->file('image');
        $file_extention = $image->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->GenerateBrandThumbailsImage($image, $file_name);
        $brand->image = $file_name;
    }

    $brand->save();
    return redirect()->route('admin.brands')->with('status', 'Brand has been updated successfully');
}

public function brand_store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:brands,slug', // Pastikan slug harus unik
        'image' => 'mimes:png,jpg,jpeg|max:2048'
    ]);

    $brand = new Brand();
    $brand->name = $request->name;
    $brand->slug = Str::slug($request->slug); // Ambil slug langsung dari input form
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $file_extention = $image->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->GenerateBrandThumbailsImage($image, $file_name);
        $brand->image = $file_name;
    }

    $brand->save();
    return redirect()->route('admin.brands')->with('status', 'Brand has been added successfully');
}

    public function GenerateBrandThumbailsImage($image, $imageName){
        $destinationPath = public_path('uploads/brands');
        $img = Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    }

    public function brand_delete($id)
    {
        $brand = Brand::find($id);

        // Pastikan brand ditemukan
        if (!$brand) {
            return redirect()->route('admin.brands')->with('error', 'Brand not found.');
        }

        // Periksa apakah file gambar ada, lalu hapus
        if ($brand->image && File::exists(public_path('uploads/brands/' . $brand->image))) {
            File::delete(public_path('uploads/brands/' . $brand->image));
        }

        // Hapus data brand dari database
        $brand->delete();

        return redirect()->route('admin.brands')->with('status', 'Brand has been deleted successfully.');
    }

    public function categories()
    {
           $categories = Category::orderBy('id','DESC')->paginate(10);
           return view("admin.categories",compact('categories'));
    }

    public function add_category()
    {
        return view("admin.category-add");
    }

    public function add_category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->GenerateCategoryThumbailImage($image,$file_name);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status','Record has been added successfully !');

    }
    public function GenerateCategoryThumbailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/categories');
        $img = Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    }

    public function edit_category($id)
    {
        $category = Category::find($id);
        return view('admin.category-edit',compact('category'));
    }

    public function update_category(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,'.$request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = $request->slug;
        if($request->hasFile('image'))
        {
            if (File::exists(public_path('uploads/categories').'/'.$category->image)) {
                File::delete(public_path('uploads/categories').'/'.$category->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('file')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extention;
            $this->GenerateCategoryThumbailImage($image,$file_name);
            $category->image = $file_name;
        }
        $category->save();
        return redirect()->route('admin.categories')->with('status','Record has been updated successfully !');
    }

    public function delete_category($id)
    {
        $category = Category::find($id);
        if (File::exists(public_path('uploads/categories').'/'.$category->image)) {
            File::delete(public_path('uploads/categories').'/'.$category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status','Record has been deleted successfully !');
    }

    public function products()
    {
        $products = Product::OrderBy('created_at','DESC')->paginate(10);
        return view("admin.products",compact('products'));
    }public function add_product()

    {
        $categories = Category::Select('id','name')->orderBy('name')->get();
        $brands = Brand::Select('id','name')->orderBy('name')->get();



        return view("admin.product-add",compact('categories','brands'));
    }

    public function product_store(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'slug'=>'required|unique:products,slug',
            'category_id'=>'required',
            'brand_id'=>'required',
            'short_description'=>'required',
            'description'=>'required',
            'regular_price'=>'required',
            'sale_price'=>'required',
            'SKU'=>'required',
            'stock_status'=>'required',
            'featured'=>'required',
            'quantity'=>'required',
            'image'=>'required|mimes:png,jpg,jpeg|max:2048'
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $current_timestamp = Carbon::now()->timestamp;

        if($request->hasFile('image'))
        {
            if (File::exists(public_path('uploads/products').'/'.$product->image)) {
                File::delete(public_path('uploads/products').'/'.$product->image);
            }
            if (File::exists(public_path('uploads/products/thumbnails').'/'.$product->image)) {
                File::delete(public_path('uploads/products/thumbnails').'/'.$product->image);
            }

            $image = $request->file('image');
            $imageName = $current_timestamp.'.'.$image->extension();

            $this->GenerateProductThumbailImage($image,$imageName);
            $product->image = $imageName;
        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if($request->hasFile('images'))
        {
            $oldGImages = explode(",",$product->images);
            foreach($oldGImages as $gimage)
            {
                if (File::exists(public_path('uploads/products').'/'.trim($gimage))) {
                    File::delete(public_path('uploads/products').'/'.trim($gimage));
                }

                if (File::exists(public_path('uploads/products/thumbails').'/'.trim($gimage))) {
                    File::delete(public_path('uploads/products/thumbails').'/'.trim($gimage));
                }
            }
            $allowedfileExtension=['jpg','png','jpeg'];
            $files = $request->file('images');
            foreach($files as $file){
                $gextension = $file->getClientOriginalExtension();
                $check=in_array($gextension,$allowedfileExtension);
                if($check)
                {
                    $gfilename = $current_timestamp . "-" . $counter . "." . $gextension;
                    $this->GenerateProductThumbailImage($file,$gfilename);
                    array_push($gallery_arr,$gfilename);
                    $counter = $counter + 1;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
        }
        $product->images = $gallery_images;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->save();
        return redirect()->route('admin.products')->with('status','Record has been added successfully !');
    }

    public function GenerateProductThumbailImage($image, $imageName)
    {
        $destinationPathThumbnails = public_path('uploads/products/thumbnails');
        $destinationPath = public_path('uploads/products');
        $img = Image::read($image->path());
        $img->cover(540,689,"top");
        $img->resize(540,689,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
        $img->resize(540,689,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPathThumbnails.'/'.$imageName);
    }

    public function edit_product($id)
    {
        $product = Product::find($id);
        $categories = Category::Select('id','name')->orderBy('name')->get();
        $brands = Brand::Select('id','name')->orderBy('name')->get();



        return view('admin.product-edit',compact('product','categories','brands'));
    }
    public function update_product(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'slug'=>'required|unique:products,slug,'.$request->id,
            'category_id'=>'required',
            'brand_id'=>'required',
            'short_description'=>'required',
            'description'=>'required',
            'regular_price'=>'required',
            'sale_price'=>'required',
            'SKU'=>'required',
            'stock_status'=>'required',
            'featured'=>'required',
            'quantity'=>'required',
            'image'=>'mimes:png,jpg,jpeg|max:2048'
        ]);

        $product = Product::find($request->id);
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $current_timestamp = Carbon::now()->timestamp;

        if($request->hasFile('image'))
        {
            if(File::exists(public_path('uploads/products') . '/' . $product->image))
            {
                File::delete(public_path('uploads/products') . '/' . $product->image);
            }
            if(File::exists(public_path('uploads/products/thumbnails') . '/' . $product->image))
            {
                File::delete(public_path('uploads/products/thumbnails') . '/' . $product->image);
            }

            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateProductThumbnailImage($image, $imageName);
            $product->image = $imageName;

        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if($request->hasFile('images'))
        {
            foreach(explode(',', $product->images) as $ofile)
{
    if(File::exists(public_path('uploads/products') . '/' . $ofile))
    {
        File::delete(public_path('uploads/products') . '/' . $ofile);
    }
    if(File::exists(public_path('uploads/products/thumbnails') . '/' . $ofile))
    {
        File::delete(public_path('uploads/products/thumbnails') . '/' . $ofile);
    }
}

            $allowedfileExtion = ['jpg', 'png', 'jpeg'];
            $files = $request->file('images');
            foreach($files as $file)
            {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension, $allowedfileExtion);
                if($gcheck)
                {
                    $gfileName = $current_timestamp . "-" . $counter . "." . $gextension;
                    $this->GenerateProductThumbailImage($file, $gfileName);
                    array_push($gallery_arr, $gfileName);
                    $counter = $counter + 1;
                }
            }

            $gallery_images = implode(',', $gallery_arr);

        }
        $product->images = $gallery_images;

        $product->save();
        return redirect()->route('admin.products')->with('status','Record has been updated successfully !');
    }
    public function show($id)
{
    $product = Product::with(['category', 'brand'])->findOrFail($id);
    return view('admin.product-detail', compact('product'));
}
    public function delete_product($id)
    {
        $product = Product::find($id);
        if(File::exists(public_path('uploads/products') . '/' . $product->image))
        {
            File::delete(public_path('uploads/products') . '/' . $product->image);
        }
        if(File::exists(public_path('uploads/products/thumbnails') . '/' . $product->image))
        {
            File::delete(public_path('uploads/products/thumbnails') . '/' . $product->image);
        }foreach(explode(',', $product->images) as $ofile)
        {
            if(File::exists(public_path('uploads/products') . '/' . $ofile))
            {
                File::delete(public_path('uploads/products') . '/' . $ofile);
            }
            if(File::exists(public_path('uploads/products/thumbnails') . '/' . $ofile))
            {
                File::delete(public_path('uploads/products/thumbnails') . '/' . $ofile);
            }
        }
        $product->delete();
        return redirect()->route('admin.products')->with('status','Record has been deleted successfully !');
    }

    public function coupons()
{
        $coupons = Coupon::orderBy("expiry_date","DESC")->paginate(12);
        return view("admin.coupons",compact("coupons"));
}

public function add_coupon()
{
    return view("admin.coupon-add");
}

public function add_coupon_store(Request $request)
{
    $request->validate([
        'code' => 'required',
        'type' => 'required',
        'value' => 'required|numeric',
        'cart_value' => 'required|numeric',
        'expiry_date' => 'required|date'
    ]);

    $coupon = new Coupon();
    $coupon->code = $request->code;
    $coupon->type = $request->type;
    $coupon->value = $request->value;
    $coupon->cart_value = $request->cart_value;
    $coupon->expiry_date = $request->expiry_date;
    $coupon->save();
    return redirect()->route("admin.coupons")->with('status','Record has been added successfully !');
}

public function edit_coupon($id)
{
       $coupon = Coupon::find($id);
       return view('admin.coupon-edit',compact('coupon'));
}

public function update_coupon(Request $request)
{
       $request->validate([
       'code' => 'required',
       'type' => 'required',
       'value' => 'required|numeric',
       'cart_value' => 'required|numeric',
       'expiry_date' => 'required|date'
       ]);

       $coupon = Coupon::find($request->id);
       $coupon->code = $request->code;
       $coupon->type = $request->type;
       $coupon->value = $request->value;
       $coupon->cart_value = $request->cart_value;
       $coupon->expiry_date = $request->expiry_date;
       $coupon->save();
       return redirect()->route('admin.coupons')->with('status','Record has been updated successfully !');
}

public function delete_coupon($id)
{
        $coupon = Coupon::find($id);
        $coupon->delete();
        return redirect()->route('admin.coupons')->with('status','Record has been deleted successfully !');
}
public function orders()
{
        $orders = Order::orderBy('created_at','DESC')->paginate(12);
        return view("admin.orders",compact('orders'));
}

public function order_items($order_id){
    $order = Order::find($order_id);
      $orderitems = OrderItem::where('order_id',$order_id)->orderBy('id')->paginate(12);
      $transaction = Transaction::where('order_id',$order_id)->first();


      return view("admin.order-details",compact('order','orderitems','transaction'));
}

public function update_order_status(Request $request){
    $order = Order::find($request->order_id);
    $order->status = $request->order_status;
    if($request->order_status=='delivered')
    {
        $order->delivered_date = Carbon::now();
    }
    else if($request->order_status=='canceled')
    {
        $order->canceled_date = Carbon::now();
    }
    $order->save();
    if($request->order_status=='delivered')
    {
        $transaction = Transaction::where('order_id',$request->order_id)->first();
        $transaction->status = "approved";
        $transaction->save();
    }
    return back()->with("status", "Status changed successfully!");
}


public function slides(){
    $slides = Slide::orderBy('id','DESC')->paginate(12);
    return view("admin.slides",compact('slides'));
}

public function slide_add(){
    return view("admin.slide-add");
}

public function slide_store(Request $request){
    $request->validate([
        'tagline' => 'required',
        'title' => 'required',
        'subtitle' => 'required',
        'link' => 'required',
        'status' => 'required',
        'image' => 'required|mimes:jpg,jpeg,png|max:2048',

    ]);

    $slide = new Slide();
    $slide->tagline = $request->tagline;
    $slide->title = $request->title;
    $slide->subtitle = $request->subtitle;
    $slide->link = $request->link;
    $slide->status = $request->status;
    $image = $request->file('image');
    $file_extention = $request->file('image')->extension();
    $file_name = Carbon::now()->timestamp . '.' . $file_extention;
    $this->GenerateSlideThumbailImage($image,$file_name);
    $slide->image = $file_name;
    $slide->save();
    return redirect()->route('admin.slides')->with('status', 'Slide added successfully!');

}
public function GenerateSlideThumbailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/slides');
        $img = Image::read($image->path());
        $img->cover(400,690,"top");
        $img->resize(400,690,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    }

    public function slide_edit($id){
        $slide = Slide::find($id);
        return view("admin.slide-edit",compact('slide'));
    }

    public function slide_update(Request $request){
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'required|mimes:jpg,jpeg,png|max:2048',

        ]);

        $slide = Slide::find($request->id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        if($request->hasFile('image')){

            if(File::exists(public_path('uploads/slides').'/'.$slide->image))
            {
                File::delete(public_path('uploads/slides').'/'.$slide->image);
            }
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->GenerateSlideThumbailImage($image,$file_name);
        $slide->image = $file_name;
        }
        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'Slide update successfully!');

    }

    public function slide_delete($id){
        $slide = Slide::find($id);
        if(File::exists(public_path('uploads/slides').'/'.$slide->image))
        {
            File::delete(public_path('uploads/slides').'/'.$slide->image);
        }
        $slide->delete();
        return redirect()->route('admin.slides')->with('status', 'Slide delete successfully!');
    }

    public function admin_edit($id)
{
    $user = User::findOrFail($id);
    return view('admin.users-edit', compact('user'));
}

public function admin_update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'mobile' => 'nullable|string|max:12',
    ]);

    $user = User::findOrFail($id);
    $user->update($request->only(['name', 'email', 'mobile']));

    return redirect()->route('admin.users')->with('success', 'User updated successfully');
}

public function admin_user()
{
    $users = User::all(); // Mengambil semua user
    return view('admin.users', compact('users'));
}

public function admin_delete($id)
{
    $user = User::findOrFail($id);
    $user->delete();

    return redirect()->route('admin.users')->with('success', 'User deleted successfully');
}
public function admin_setting(){
    return view('admin.setting');
}

public function admin_update_password(Request $request)
{
    $request->validate([
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

public function contacts(){
    $contacts = Contact::orderBy('created_at','DESC')->paginate(10);
    return view('admin.contacts', compact('contacts'));
}

public function contact_delete($id){
    $contact = Contact::findOrFail($id);
    $contact->delete();
    return redirect()->route('admin.contacts')->with('status', 'Contact deleted successfully');
}
public function search(Request $request){
    $query = $request->input('query');
    $results = Product::where('name','LIKE',"%$query%")->get()->take(8);
    return response()->json($results);
}
public function show_contact($id){
    $contact = Contact::findOrFail($id);
    return view('admin.show_contact', compact('contact'));
}
}

