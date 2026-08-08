<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
class Category extends Model
{

    protected $fillable = [
        'title_ru',
        'title_en'
    ];

    protected $casts = [
        'products_count' => 'integer',
    ];

    public function products()
    {
    	return $this->hasMany(Product::class);
    }


    public static function add($fields)
    {
        $category = new self;

        $category->fill($fields);
        $category->save();

        return $category;
    }

    public function edit($fields)
    {
        $this->fill($fields);
        $this->save();
    }

    public function remove()
    {
        $this->delete();
    }
}
