@if($errors->any())
    <div class="dashboard-errors">
        <div class="alert alert-danger" role="alert">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{$error}}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@if(session('status'))
    <div class="dashboard-errors">
        <div class="alert alert-danger" role="alert">{{session('status')}}</div>
    </div>
@endif
