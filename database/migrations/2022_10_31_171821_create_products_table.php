<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('title_en')->unique();
                $table->string('title_ru')->unique();
                $table->timestamps();
            });
        }

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title_ru')->default('-');
            $table->string('title_en')->default('-');
            $table->text('description_ru')->nullable();
            $table->text('description_en')->nullable();
            $table->decimal('price_uzs', 12, 2);
            $table->string('netto')->default('-');
            $table->string('preview_image')->nullable();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
