<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);

        return view('admin.brands', compact('brands'));
    }

    public function add_brand()
    {
        return view('admin.brand-add');
    }

    public function brand_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->slug);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $fileName = Carbon::now()->timestamp . '.' . $extension;
            $this->generateBrandThumbnailsImage($file, $fileName);
            $brand->image = $fileName;
        }

        $brand->save();

        return redirect()->route('admin.brands')->with('status', 'Brand added successfully');
    }

    public function brand_edit($id)
    {
        $brand = Brand::find($id);

        return view('admin.brand-edit', compact('brand'));
    }

    public function brand_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);

        $brand = Brand::find($request->id);
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->slug);

        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/brands/' . $brand->image))) {
                File::delete(
                    public_path('uploads/brands/' . $brand->image)
                );
            }

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $fileName = Carbon::now()->timestamp . '.' . $extension;
            $this->generateBrandThumbnailsImage($file, $fileName);
            $brand->image = $fileName;
        }

        $brand->save();

        return redirect()->route('admin.brands')->with('status', 'Brand updated successfully');
    }

    public function brand_delete($id)
    {
        $brand = Brand::find($id);

        if (File::exists(public_path('uploads/brands/' . $brand->image))) {
            File::delete(
                public_path('uploads/brands/' . $brand->image)
            );
        }

        $brand->delete();

        return redirect()->route('admin.brands')->with('status', 'Brand deleted successfully');
    }

    public function generateBrandThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/brands');
        $img = Image::read($image->path());

        $img->cover(124, 124, 'top');
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }

    public function categories()
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);

        return view('admin.categories', compact('categories'));
    }

    public function category_add()
    {
        return view('admin.category-add');
    }

    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $fileName = Carbon::now()->timestamp . '.' . $extension;
            $this->generateCategoryThumbnailsImage($file, $fileName);
            $category->image = $fileName;
        }

        $category->save();

        return redirect()->route('admin.categories')->with('status', 'Category added successfully');
    }

    public function category_edit($id)
    {
        $category = Category::find($id);

        return view('admin.category-edit', compact('category'));
    }

    public function category_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);

        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);

        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/categories/' . $category->image))) {
                File::delete(
                    public_path('uploads/categories/' . $category->image)
                );
            }

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $fileName = Carbon::now()->timestamp . '.' . $extension;
            $this->generateCategoryThumbnailsImage($file, $fileName);
            $category->image = $fileName;
        }

        $category->save();

        return redirect()->route('admin.categories')->with('status', 'Category updated successfully');
    }

    public function category_delete($id)
    {
        $category = Category::find($id);

        if (File::exists(public_path('uploads/categories/' . $category->image))) {
            File::delete(
                public_path('uploads/categories/' . $category->image)
            );
        }

        $category->delete();

        return redirect()->route('admin.categories')->with('status', 'Category deleted successfully');
    }

    public function generateCategoryThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/categories');
        $img = Image::read($image->path());

        $img->cover(124, 124, 'top');
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }

    public function products()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(10);

        return view('admin.products', compact('products'));
    }

    public function product_add()
    {
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();

        return view('admin.product-add', compact('categories', 'brands'));
    }

    public function product_store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug',
            'short_description' => 'nullable|string|max:255',
            'description' => 'required|string',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:regular_price',
            'sku' => 'nullable|string|max:50',
            'stock_status' => 'required|in:in_stock,out_of_stock',
            'featured' => 'nullable|boolean',
            'quantity' => 'nullable|integer|min:0',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
            'images' => 'nullable|array',
            // 'images.*' => 'mimes:png,jpg,jpeg|max:2048',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->slug);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->sku = $request->sku;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $fileName = Carbon::now()->timestamp . '.' . $extension;
            $this->generateProductThumbnailsImage($file, $fileName);
            $product->image = $fileName;
        }

        $galleryArr = [];
        $galleryImages = "";
        $counter = 1;

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $extension = $image->getClientOriginalExtension();
                $fileName = Carbon::now()->timestamp . $counter . '.' . $extension;
                $this->generateProductThumbnailsImage($image, $fileName);
                array_push($galleryArr, $fileName);
                $counter++;
            }

            $galleryImages = implode(',', $galleryArr);
            $product->images = $galleryImages;
        }

        $product->save();

        return redirect()->route('admin.products')->with('status', 'Product added successfully');
    }

    public function product_edit($id)
    {
        $product = Product::find($id);
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();

        return view('admin.product-edit', compact('product', 'categories', 'brands'));
    }

    public function product_update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,' . $request->id,
            'short_description' => 'nullable|string|max:255',
            'description' => 'required|string',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:regular_price',
            'sku' => 'nullable|string|max:50',
            'stock_status' => 'required|in:in_stock,out_of_stock',
            'featured' => 'nullable|boolean',
            'quantity' => 'nullable|integer|min:0',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
            'images' => 'nullable|array',
            // 'images.*' => 'mimes:png,jpg,jpeg|max:2048',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
        ]);

        $product = Product::find($request->id);
        $product->name = $request->name;
        $product->slug = Str::slug($request->slug);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->sku = $request->sku;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/products/' . $product->image))) {
                File::delete(public_path('uploads/products/' . $product->image));
            }

            if (File::exists(public_path('uploads/products/thumbnails/' . $product->image))) {
                File::delete(public_path('uploads/products/thumbnails/' . $product->image));
            }

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $fileName = Carbon::now()->timestamp . '.' . $extension;
            $this->generateProductThumbnailsImage($file, $fileName);
            $product->image = $fileName;
        }

        $galleryArr = [];
        $galleryImages = "";
        $counter = 1;

        if ($request->hasFile('images')) {
            foreach (explode(',', $product->images) as $oldImage) {
                if (File::exists(public_path('uploads/products/' . $oldImage))) {
                    File::delete(public_path('uploads/products/' . $oldImage));
                }

                if (File::exists(public_path('uploads/products/thumbnails/' . $oldImage))) {
                    File::delete(public_path('uploads/products/thumbnails/' . $oldImage));
                }
            }

            foreach ($request->file('images') as $image) {
                $extension = $image->getClientOriginalExtension();
                $fileName = Carbon::now()->timestamp . $counter . '.' . $extension;
                $this->generateProductThumbnailsImage($image, $fileName);
                array_push($galleryArr, $fileName);
                $counter++;
            }

            $galleryImages = implode(',', $galleryArr);
            $product->images = $galleryImages;
        }

        $product->save();

        return redirect()->route('admin.products')->with('status', 'Product updated successfully');
    }

    public function product_delete($id)
    {
        $product = Product::find($id);

        if (File::exists(public_path('uploads/products/' . $product->image))) {
            File::delete(public_path('uploads/products/' . $product->image));
        }

        if (File::exists(public_path('uploads/products/thumbnails/' . $product->image))) {
            File::delete(public_path('uploads/products/thumbnails/' . $product->image));
        }

        foreach (explode(',', $product->images) as $oldImage) {
            if (File::exists(public_path('uploads/products/' . $oldImage))) {
                File::delete(public_path('uploads/products/' . $oldImage));
            }

            if (File::exists(public_path('uploads/products/thumbnails/' . $oldImage))) {
                File::delete(public_path('uploads/products/thumbnails/' . $oldImage));
            }
        }

        $product->delete();

        return redirect()->route('admin.products')->with('status', 'Product deleted successfully');
    }

    public function generateProductThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/products');
        $destinationPathThumbnails = public_path('uploads/products/thumbnails');
        $img = Image::read($image->path());

        $img->cover(540, 689, 'top');
        $img->resize(540, 689, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);

        $img->resize(104, 104, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPathThumbnails . '/' . $imageName);
    }
}
