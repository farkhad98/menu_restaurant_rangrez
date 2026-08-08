@extends('dashboard.layout')

@section('title', '| Изменение продукта '.$product->id)

@section('content')
    @include('dashboard.errors')

    <div class="admin-form-card">
        <h2 class="admin-form-title">Изменение продукта</h2>

        <form class="admin-form" action="{{route('products.update', $product->id)}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="title_ru">Название RU</label>
                        <input id="title_ru" type="text" name="title_ru" class="form-control" required value="{{old('title_ru', $product->title_ru)}}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="title_en">Название EN</label>
                        <input id="title_en" type="text" name="title_en" class="form-control" required value="{{old('title_en', $product->title_en)}}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="price_uzs">Цена, сум</label>
                        <input id="price_uzs" type="number" min="0" step="0.01" name="price_uzs" class="form-control" required value="{{old('price_uzs', $product->price_uzs)}}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="netto">Вес нетто</label>
                        <input id="netto" type="text" name="netto" class="form-control" required value="{{old('netto', $product->netto)}}" placeholder="Например: 30">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="category_id">Категория</label>
                        <select id="category_id" name="category_id" class="form-select" required>
                            @foreach($categories as $category)
                                <option value="{{$category->id}}" {{old('category_id', $product->category_id) == $category->id ? 'selected' : ''}}>{{$category->title_ru}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="preview_image">Новое изображение (необязательно)</label>
                        <input id="preview_image" type="file" name="preview_image" class="form-control" accept="image/jpeg,image/png,image/webp,image/avif,.jpg,.jpeg,.png,.webp,.avif">
                        <small class="form-text text-muted">JPG, PNG, WebP или AVIF до 25 МБ. Большие фотографии будут автоматически уменьшены.</small>
                    </div>

                    @if($product->getImage())
                        <img src="{{$product->getImage()}}" class="admin-preview-image" alt="{{$product->title_ru}}">
                    @else
                        <p class="text-muted">Изображение ещё не добавлено.</p>
                    @endif
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="description_ru">Описание RU</label>
                        <textarea id="description_ru" name="description_ru" class="form-control" rows="6">{{old('description_ru', $product->description_ru)}}</textarea>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="description_en">Описание EN</label>
                        <textarea id="description_en" name="description_en" class="form-control" rows="6">{{old('description_en', $product->description_en)}}</textarea>
                    </div>
                </div>

                <div class="col-12">
                    <button class="btn btn-dark w-100" type="submit">Сохранить изменения</button>
                </div>
            </div>
        </form>
    </div>
@endsection
