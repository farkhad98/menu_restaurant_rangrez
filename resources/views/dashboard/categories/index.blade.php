@extends('dashboard.layout')

@section('title', '| Категории')

@section('content')
    <div class="admin-section-title">
        <h2>Категории</h2>
        <a class="btn btn-dark admin-add-button" href="{{route('categories.create')}}">
            Добавить категорию
        </a>
    </div>

    <div class="table-responsive">
        {{$categories->onEachSide(2)->links()}}

        <table class="table table-striped table-sm admin-table">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Название RU</th>
                <th scope="col">Название EN</th>
                <th scope="col">Действия</th>
            </tr>
            </thead>
            <tbody>
            @foreach($categories as $category)
                <tr>
                    <td data-label="ID"><a href="{{route('categories.edit', $category->id)}}">{{$category->id}}</a></td>
                    <td data-label="Название RU"><a href="{{route('categories.edit', $category->id)}}">{{$category->title_ru}}</a></td>
                    <td data-label="Название EN"><a href="{{route('categories.edit', $category->id)}}">{{$category->title_en}}</a></td>
                    <td data-label="Действия">
                        <form action="{{route('categories.destroy', $category->id)}}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger admin-delete-button" type="submit">Удалить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{$categories->onEachSide(2)->links()}}
    </div>
@endsection
