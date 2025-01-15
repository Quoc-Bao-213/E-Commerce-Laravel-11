<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
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
        $fBrands = $request->query('brands');
        $fCategories = $request->query('categories');
        $minPrice = $request->query('min') ?: 1;
        $maxPrice = $request->query('max') ?: 500;

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

        $brands = Brand::orderBy('name', 'ASC')->get();
        $categories = Category::orderBy('name', 'ASC')->get();
        $products = Product::where(function ($query) use ($fBrands) {
            $query->whereIn('brand_id', explode(',', $fBrands))
                ->orWhereRaw("'" . $fBrands . "'=''");
        })
            ->where(function ($query) use ($fCategories) {
                $query->whereIn('category_id', explode(',', $fCategories))
                    ->orWhereRaw("'" . $fCategories . "'=''");
            })
            ->where(function ($query) use ($minPrice, $maxPrice) {
                $query->whereBetween('regular_price', [$minPrice, $maxPrice])
                    ->orWhereBetween('sale_price', [$minPrice, $maxPrice]);
            })
            ->orderBy($oColumn, $oOrder)->paginate($size);

        return view('shop', compact(
            'products',
            'size',
            'order',
            'brands',
            'fBrands',
            'categories',
            'fCategories',
            'minPrice',
            'maxPrice'
        ));
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
