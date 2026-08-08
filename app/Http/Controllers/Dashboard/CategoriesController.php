<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        $categories = Category::orderBy('created_at', 'DESC')->simplePaginate(30);
        return view('dashboard.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create()
    {
        return view('dashboard.categories.create');
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
            'title_ru' => 'required|string|max:255|unique:categories,title_ru',
            'title_en' => 'required|string|max:255|unique:categories,title_en',
        ]);
        $category = Category::add($request->only(['title_ru', 'title_en']));

        return redirect(route('categories.edit', $category->id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return View
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('dashboard.categories.edit', compact('category'));
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
            'title_ru' => 'required|string|max:255|unique:categories,title_ru,'.$id,
            'title_en' => 'required|string|max:255|unique:categories,title_en,'.$id,
        ]);
        $category = Category::findOrFail($id);
        $category->edit($request->only(['title_ru', 'title_en']));

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
        $category = Category::findOrFail($id);

        DB::transaction(function () use ($category) {
            $category->products->each(function ($product) {
                $product->remove();
            });

            $category->remove();
        });

        return redirect()->back();
    }
}
