@extends($theme_layout)

@section('content')
<ol class="breadcrumb">
    <li><a href="{{ url('admin') }}">Pannello Amministrazione</a></li>
    <li class="active">Utenti</li>
    <li class="pull-right"><a href="{{ url('/auth/logout') }}">Logout</a></li>
</ol>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 text-center">
            <p>Seleziona il gruppo di utenti da visualizzare</p>
        </div>
        <div class="col-md-12 contents text-center">
            @foreach($groups as $group)
            <a class="btn btn-lg btn-primary" href="{{ url('/admin/users?group=' . $group->id) }}">{{ $group->name }}</a>
            @endforeach
            <a class="btn btn-lg btn-primary" href="{{ url('/admin/users?group=none') }}">Nessuno</a>
        </div>
    </div>
</div>

@endsection
