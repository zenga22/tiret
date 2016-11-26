@extends($theme_layout)

@section('content')
<ol class="breadcrumb">
    <li><a href="{{ url('admin') }}">Pannello Amministrazione</a></li>
    <li><a href="{{ url('admin/users') }}">Utenti</a></li>
    <li class="active">{{ $user->name . ' ' . $user->surname }}</li>
    <li class="pull-right"><a href="{{ url('/auth/logout') }}">Logout</a></li>
</ol>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4 options">
            @role('admin')
                <button type="button" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#editUser">Modifica Utente</button>
                <button type="button" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#enableUser">Abilita / Disabilita</button>
                <button type="button" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#removeUser">Elimina Utente</button>
            @endrole
            <button type="button" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#loadFile">Carica File</button>
        </div>

        <div class="col-md-8 contents">
            @if(count($files) == 0)
                <p class="alert alert-info">Questo utente non ha files assegnati</p>
            @else
                <table class="table">
                    <tbody>
                        @foreach($files as $file)
                            <tr>
                                <td><a href="{{ url('file/' . $file) }}">{{ basename($file) }}</a></td>
                                <td><a class="btn btn-default" href="{{ url('file/delete/' . $file) }}">Elimina</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="loadFile" tabindex="-1" role="dialog" aria-labelledby="loadFileLavel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Carica File</h4>
            </div>
            <form method="POST" action="{{ url('file') }}" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="user_id" value="{{ $user->id }}">

                <div class="modal-body">
                    <p>
                        Assegna un nuovo file all'utente. Sarà caricato nella sua area personale, ed accessibile a lui solo.
                    </p>

                    <div class="form-group">
                        <label for="file">File</label>
                        <input type="file" class="form-control" name="file">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Salva</button>
                </div>
            </form>
        </div>
    </div>
</div>

@role('admin')
    @include('admin.edituser', ['id' => 'editUser', 'title' => 'Edita Utente', 'user' => $user])

    <div class="modal fade" id="enableUser" tabindex="-1" role="dialog" aria-labelledby="enableUserLavel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Stato Utente</h4>
                </div>
                <form method="POST" action="{{ url('admin/status/' . $user->id) }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="modal-body">
                        <p>
                            Un utente disabilitato non può accedere alla piattaforma, benché il suo account ed i suoi files restano conservati fino alla sua esplicita cancellazione.
                        </p>

                        <div class="radio">
                            <label>
                                <input type="radio" name="status" value="disabled"<?php if ($user->suspended == false) echo ' checked="checked"' ?>> Abilitato
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="status" value="disabled"<?php if ($user->suspended == true) echo ' checked="checked"' ?>> Disabilitato
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-primary">Salva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="removeUser" tabindex="-1" role="dialog" aria-labelledby="removeUserLavel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Elimina Utente</h4>
                </div>
                <form method="POST" action="{{ url('admin/delete/' . $user->id) }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="modal-body">
                        <p>
                            Sei sicuro di voler eliminare l'utente {{ $user->name }} {{ $user->surname }}?
                        </p>
                        <p>
                            L'operazione comporta la cancellazione di tutti i files ad esso associati. Eventualmente, considera la possibilità di disabilitarlo anziché eliminarlo.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-primary">Elimina</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endrole

@endsection
