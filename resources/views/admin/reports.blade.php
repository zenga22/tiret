@extends($theme_layout)

@section('content')
<ol class="breadcrumb">
    <li><a href="{{ url('admin') }}">Pannello Amministrazione</a></li>
    <li class="active">Reports</li>
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

    @if($show_menu)
        <div class="row">
            <div class="col-md-12 text-center">
                <p>Seleziona il gruppo di reports da visualizzare</p>
            </div>
            <div class="col-md-12 contents text-center">
                <a class="btn btn-lg btn-primary" href="{{ url('/admin/reports?section=import') }}">Utenti</a>
                <a class="btn btn-lg btn-primary" href="{{ url('/admin/reports?section=files') }}">Files</a>

                @if(env('TRACK_MAIL_STATUS', false) == true)
                    <a class="btn btn-lg btn-primary" href="{{ url('/admin/reports?section=mail') }}">Mail</a>
                @endif
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-md-12">
                @foreach(['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'] as $index => $name)
                    <a class="btn btn-{{ ($index == $month - 1) ? 'primary' : 'default' }}" href="{{ url('/admin/reports?section=' . $section . '&month=' . ($index + 1)) }}">{{ $name }}</a>
                @endforeach

                <a class="btn btn-default pull-right" href="{{ url('/admin/reports?section=' . $section . '&download=csv') }}">Download CSV</a>
            </div>
        </div>

        <hr/>

        <div class="row">
            <div class="col-md-12 contents">
                @if($section == 'mail')
                    <form method="POST" action="{{ url('admin/resend') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="row">
                            <div class="col-md-12">
                                <input type="text" class="form-control" id="textfilter" autocomplete="off" placeholder="Cerca...">

                                <div class="btn-group" data-toggle="buttons" id="buttonfilter" data-filter-attribute="status">
                                    <label class="btn btn-primary active">
                                        <input type="radio" name="filter" value="all" autocomplete="off" checked> Tutti
                                    </label>
                                    <label class="btn btn-primary">
                                        <input type="radio" name="filter" value="try" autocomplete="off"> In Attesa
                                    </label>
                                    <label class="btn btn-primary">
                                        <input type="radio" name="filter" value="sent" autocomplete="off"> Inviata
                                    </label>
                                    <label class="btn btn-primary">
                                        <input type="radio" name="filter" value="fail" autocomplete="off"> Fallita
                                    </label>
                                    <label class="btn btn-primary">
                                        <input type="radio" name="filter" value="reschedule" autocomplete="off"> Riprovare
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-default">Reinoltra mail selezionate</button>
                            </div>
                        </div>
                        <table class="table filteratable">
                            <thead>
                                <tr>
                                    <th width="5%"></th>
                                    <th width="10%">Data</th>
                                    <th width="15%">Utente</th>
                                    <th width="30%">Mail</th>
                                    <th width="30%">Documento</th>
                                    <th width="10%">Stato</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr data-filter-status="{{ $log->status }}">
                                        <td><input type="checkbox" name="resend[]" value="{{ $log->id }}"></td>
                                        <td>{{ $log->created_at }}</td>
                                        <td>{{ $log->user->username }}</td>
                                        <td>{{ join(', ', $log->user->emails) }}</td>
                                        <td>{{ $log->filename }}</td>
                                        <td>{!! $log->description !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </form>
                @else
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" class="form-control" id="textfilter" autocomplete="off" placeholder="Cerca...">
                        </div>
                    </div>
                    <table class="table filteratable">
                        <thead>
                            <tr>
                                <th width="10%">Data</th>
                                <th width="90%">Messaggio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at }}</td>
                                    <td>{{ $log->message }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
