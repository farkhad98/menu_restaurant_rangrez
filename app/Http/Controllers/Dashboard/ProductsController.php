<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Redirector;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(30);
        return view('dashboard.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create()
    {
        $categories = Category::all();
        return view('dashboard.products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Redirector
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title_ru' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ru' => 'nullable|string|max:5000',
            'description_en' => 'nullable|string|max:5000',
            'preview_image' => $this->previewImageRules(),
            'price_uzs' => 'required|numeric|min:0|max:9999999999',
            'netto' => 'required|string|max:50',
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        $product = Product::add($request->only([
            'title_ru',
            'title_en',
            'description_ru',
            'description_en',
            'price_uzs',
            'netto',
            'category_id',
        ]));
        $product->uploadImage($request->file('preview_image'));

        return redirect(route('products.edit', $product->id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return View
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        return view('dashboard.products.edit', compact(['product','categories']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title_ru' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ru' => 'nullable|string|max:5000',
            'description_en' => 'nullable|string|max:5000',
            'preview_image' => $this->previewImageRules(),
            'price_uzs' => 'required|numeric|min:0|max:9999999999',
            'netto' => 'required|string|max:50',
            'category_id' => 'required|integer|exists:categories,id',
        ]);
        $product = Product::findOrFail($id);
        $product->edit($request->only([
            'title_ru',
            'title_en',
            'description_ru',
            'description_en',
            'price_uzs',
            'netto',
            'category_id',
        ]));
        $product->uploadImage($request->file('preview_image'));

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        Product::findOrFail($id)->remove();
        return redirect()->back();
    }

    private function previewImageRules(): array
    {
        return [
            'nullable',
            'file',
            'max:' . Product::MAX_IMAGE_FILE_SIZE_KB,
            function ($attribute, $image, $fail) {
                $imageInfo = @getimagesize($image->getRealPath());

                if (!$imageInfo || !in_array($imageInfo['mime'] ?? null, Product::SUPPORTED_IMAGE_TYPES, true)) {
                    $fail('Разрешены изображения JPG, PNG, WebP и AVIF.');
                    return;
                }

                $width = $imageInfo[0];
                $height = $imageInfo[1];
                $pixels = $width * $height;

                if (
                    $width > Product::MAX_SOURCE_IMAGE_SIDE
                    || $height > Product::MAX_SOURCE_IMAGE_SIDE
                    || $pixels > Product::MAX_SOURCE_IMAGE_PIXELS
                ) {
                    $fail('Изображение слишком большое. Максимум: 60 мегапикселей и 12000 пикселей по одной стороне.');
                }
            },
        ];
    }
}
