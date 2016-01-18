<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use App\User;
use App\Group;
use App\Cloud;

class FileController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->is('admin|groupadmin')) {
            if ($request->hasFile('file')) {
                $filename = $request->file('file')->getClientOriginalName();
                if ($request->file('file')->move(sys_get_temp_dir(), $filename)) {
                    $path = sys_get_temp_dir() . '/' . $filename;

                    if ($request->has('user_id')) {
                        $target = User::findOrFail($request->input('user_id'));
                        $folder = $target->username;
                        $ret = redirect(url('admin/show/' . $target->id));
                    }
                    else if ($request->has('group_id')) {
                        $group = Group::findOrFail($request->input('group_id'));
                        $folder = $group->name;
                        $ret = redirect(url('admin/groups/'));
                    }

                    if ($user->testAccess($folder))
                        Cloud::loadFile($path, $folder, $filename);
                    else
                        abort(403);

                    unlink($path);
                }
            }

            return $ret;
        }
        else {
            abort(403);
        }
    }

    public function show($folder, $filename)
    {
        $user = Auth::user();

        if ($user->testAccess($folder) == true) {
            $contents = Cloud::readFile($folder, $filename);
            $path = tempnam(sys_get_temp_dir(), 'download');
            file_put_contents($path, $contents);
            return response()->download($path, $filename)->deleteFileAfterSend(true);
        }
        else {
            abort(403);
        }
    }

    public function destroy($folder, $filename)
    {
        $user = Auth::user();

        if ($user->is('admin|groupadmin') && $user->testAccess($folder)) {
            Cloud::deleteFile($folder, $filename);
            return redirect(url('admin'));
        }
        else {
            abort(403);
        }
    }
}
