<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_russian_menu()
    {
        $this->get('/')->assertRedirect('/ru');
        $this->get('/ru')->assertOk();
        $this->get('/en')->assertOk();
        $this->get('/uz')->assertNotFound();
    }

    public function test_api_returns_categories_and_products()
    {
        $category = Category::add([
            'title_ru' => 'Супы',
            'title_en' => 'Soups',
        ]);

        Product::add([
            'title_ru' => 'Чечевичный суп',
            'title_en' => 'Lentil soup',
            'description_ru' => 'Горячий суп',
            'description_en' => 'Hot soup',
            'price_uzs' => 30000,
            'netto' => '300 г',
            'category_id' => $category->id,
        ]);

        $this->getJson('/api/categories?limit=100')
            ->assertOk()
            ->assertJsonPath('data.0.title_en', 'Soups')
            ->assertJsonPath('data.0.products_count', 1);

        $this->getJson('/api/products?limit=100')
            ->assertOk()
            ->assertJsonPath('data.0.title_ru', 'Чечевичный суп')
            ->assertJsonPath('data.0.category.title_en', 'Soups')
            ->assertJsonPath('data.0.image', null);
    }

    public function test_api_validates_limit_and_unknown_records()
    {
        $this->getJson('/api/products?limit=1000')->assertStatus(422);
        $this->getJson('/api/products/999')->assertNotFound();
    }

}
