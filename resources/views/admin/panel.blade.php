@extends($theme_layout)

@section('content')
<ol class="breadcrumb">
    <li class="active">Pannello Amministrazione</li>
</ol>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <a href="{{ url('admin/users') }}" class="btn btn-primary btn-lg btn-block">Gestione Utenti</a>
        </div>
        <div class="col-md-6">
            <a href="{{ url('admin/groups') }}" class="btn btn-primary btn-lg btn-block">Gruppi e Files Condivisi</a>
        </div>
        @role('admin')
        <div class="col-md-6">
            <a href="{{ url('admin/rules') }}" class="btn btn-primary btn-lg btn-block">Regole Assegnazione</a>
        </div>
        @endrole
    </div>
</div>
@endsection