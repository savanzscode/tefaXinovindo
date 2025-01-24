<?php

namespace App\Http\Controllers;

use App\Models\Brand;
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
    }

}

