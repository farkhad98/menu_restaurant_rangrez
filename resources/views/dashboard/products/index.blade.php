@extends('dashboard.layout')

@section('title', '| Продукты')

@section('content')
    <div class="admin-section-title">
        <h2>Продукты</h2>
        <a class="btn btn-dark admin-add-button" href="{{route('products.create')}}">
            Добавить продукт
        </a>
    </div>

    <div class="table-responsive">
        {{$products->onEachSide(2)->links()}}

        <table class="table table-striped table-sm admin-table">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Название RU</th>
                <th scope="col">Описание RU</th>
                <th scope="col">Цена</th>
                <th scope="col">Изображение</th>
                <th scope="col">Действия</th>
            </tr>
            </thead>
            <tbody>
            @foreach($products as $product)
                <tr>
                    <td data-label="ID"><a href="{{route('products.edit', $product->id)}}">{{$product->id}}</a></td>
                    <td data-label="Название"><a href="{{route('products.edit', $product->id)}}">{{$product->title_ru}}</a></td>
                    <td data-label="Описание"><a href="{{route('products.edit', $product->id)}}">{{Str::limit(strip_tags($product->description_ru), 80)}}</a></td>
                    <td data-label="Цена">{{number_format($product->price_uzs, 0, '.', ' ')}} сум</td>
                    <td data-label="Изображение">
                        <img src="{{$product->getImage()}}" class="admin-table-image" alt="{{$product->title_ru}}">
                    </td>
                    <td data-label="Действия">
                        <form action="{{route('products.destroy', $product->id)}}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger admin-delete-button" type="submit">Удалить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{$products->onEachSide(2)->links()}}
    </div>
@endsection
