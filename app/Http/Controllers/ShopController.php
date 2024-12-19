<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(12);

        return view('shop', compact('products'));
    }

    public function product_details($productSlug)
    {
        $product = Product::where('slug', $productSlug)->first();
        $relatedProducts = Product::where('slug', '<>', $productSlug)
            ->get()
            ->take(8);

        return view('details', compact('product', 'relatedProducts'));
    }
}
