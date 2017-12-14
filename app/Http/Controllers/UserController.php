<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Theme;
use Auth;
use Hash;
use Session;

use App\Tlog;
use App\User;
use App\Group;

class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    public function getIndex()
    {
        return Theme::view('user.profile');
    }

    public function postIndex(Request $request)
    {
        $user = Auth::user();

        $password = $request->input('password');
        $confirm_password = $request->input('confirm_password');

        if ($password != $confirm_password) {
            Session::flash('message', 'Le password non coincidono!');
            Tlog::write('import', "Fallito aggiornamento password utente " . $user->username);
        }
        else {
            $user->password = Hash::make($password);
            $user->save();

            Session::flash('message', 'Password aggiornata');
            Tlog::write('import', "Aggiornata password utente " . $user->username);
        }

        return redirect(url('/user'));
    }

    public function getExport(Request $request)
    {
        $user = Auth::user();

        if ($user->is('admin|groupadmin')) {
            $group_id = $request->input('group');
            if ($user->is('admin') == false && ($user->is('groupadmin') && $group_id != 'none' && $group_id != $user->group_id))
                abort(403);

            $currentgroup = Group::find($group_id);
            if ($currentgroup != null) {
                $users = $currentgroup->users;
            }
            else {
                $users = User::where('group_id', 0)->orderBy('surname', 'asc')->get();
            }

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="export_utenti.csv"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            return Theme::view('user.csv', ['users' => $users]);
        }
        else {
            abort(403);
        }
    }
}
