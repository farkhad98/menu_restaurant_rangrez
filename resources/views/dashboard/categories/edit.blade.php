@extends('dashboard.layout')

@section('title', '| Изменение категории '.$category->id)

@section('content')
    @include('dashboard.errors')

    <div class="admin-form-card">
        <h2 class="admin-form-title">Изменение категории</h2>

        <form class="admin-form" action="{{route('categories.update', $category->id)}}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="title_ru">Название RU</label>
                        <input id="title_ru" type="text" name="title_ru" class="form-control" required value="{{old('title_ru', $category->title_ru)}}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="title_en">Название EN</label>
                        <input id="title_en" type="text" name="title_en" class="form-control" required value="{{old('title_en', $category->title_en)}}">
                    </div>
                </div>

                <div class="col-12">
                    <button class="btn btn-dark w-100" type="submit">Сохранить изменения</button>
                </div>
            </div>
        </form>
    </div>
@endsection
