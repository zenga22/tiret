@extends($theme_layout)

@section('content')
<ol class="breadcrumb">
    <li><a href="{{ url('admin') }}">Pannello Amministrazione</a></li>
    <li class="active">Regole</li>
</ol>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4 options">
            <div class="well">
                <p>
                    Da questo pannello è possibile modificare le regole di assegnazione automatica dei files.
                </p>
                <p>
                    I files che verranno trovati in verranno verificati con queste regole, e all'occorrenza assegnati alle diverse cartelle.
                </p>
                <p>
                    Le regole devono essere scritte sottoforma di espressioni regolari PCRE, in cui possono apparire le seguenti variabili:
                </p>
                <ul>
                    <li>filename (opzionale): il nome con cui il file verrà salvato permanentemente. Se non specificato, verrà utilizzato il nome originale</li>
                    <li>folder (obbligatorio): la cartella in cui salvare il file, corrisponde allo username di un utente o al nome di un gruppo</li>
                </ul>
                <p>
                    Esempi:
                </p>
                <ul>
                    <li>/^(?P&lt;filename&gt;(?P&lt;folder&gt;[^_]*)_file.pdf)$/</li>
                </ul>
            </div>
        </div>
        <div class="col-md-8 contents">
            <form method="POST" action="{{ url('admin/rules') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                @foreach($rules as $rule)
                <div class="form-group">
                    <input type="hidden" name="ids[]" value="{{ $rule->id }}">
                    <input type="text" class="form-control" name="expressions[]" value="{{ $rule->expression }}">
                    <input type="checkbox" name="delete_{{ $rule->id }}"> Elimina
                </div>
                @endforeach

                <hr />

                <div class="form-group">
                    <label for="newgroup">Nuova Regola</label>
                    <input type="text" class="form-control" name="newrule">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Salva</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection