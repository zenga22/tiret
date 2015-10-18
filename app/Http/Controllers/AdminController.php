<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use App\User;
use App\Group;
use App\Cloud;
use App\Rule;
use DB;
use Session;
use Hash;
use Theme;
use Mail;

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');

        $user = Auth::user();
        if ($user != null && $user->is('admin') == false && $user->is('groupadmin') == false)
            return redirect(url('/'));
    }

    public function getIndex()
    {
        return Theme::view('admin.panel');
    }

    public function getUsers()
    {
        $user = Auth::user();

        $data['groups'] = Group::get();

        if ($user->is('admin'))
            $data['users'] = User::orderBy('surname', 'asc')->get();
        else if ($user->is('groupadmin'))
            $data['users'] = User::where('group_id', '=', $user->group_id)->orderBy('surname', 'asc')->get();

        return Theme::view('admin.users', $data);
    }

    private function notifyNewUser($user, $password)
    {
        try {
            Mail::send('emails.creation', ['user' => $user, 'password' => $password], function ($m) use ($user) {
                $m->to($user->email, $user->name . ' ' . $user->surname)->subject('nuovo account accesso files');
            });
        }
        catch(\Swift_TransportException $e) {
            Log::info('Failed mail to ' . $user->email);
        }
    }

    private function importing($step, $limit)
    {
        $path = sys_get_temp_dir() . '/' . 'import.csv';
        $contents = file($path);

        $groups = [];
        $dbgroups = Group::get();
        foreach($dbgroups as $g)
            $groups[$g->name] = $g->id;

        for ($i = $step, $iterations = 0; $i < count($contents); $i++) {
            $iterations++;
            if ($iterations >= $limit)
                return $i;

            $row = $contents[$i];
            $data = str_getcsv($row);

            if (count($data) == 1) {
                $mail = $data[0];
                $test = User::where('email', '=', $mail)->first();
                if ($test == null) {
                    Log::info('Missing user ' . $mail);
                }
                else {
                    $u = $test;
                    $password = str_random(10);
                    $u->password = Hash::make($password);
                    $u->save();
                }
            }
            else {
                $username = $data[2];
                $test = User::where('username', '=', $username)->first();
                if ($test != null)
                    continue;

                $u = new User();
                $u->name = $data[0];
                $u->surname = $data[1];
                $u->username = $username;
                $u->email = $data[3];

                if (isset($groups[$data[4]]))
                    $u->group_id = $groups[$data[4]];
                else
                    $u->group_id = -1;

                $password = str_random(10);
                $u->password = Hash::make($password);
                $u->save();

                Cloud::createFolder($u->username);
            }

            $this->notifyNewUser($u, $password);
            usleep(500000);
        }

        return null;
    }

    public function postImport(Request $request)
    {
        if ($request->hasFile('file') && $request->file('file')->move(sys_get_temp_dir(), 'import.csv'))
            $step = $this->importing(0, 10);
        else
            $step = $this->importing($request->input('step'), 100);

        if ($step == null)
            return redirect(url('admin/users'));
        else
            return Theme::view('admin.import', ['step' => $step]);
    }

    public function postCreate(Request $request)
    {
        $username = $request->input('username');
        $test = User::where(DB::raw('LOWER(username)'), '=', strtolower($username))->first();

        if ($test == null) {
            $user = new User();
            $user->name = $request->input('name');
            $user->surname = $request->input('surname');
            $user->username = $username;
            $user->email = $request->input('email');
            $user->group_id = $request->input('group');

            $password = $request->input('password');
            $user->password = Hash::make($password);

            $user->save();

            $role = $request->input('admin');
            if ($role != 'none')
                $u->attachRole($role);

            Cloud::createFolder($username);
            $this->notifyNewUser($user, $password);

            Session::flash('message', 'Utente creato');
        }
        else {
            Session::flash('message', 'Username già esistente, impossibile creare nuovo utente');
        }

        return redirect(url('admin/users'));
    }

    public function getShow($id)
    {
        $user = Auth::user();
        $target = User::findOrFail($id);

        if ($user->testAccess($target->username)) {
            $data['user'] = $target;
            $data['files'] = Cloud::getContents($target->username);
            $data['groups'] = Group::get();
            return Theme::view('admin.display', $data);
        }
        else {
            abort(403);
        }
    }

    public function postSave(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->name = $request->input('name');
        $user->surname = $request->input('surname');
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->group_id = $request->input('group');

        $password = $request->input('password');
        if ($password != '')
            $user->password = Hash::make($password);

        $user->save();
        return redirect(url('admin/show/' . $id));
    }

    public function postStatus(Request $request, $id)
    {
        $user = Auth::user();
        $target = User::findOrFail($id);

        if ($user->testAccess($target->username)) {
            $status = $request->input('status');
            switch($status) {
                case 'enabled':
                    $target->suspended = false;
                    break;
                case 'disabled':
                    $target->suspended = true;
                    break;
            }

            $target->save();
            return redirect(url('admin/show/' . $id));
        }
        else {
            abort(403);
        }
    }

    public function postDelete($id)
    {
        $user = Auth::user();
        $target = User::findOrFail($id);

        if ($user->testAccess($target->username)) {
            Cloud::deleteFolder($target->username);
            $target->delete();
            return redirect(url('admin/users'));
        }
        else {
            abort(403);
        }
    }

    public function getGroups()
    {
        $user = Auth::user();
        $files = array();

        if ($user->is('admin'))
            $groups = Group::get();
        else if ($user->is('groupadmin'))
            $groups = [Group::find($user->group_id)];

        foreach($groups as $group)
            $files[$group->name] = Cloud::getContents($group->name);

        $data['groups'] = $groups;
        $data['files'] = $files;

        return Theme::view('admin.groups', $data);
    }

    public function postGroups(Request $request)
    {
        $user = Auth::user();

        if ($user->is('admin')) {
            $ids = $request->input('ids');
            $names = $request->input('names');

            for ($i = 0; $i < count($ids); $i++) {
                $id = $ids[$i];
                $group = Group::find($id);
                if ($request->has('delete_' . $id)) {
                    Cloud::deleteFolder($group->name);
                    User::where('group_id', '=', $group->id)->update(['group_id' => -1]);
                    $group->delete();
                }
                else {
                    $group->name = $names[$i];
                    $group->save();
                }
            }

            $new = $request->input('newgroup');
            if (empty($new) == false) {
                $group = new Group();
                $group->name = $new;
                $group->save();
            }

            return redirect(url('admin/groups'));
        }
        else {
            abort(403);
        }
    }

    public function getRules()
    {
        $user = Auth::user();

        if ($user->is('admin')) {
            $data['rules'] = Rule::get();
            return Theme::view('admin.rules', $data);
        }
        else {
            abort(403);
        }
    }

    public function postRules(Request $request)
    {
        $user = Auth::user();

        if ($user->is('admin')) {
            $ids = $request->input('ids');
            $expressions = $request->input('expressions');

            for ($i = 0; $i < count($ids); $i++) {
                $id = $ids[$i];
                $rule = Rule::find($id);
                if ($request->has('delete_' . $id)) {
                    $rule->delete();
                }
                else {
                    $rule->expression = $expressions[$i];
                    $rule->save();
                }
            }

            $new = $request->input('newrule');
            if (empty($new) == false) {
                $rule = new Rule();
                $rule->expression = $new;
                $rule->save();
            }

            return redirect(url('admin/rules'));
        }
        else {
            abort(403);
        }
    }

    public function getCount(Request $request)
    {
        $folder = $request->input('folder');
        $files = Cloud::getContents($folder);
        return count($files);
    }
}
