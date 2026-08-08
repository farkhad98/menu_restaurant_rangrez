<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index($locale)
    {
        app()->setLocale($locale);

        return view('client.index');
    }

    public function categories(Request $request)
    {
        $this->validate($request, [
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $limit = $request->get('limit', 100);
        $categories = Category::withCount('products')
            ->orderBy('title_ru')
            ->paginate($limit);

        return response()->json($categories);
    }

    public function categoryDetail(Category $category)
    {
        return response()->json($category);
    }

    public function products(Request $request)
    {
        $this->validate($request, [
            'limit' => 'nullable|integer|min:1|max:100',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        $limit = $request->get('limit', 100);
        $products = Product::with('category')
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->get('category_id'));
            })
            ->orderBy('title_ru')
            ->paginate($limit);

        return response()->json($products);
    }

    public function productDetail(Product $product)
    {
        $product->load('category');

        return response()->json($product);
    }
}
