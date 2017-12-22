@extends($theme_layout)

@section('content')
<ol class="breadcrumb">
    <li><a href="{{ url('home') }}">Documenti</a></li>
    <li>Cambia Password</li>
    <li class="pull-right"><a href="{{ url('/auth/logout') }}">Logout</a></li>
</ol>

<div class="container-fluid">
    @if(Session::has('message'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-info">
                    {{ Session::get('message') }}
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <form method="POST" action="{{ url('user') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label for="password">Nuova Password</label>
                    <input type="password" class="form-control" name="password">
                </div>

                <div class="form-group">
                    <label for="password">Conferma Nuova Password</label>
                    <input type="password" class="form-control" name="confirm_password">
                </div>

                <a class="btn btn-default" href="{{ url('home') }}">Annulla</a>
                <button type="submit" class="btn btn-primary">Salva</button>
            </form>
        </div>
    </div>
</div>
@endsection
