<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['title_ru' => 'Основные блюда', 'title_en' => 'Main dishes'],
            ['title_ru' => 'Салаты', 'title_en' => 'Salads'],
            ['title_ru' => 'Десерты', 'title_en' => 'Desserts'],
        ];

        foreach ($categories as $fields) {
            Category::firstOrCreate(['title_ru' => $fields['title_ru']], $fields);
        }

        $products = [
            [
                'title_ru' => 'Плов по-чайхански',
                'title_en' => 'Traditional Uzbek Plov',
                'description_ru' => 'Рис, говядина, морковь и ароматные специи.',
                'description_en' => 'Rice, beef, carrots and aromatic spices.',
                'price_uzs' => 65000,
                'netto' => '400',
                'category' => 'Основные блюда',
            ],
            [
                'title_ru' => 'Салат Rangrez',
                'title_en' => 'Rangrez Salad',
                'description_ru' => 'Свежие овощи, зелень и фирменная заправка.',
                'description_en' => 'Fresh vegetables, herbs and our signature dressing.',
                'price_uzs' => 38000,
                'netto' => '250',
                'category' => 'Салаты',
            ],
            [
                'title_ru' => 'Шоколадный фондан',
                'title_en' => 'Chocolate Fondant',
                'description_ru' => 'Тёплый шоколадный десерт с нежной начинкой.',
                'description_en' => 'Warm chocolate dessert with a soft molten centre.',
                'price_uzs' => 42000,
                'netto' => '180',
                'category' => 'Десерты',
            ],
        ];

        foreach ($products as $fields) {
            $category = Category::where('title_ru', $fields['category'])->first();
            unset($fields['category']);
            $fields['category_id'] = $category->id;

            Product::firstOrCreate(['title_ru' => $fields['title_ru']], $fields);
        }
    }
}
