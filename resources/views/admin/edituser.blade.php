<div class="modal fade" id="{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="{{ $id }}Label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{ $title }}</h4>
            </div>
            <form method="POST" action="<?php if ($user == null) echo url('admin/create'); else echo url('admin/save/' . $user->id) ?>">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Nome</label>
                        <input type="text" class="form-control" name="name" value="<?php if($user != null) echo $user->name ?>">
                    </div>
                    <div class="form-group">
                        <label for="surname">Cognome</label>
                        <input type="text" class="form-control" name="surname" value="<?php if($user != null) echo $user->surname ?>">
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" name="username" value="<?php if($user != null) echo $user->username ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Indirizzo Mail</label>
                        <input type="email" class="form-control" name="email" value="<?php if($user != null) echo $user->email ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" name="password">
                        @if ($user != null)
                        <p class="help-block">Lascia in bianco per non modificare la password esistente</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="group">Gruppo</label>
                        <select class="form-control" name="group">
                            @foreach($groups as $group)
                            <option value="{{ $group->id }}"<?php if ($user != null && $user->group_id == $group->id) echo ' selected="selected"' ?>>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="admin" value="none" checked="checked"<?php if ($user == null || $user->is('admin|groupadmin') == false) echo ' checked="checked"' ?>> Utente
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="admin" value="groupadmin"<?php if ($user != null && $user->is('groupadmin')) echo ' checked="checked"' ?>> Amministratore Gruppo
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="admin" value="admin"<?php if ($user != null && $user->is('admin')) echo ' checked="checked"' ?>> Amministratore Generale
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