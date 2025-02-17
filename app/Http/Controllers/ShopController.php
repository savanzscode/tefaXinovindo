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

        $orderOptions = [
            1 => ['created_at', 'DESC'],
            2 => ['created_at', 'ASC'],
            3 => ['regular_price', 'ASC'],
            4 => ['regular_price', 'DESC']
        ];

        // Ambil kolom dan arah order berdasarkan opsi yang tersedia
        [$o_column, $o_order] = $orderOptions[$order] ?? ['id', 'DESC'];

        $products = Product::orderBy($o_column, $o_order)->paginate($size);

        return view('shop', compact('products', 'size', 'order'));
    }



public function product_details($product_slug)
{
    $product = Product::where("slug",$product_slug)->first();
    $rproducts = Product::where("slug","<>",$product_slug)->get()->take(8);


    return view('details',compact("product","rproducts"));
}
}
