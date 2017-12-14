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
}
