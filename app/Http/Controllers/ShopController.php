<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $size = $request->query('size') ?: 12;
        $oColumn = '';
        $oOrder = '';
        $order = $request->query('order') ?: -1;

        switch ($order) {
            case 1:
                $oColumn = 'created_at';
                $oOrder = 'DESC';
                break;
            case 2:
                $oColumn = 'created_at';
                $oOrder = 'ASC';
                break;
            case 3:
                $oColumn = 'sale_price';
                $oOrder = 'DESC';
                break;
            case 3:
                $oColumn = 'sale_price';
                $oOrder = 'ASC';
                break;
            default:
                $oColumn = 'id';
                $oOrder = 'DESC';
        }

        $products = Product::orderBy($oColumn, $oOrder)->paginate($size);

        return view('shop', compact('products', 'size', 'order'));
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
