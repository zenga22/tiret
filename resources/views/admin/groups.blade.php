@extends($theme_layout)

@section('content')
<ol class="breadcrumb">
    <li><a href="{{ url('admin') }}">Pannello Amministrazione</a></li>
    <li class="active">Gruppi</li>
    <li class="pull-right"><a href="{{ url('/auth/logout') }}">Logout</a></li>
</ol>

<div class="container-fluid">
    @role('admin')
        <div class="row">
            <button type="button" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#editGroups">Modifica Gruppi</button>
        </div>

        @if(env('SEND_MAIL', false) == true)
            <div class="row">
                <button type="button" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#editMailTexts">Modifica Testi Mail</button>
            </div>
        @endif
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
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Modifica Gruppi</h4>
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

                        <p class="clearfix">&nbsp;</p>

                        <div class="row">
                            @foreach($groups as $group)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="hidden" name="ids[]" value="{{ $group->id }}">

                                        <div class="form-group">
                                            <label for="names[]">{{ $group->name }}</label>
                                            <input type="text" class="form-control" name="names[]" value="{{ $group->name }}">
                                        </div>

                                        <div class="form-group">
                                            <label for="emails[]">Email</label>
                                            <input type="text" class="form-control" name="emails[]" value="{{ $group->email }}">
                                        </div>

                                        <div class="form-group">
                                            <label for="signature[]">Firma da postporre nelle email di notifica.</label>
                                            <textarea class="form-control" name="signature[]">{{ $group->signature }}</textarea>
                                        </div>

                                        <div class="form-group">
                                            <label for="message[]">Testo di segnalazione da visualizzare per gli utenti del gruppo</label>
                                            <textarea class="form-control" name="message[]">{{ $group->message }}</textarea>
                                        </div>

                                        <input type="checkbox" name="delete_{{ $group->id }}"> Elimina Gruppo {{ $group->name }}
                                    </div>

                                    <hr/>
                                </div>
                            @endforeach

                            <div class="col-md-12 form-group">
                                <label for="newgroup">Nuovo Gruppo</label>
                                <input type="text" class="form-control" name="newgroup">
                            </div>
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

    @if(env('SEND_MAIL', false) == true)
        <div class="modal fade" id="editMailTexts" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Modifica Testi Mail</h4>
                    </div>
                    <form method="POST" action="{{ url('admin/mails') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="modal-body">
                            <div>
                                <p>
                                    Da qui è possibile configurare diversi testi per le mail di notifica inviate agli utenti, a seconda del nome del file.
                                </p>

                                <hr>

                                <ul class="nav nav-tabs" role="tablist">
                                    <?php $index = 0 ?>
                                    @foreach(App\MailText::where('fallback', false)->orderBy('created_at', 'asc')->get() as $index => $mt)
                                        <li role="presentation" class="{{ $index++ == 0 ? 'active' : '' }}"><a href="#mailtext-{{ $mt->id }}" role="tab" data-toggle="tab">{{ $mt->rule }}</a></li>
                                    @endforeach
                                    <li role="presentation" class="{{ $index == 0 ? 'active' : '' }}"><a href="#mailtext-new" role="tab" data-toggle="tab">Nuova Regola</a></li>
                                    <li role="presentation" class=""><a href="#mailtext-default" role="tab" data-toggle="tab">Mail di Default</a></li>
                                </ul>

                                <div class="tab-content">
                                    <?php $index = 0 ?>
                                    @foreach(App\MailText::where('fallback', false)->orderBy('created_at', 'asc')->get() as $index => $mt)
                                        <div role="tabpanel" class="tab-pane {{ $index++ == 0 ? 'active' : '' }}" id="mailtext-{{ $mt->id }}">
                                            <input type="hidden" name="text_id[]" value="{{ $mt->id }}">

                                            <div class="form-group">
                                                <label for="rule[]">Regola</label>
                                                <input type="text" class="form-control" name="rule[]" value="{{ $mt->rule }}">
                                                <p class="help-block">Testo che deve apparire nel nome del file per attivare la regola.</p>
                                            </div>
                                            <div class="form-group">
                                                <label for="subject[]">Soggetto</label>
                                                <input type="text" class="form-control" name="subject[]" value="{{ $mt->subject }}">
                                            </div>
                                            <div class="form-group">
                                                <label for="plain[]">Testo mail di notifica assegnazione file</label>
                                                <textarea class="form-control" name="plain[]">{{ $mt->plain }}</textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="light[]">Testo mail di notifica assegnazione file (versione senza allegato)</label>
                                                <textarea class="form-control" name="light[]">{{ $mt->light }}</textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="update[]">Testo mail di notifica aggiornamento file</label>
                                                <textarea class="form-control" name="update[]">{{ $mt->update }}</textarea>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div role="tabpanel" class="tab-pane {{ $index == 0 ? 'active' : '' }}" id="mailtext-new">
                                        <input type="hidden" name="text_id[]" value="new">

                                        <div class="alert alert-info">
                                            Compila questo form per aggiungere una nuova regola.
                                        </div>

                                        <div class="form-group">
                                            <label for="rule[]">Regola</label>
                                            <input type="text" class="form-control" name="rule[]" value="">
                                        </div>
                                        <div class="form-group">
                                            <label for="subject[]">Soggetto</label>
                                            <input type="text" class="form-control" name="subject[]" value="">
                                        </div>
                                        <div class="form-group">
                                            <label for="plain[]">Testo mail di notifica assegnazione file</label>
                                            <textarea class="form-control" name="plain[]" rows="4"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="light[]">Testo mail di notifica assegnazione file (versione senza allegato)</label>
                                            <textarea class="form-control" name="light[]" rows="4"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="update[]">Testo mail di notifica aggiornamento file</label>
                                            <textarea class="form-control" name="update[]" rows="4"></textarea>
                                        </div>
                                    </div>

                                    <?php $mt = App\MailText::where('fallback', true)->first() ?>
                                    <div role="tabpanel" class="tab-pane" id="mailtext-default">
                                        <input type="hidden" name="text_id[]" value="default">
                                        <input type="hidden" name="rule[]" value="">

                                        <div class="alert alert-info">
                                            Questa è la mail inviata di default, se nessuna regola combacia col nome di un file.
                                        </div>

                                        <div class="form-group">
                                            <label for="subject[]">Soggetto</label>
                                            <input type="text" class="form-control" name="subject[]" value="{{ $mt ? $mt->subject : '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="plain[]">Testo mail di notifica assegnazione file</label>
                                            <textarea class="form-control" name="plain[]">{{ $mt ? $mt->plain : '' }}</textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="light[]">Testo mail di notifica assegnazione file (versione senza allegato)</label>
                                            <textarea class="form-control" name="light[]">{{ $mt ? $mt->light : '' }}</textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="update[]">Testo mail di notifica aggiornamento file</label>
                                            <textarea class="form-control" name="update[]">{{ $mt ? $mt->update : '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
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
    @endif
@endrole
@endsection
