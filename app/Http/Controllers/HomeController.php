<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use App\Cloud;
use Theme;

class HomeController extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->suspended)
            return redirect(url('auth/logout'));

        if ($user->is('admin|groupadmin')) {
            return redirect(url('admin'));
        }
        else {
            $data['user'] = $user;
            $data['files'] = Cloud::getContents($user->username);
            $data['groupfiles'] = Cloud::getContents($user->group->name);
            return Theme::view('home', $data);
        }
    }

}
