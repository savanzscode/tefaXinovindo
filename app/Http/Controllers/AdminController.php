<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\File;

class AdminController extends Controller
{
    public function index(){
        return view('admin.index');
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
    }public function categories()
    {
           $categories = Category::orderBy('id','DESC')->paginate(10);
           return view("admin.categories",compact('categories'));
    }public function add_category()
    {
        return view("admin.category-add");
    }public function add_category_store(Request $request)
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

    }public function GenerateCategoryThumbailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/categories');
        $img = Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    } public function edit_category($id)
    {
        $category = Category::find($id);
        return view('admin.category-edit',compact('category'));
    }public function update_category(Request $request)
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
    }public function delete_category($id)
    {
        $category = Category::find($id);
        if (File::exists(public_path('uploads/categories').'/'.$category->image)) {
            File::delete(public_path('uploads/categories').'/'.$category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status','Record has been deleted successfully !');
    }public function products()
    {
        $products = Product::OrderBy('created_at','DESC')->paginate(10);
        return view("admin.products",compact('products'));
    }public function add_product()
    {
        $categories = Category::Select('id','name')->orderBy('name')->get();
        $brands = Brand::Select('id','name')->orderBy('name')->get();



        return view("admin.product-add",compact('categories','brands'));
    }public function product_store(Request $request)
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
    }public function GenerateProductThumbailImage($image, $imageName)
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
    }public function edit_product($id)
    {
        $product = Product::find($id);
        $categories = Category::Select('id','name')->orderBy('name')->get();
        $brands = Brand::Select('id','name')->orderBy('name')->get();



        return view('admin.product-edit',compact('product','categories','brands'));
    }public function update_product(Request $request)
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
    }public function delete_product($id)
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

}

