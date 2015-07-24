@extends($theme_layout)

@section('content')
<ol class="breadcrumb">
    <li><a href="{{ url('admin') }}">Pannello Amministrazione</a></li>
    <li class="active">Gruppi</li>
</ol>

<div class="container-fluid">
    @role('admin')
    <div class="row">
        <button type="button" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#editGroups">Modifica Gruppi</button>
    </div>
    @endrole

    <div class="row">
        @foreach($groups as $group)
        <div class="col-md-6">
            <h4>{{ $group->name }}</h4>

            <button type="button" class="btn btn-lg btn-block load-group-file" data-toggle="modal" data-target="#loadFile" data-group="{{ $group->id }}">Carica File in '{{ $group->name }}'</button>

            @if(count($files[$group->name]) == 0)
                <p class="alert alert-info">Questo gruppo non ha files assegnati</p>
            @else
                <table class="table">
                    <tbody>
                        @foreach($files[$group->name] as $file)
                            <tr>
                                <td><a href="{{ url('file/' . $file) }}">{{ basename($file) }}</a></td>
                                <td class="text-right"><a class="btn btn-default" href="{{ url('file/delete/' . $file) }}">Elimina</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        @endforeach
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
                <input type="hidden" name="group_id" value="">

                <div class="modal-body">
                    <p>
                        Assegna un file al gruppo. Sarà visibile e scaricabile da tutti gli utenti inclusi nel gruppo stesso.
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
<div class="modal fade" id="editGroups" tabindex="-1" role="dialog" aria-labelledby="editGroupsLavel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Carica File</h4>
            </div>
            <form method="POST" action="{{ url('admin/groups') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="modal-body">
                    <p>
                        Qui puoi modificare i nomi dei gruppi esistenti, eliminarli, o creare un gruppo nuovo.
                    </p>
                    <p>
                        Quando un gruppo viene eliminato, tutti i files comuni ad esso assegnati sono eliminati ed i relativi utenti risultano non più assegnati ad alcun gruppo.
                    </p>

                    <hr />

                    @foreach($groups as $group)
                    <div class="form-group">
                        <input type="hidden" name="ids[]" value="{{ $group->id }}">
                        <label for="">{{ $group->name }}</label>
                        <input type="text" class="form-control" name="names[]" value="{{ $group->name }}">
                        <input type="checkbox" name="delete_{{ $group->id }}"> Elimina
                    </div>
                    @endforeach

                    <hr />

                    <div class="form-group">
                        <label for="newgroup">Nuovo Gruppo</label>
                        <input type="text" class="form-control" name="newgroup">
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
@endrole
@endsection