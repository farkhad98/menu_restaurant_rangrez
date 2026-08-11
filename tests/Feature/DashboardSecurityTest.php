<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DashboardSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_does_not_create_or_change_an_administrator()
    {
        $this->get('/dashboard/login')->assertOk();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_dashboard_requires_authentication()
    {
        $this->get('/dashboard/products')->assertRedirect('/dashboard/login');
    }

    public function test_product_upload_rejects_non_image_files()
    {
        $user = User::factory()->create();
        $category = Category::add([
            'title_ru' => 'Напитки',
            'title_en' => 'Drinks',
        ]);

        $response = $this->actingAs($user)->post('/dashboard/products', [
            'title_ru' => 'Чай',
            'title_en' => 'Tea',
            'description_ru' => 'Чёрный чай',
            'description_en' => 'Black tea',
            'price_uzs' => 10000,
            'netto' => '500 мл',
            'category_id' => $category->id,
            'preview_image' => UploadedFile::fake()->create('menu.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('preview_image');
        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_can_be_created_without_an_image()
    {
        $user = User::factory()->create();
        $category = Category::add([
            'title_ru' => 'Напитки',
            'title_en' => 'Drinks',
        ]);

        $response = $this->actingAs($user)->post('/dashboard/products', [
            'title_ru' => 'Чай',
            'title_en' => 'Tea',
            'description_ru' => 'Чёрный чай',
            'description_en' => 'Black tea',
            'price_uzs' => 10000,
            'netto' => '500 мл',
            'category_id' => $category->id,
        ]);

        $product = Product::firstOrFail();
        $response->assertRedirect(route('products.edit', $product->id));
        $this->assertNull($product->image);
    }

    public function test_large_photo_is_resized_and_saved_as_webp()
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $category = Category::add([
            'title_ru' => 'Основные блюда',
            'title_en' => 'Main dishes',
        ]);

        $response = $this->actingAs($user)->post('/dashboard/products', [
            'title_ru' => 'Большая фотография',
            'title_en' => 'Large photo',
            'description_ru' => 'Проверка большого изображения',
            'description_en' => 'Large image test',
            'price_uzs' => 50000,
            'netto' => '350',
            'category_id' => $category->id,
            'preview_image' => UploadedFile::fake()->image('large-photo.jpg', 4000, 3000),
        ]);

        $product = Product::firstOrFail();
        $path = 'uploads/products/' . $product->id . '/' . $product->preview_image;
        $imageInfo = getimagesize(Storage::path($path));

        $response->assertRedirect(route('products.edit', $product->id));
        $this->assertStringEndsWith('.webp', $product->preview_image);
        Storage::assertExists($path);
        $this->assertSame('public', Storage::getVisibility($path));
        $this->assertSame('image/webp', $imageInfo['mime']);
        $this->assertLessThanOrEqual(Product::MAX_SAVED_IMAGE_SIDE, max($imageInfo[0], $imageInfo[1]));
    }

    public function test_avif_photo_can_be_uploaded()
    {
        if (!function_exists('imageavif') || !function_exists('imagecreatefromavif')) {
            $this->markTestSkipped('PHP GD собран без поддержки AVIF.');
        }

        Storage::fake('local');
        $user = User::factory()->create();
        $category = Category::add([
            'title_ru' => 'Десерты',
            'title_en' => 'Desserts',
        ]);
        $temporaryPath = tempnam(sys_get_temp_dir(), 'menu-avif-');
        $avifImage = imagecreatetruecolor(1600, 1200);
        $background = imagecolorallocate($avifImage, 208, 114, 73);
        imagefill($avifImage, 0, 0, $background);
        imageavif($avifImage, $temporaryPath, 80);
        imagedestroy($avifImage);

        try {
            $response = $this->actingAs($user)->post('/dashboard/products', [
                'title_ru' => 'AVIF фотография',
                'title_en' => 'AVIF photo',
                'description_ru' => 'Проверка современного формата',
                'description_en' => 'Modern format test',
                'price_uzs' => 30000,
                'netto' => '200',
                'category_id' => $category->id,
                'preview_image' => new UploadedFile(
                    $temporaryPath,
                    'modern-photo.avif',
                    'image/avif',
                    null,
                    true
                ),
            ]);

            $product = Product::firstOrFail();
            $path = 'uploads/products/' . $product->id . '/' . $product->preview_image;

            $response->assertRedirect(route('products.edit', $product->id));
            $this->assertStringEndsWith('.webp', $product->preview_image);
            Storage::assertExists($path);
        } finally {
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }
}
