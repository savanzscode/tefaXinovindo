<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $size = $request->query('size', 12);
        $order = $request->query('order', -1);
        $f_brands = $request->query('brands');
        $f_categories = $request->query('categories');
        $min_price = $request->query('min')?$request->query('min'):1;
        $max_price = $request->query('max')?$request->query('max'):1000000000;
        $orderOptions = [
            1 => ['created_at', 'DESC'],
            2 => ['created_at', 'ASC'],
            3 => ['regular_price', 'ASC'],
            4 => ['regular_price', 'DESC']
        ];


        $brands = Brand::orderBy('name','ASC')->get();
        $categories = Category::orderBy('name','ASC')->get();

        [$o_column, $o_order] = $orderOptions[$order] ?? ['id', 'DESC'];

        $products = Product::when(!empty($f_brands), function($query) use ($f_brands) {
            $query->whereIn('brand_id', explode(',', $f_brands));
        })->when(!empty($f_categories), function($query) use ($f_categories) {
            $query->whereIn('category_id', explode(',', $f_categories));
    })->where(function($query) use($min_price,$max_price){
        $query->whereBetween('regular_price',[$min_price,$max_price])
        ->orWhereBetween('sale_price',[$min_price,$max_price]);
    })
        ->orderBy($o_column, $o_order)->paginate($size);

        return view('shop', compact('products', 'size', 'order','brands','f_brands','categories','f_categories','max_price','min_price'));
    }



public function product_details($product_slug)
{
    $product = Product::where("slug",$product_slug)->first();
    $rproducts = Product::where("slug","<>",$product_slug)->get()->take(8);


    return view('details',compact("product","rproducts"));
}
}
