@extends($theme_layout)

@section('content')
<ol class="breadcrumb">
    <li><a href="{{ url('admin') }}">Pannello Amministrazione</a></li>
    <li><a href="{{ url('admin/users') }}">Utenti</a></li>
    <li class="active">Importazione</li>
    <li class="pull-right"><a href="{{ url('/auth/logout') }}">Logout</a></li>
</ol>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <p>Importazione In Corso... Attendere...</p>
        </div>
    </div>
</div>

<form id="triggerform" method="POST" action="{{ url('admin/import') }}" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" autocomplete="off">
    <input type="hidden" name="step" value="{{ $step }}" autocomplete="off">
</form>
@endsection
