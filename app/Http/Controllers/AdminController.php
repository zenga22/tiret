<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Bican\Roles\Models\Role;

use Auth;
use DB;
use Session;
use Hash;
use Theme;
use Mail;
use Log;

use App\User;
use App\Group;
use App\Cloud;
use App\Rule;
use App\Tlog;
use App\Mlog;

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

    public function getUsers(Request $request)
    {
        $user = Auth::user();

        $data['groups'] = Group::get();

        if ($request->has('group')) {
            $group_id = $request->input('group');
            if ($user->is('admin') == false && ($user->is('groupadmin') && $group_id != 'none' && $group_id != $user->group_id))
                abort(403);

            $currentgroup = Group::find($group_id);
            if ($currentgroup != null) {
                $data['currentgroup'] = $currentgroup;
                $data['users'] = $currentgroup->users;
            }
            else {
                $data['currentgroup'] = null;
                $data['users'] = User::where('group_id', 0)->orderBy('surname', 'asc')->get();
            }

            return Theme::view('admin.users', $data);
        }
        else {
            if ($user->is('groupadmin'))
                $data['groups'] = [Group::find($user->group_id)];
            else if ($user->is('admin'))
                $data['groups'] = Group::get();

            return Theme::view('admin.gusers', $data);
        }
    }

    private function notifyNewUser($user, $password)
    {
        foreach($user->emails as $mail) {
            try {
                Mail::send('emails.creation', ['user' => $user, 'password' => $password], function ($m) use ($user, $mail) {
                    $m->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    $m->to($mail, $user->name . ' ' . $user->surname)->subject('nuovo account accesso files');
                });
            }
            catch(\Exception $e) {
                Log::info('Failed notification mail to ' . $mail . ': ' . $e->getMessage());
            }
        }
    }

    private function notifyNewPassword($user, $password)
    {
        foreach($user->emails as $mail) {
            try {
                Mail::send('emails.changepassword', ['user' => $user, 'password' => $password], function ($m) use ($user, $mail) {
                    $m->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    $m->to($mail, $user->name . ' ' . $user->surname)->subject('modificato account accesso files');
                });
            }
            catch(\Exception $e) {
                Log::info('Failed notification mail to ' . $mail . ': ' . $e->getMessage());
            }
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

                continue;
            }
            else {
                $username = trim($data[2]);
                $suspended = (intval(trim($data[4])) == 0);
                $email = strtolower(trim($data[3]));

                $test = User::where('username', '=', $username)->first();
                if ($test != null) {
                    $changed = false;

                    if ($test->suspended != $suspended) {
                        $test->suspended = $suspended;
                        $changed = true;
                    }

                    if (strtolower($test->email) != $email && strtolower($test->email2) != $email && strtolower($test->email3) != $email) {
                        if (empty($test->email))
                            $test->email = $email;
                        else if (empty($test->email2))
                            $test->email2 = $email;
                        else if (empty($test->email3))
                            $test->email3 = $email;
                        $changed = true;
                    }

                    if ($changed == true) {
                        Tlog::write('import', "Aggiornato utente $username");
                        $test->save();
                    }
                }
                else {
                    $u = new User();
                    $u->name = trim($data[0]);
                    $u->surname = trim($data[1]);
                    $u->username = $username;
                    $u->email = $email;
                    $u->suspended = $suspended;

                    if (isset($groups[$data[5]]))
                        $u->group_id = $groups[$data[5]];
                    else
                        $u->group_id = -1;

                    $password = str_random(10);
                    $u->password = Hash::make($password);
                    $u->save();

                    Cloud::createFolder($u->username);
                    Tlog::write('import', "Creato nuovo utente $username");

                    $this->notifyNewUser($u, $password);
                    sleep(1);
                }
            }
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

        $user = User::where(DB::raw('LOWER(username)'), '=', strtolower($username))->first();
        if ($user == null)
            $user = new User();

        $user->name = trim($request->input('name'));
        $user->surname = trim($request->input('surname'));
        $user->username = trim($username);
        $user->email = trim($request->input('email'));
        $user->email2 = trim($request->input('email2'));
        $user->email3 = trim($request->input('email3'));
        $user->group_id = $request->input('group');

        $password = $request->input('password');
        $user->password = Hash::make($password);

        $user->save();

        $role = $request->input('admin');
        if ($role != 'none')
            $user->attachRole(Role::where('slug', '=', $role)->first());

        Cloud::createFolder($username);
        $this->notifyNewUser($user, $password);

        Session::flash('message', 'Utente creato');

        return redirect(url('admin/users'));
    }

    public function getShow($id)
    {
        $user = Auth::user();
        $target = User::findOrFail($id);

        if ($user->testAccess($target->username)) {
            $data['user'] = $target;
            $data['files'] = Cloud::getContents($target->username, true);
            $data['groups'] = Group::get();
            return Theme::view('admin.display', $data);
        }
        else {
            return redirect(url('admin/users/'));
        }
    }

    public function postSave(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $just_populated = ($user->group == null);

        $user->name = trim($request->input('name'));
        $user->surname = trim($request->input('surname'));
        $user->username = trim($request->input('username'));
        $user->email = trim($request->input('email'));
        $user->email2 = trim($request->input('email2'));
        $user->email3 = trim($request->input('email3'));
        $user->group_id = $request->input('group');

        $password = $request->input('password');
        if (!empty($password))
            $user->password = Hash::make($password);

        $role = $request->input('admin');

        if ($user->is('admin'))
            $current_role = 'admin';
        else if ($user->is('groupadmin'))
            $current_role = 'groupadmin';
        else
            $current_role = 'none';

        if ($role != $current_role) {
            if ($current_role != 'none')
                $user->detachRole(Role::where('slug', '=', $current_role)->first());
            if ($role != 'none')
                $user->attachRole(Role::where('slug', '=', $role)->first());
        }

        $user->save();

        if ($just_populated && !empty($password)) {
            $user = $user->fresh();
            $this->notifyNewUser($user, $password);
            Tlog::write('import', "Popolata anagrafica utente " . $user->username . ": inviata mail con credenziali");

            $files = Cloud::getContents($user->username);
            foreach($files as $filename) {
                $filename = basename($filename);
                $filepath = Cloud::localPark($user->username, $filename);
                $user->deliverDocument($filepath, $filename, false);
                unlink($filepath);
            }
        }
        else if (!empty($password)) {
            $this->notifyNewPassword($user, $password);
        }

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
            $files[$group->name] = Cloud::getContents($group->name, true);

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
            $emails = $request->input('emails');
            $mailtexts = $request->input('mailtext', []);
            $lightmailtexts = $request->input('lightmailtext', []);
            $updatemailtexts = $request->input('updatedmailtext', []);
            $messages = $request->input('message', []);

            for ($i = 0; $i < count($ids); $i++) {
                $id = $ids[$i];
                $group = Group::find($id);
                if ($request->has('delete_' . $id)) {
                    Cloud::deleteFolder($group->name);
                    User::where('group_id', '=', $group->id)->update(['group_id' => -1]);
                    $group->delete();
                }
                else {
                    $group->name = trim($names[$i]);
                    $group->email = trim($emails[$i]);
                    $group->mailtext = isset($mailtexts[$i]) ? $mailtexts[$i] : '';
                    $group->lightmailtext = isset($lightmailtexts[$i]) ? $lightmailtexts[$i] : '';
                    $group->updatedmailtext = isset($updatemailtexts[$i]) ? $updatemailtexts[$i] : '';
                    $group->message = isset($messages[$i]) ? $messages[$i] : '';
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

    public function getReports(Request $request)
    {
        if ($request->has('section')) {
            $section = $request->input('section');

            if ($request->has('download')) {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="export_utenti.csv"');
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');

                if ($section == 'mail') {
                    echo join(',', ['Data', 'Utente', 'Mail', 'Documento', 'Stato']) . "\n";
                    $index = 0;

                    while (true) {
                        $data = Mlog::orderBy('created_at', 'asc')->take(100)->offset($index * 100)->get();
                        if($data->isEmpty())
                            break;

                        foreach($data as $d) {
                            $row = [
                                $d->created_at,
                                $d->user->username,
                                join(', ', $d->user->emails),
                                $d->filename,
                                $d->string_description
                            ];

                            echo '"' . join('","', $row) . '"' . "\n";
                        }

                        $index++;
                    }
                }
                else {
                    echo join(',', ['Data', 'Messaggio']) . "\n";
                    $index = 0;

                    while (true) {
                        $data = Tlog::where('section', $section)->orderBy('created_at', 'asc')->take(100)->offset($index * 100)->get();
                        if($data->isEmpty())
                            break;

                        foreach($data as $d) {
                            $row = [
                                $d->created_at,
                                $d->message
                            ];

                            echo '"' . join('","', $row) . '"' . "\n";
                        }

                        $index++;
                    }
                }

                exit();
            }
            else {
                $expiring_date = date('Y-m-d G:i:s', strtotime('-1 years'));
                $month = $request->input('month', date('m'));

                if ($section == 'mail') {
                    Mlog::where('created_at', '<', $expiring_date)->delete();
                    $data['logs'] = Mlog::where(DB::raw('MONTH(created_at)'), $month)->orderBy('created_at', 'desc')->get();
                }
                else {
                    Tlog::where('created_at', '<', $expiring_date)->delete();
                    $data['logs'] = Tlog::where('section', $section)->where(DB::raw('MONTH(created_at)'), $month)->orderBy('created_at', 'desc')->get();
                }

                $data['show_menu'] = false;
                $data['month'] = $month;
                $data['section'] = $section;
            }
        }
        else {
            $data['show_menu'] = true;
            $data['logs'] = [];
        }

        return Theme::view('admin.reports', $data);
    }

    public function getCount(Request $request, $folder)
    {
        $files = Cloud::getContents($folder, false);
        header('Folder-ID: ' . $folder);
        return count($files);
    }
}
