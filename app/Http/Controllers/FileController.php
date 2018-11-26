<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use Auth;
use Log;
use Mail;
use App\User;
use App\Group;
use App\Document;
use App\Cloud;
use App\Tlog;

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
                    $target = null;
                    $send_mail = false;

                    if ($request->has('user_id')) {
                        $target = User::findOrFail($request->input('user_id'));
                        $folder = $target->username;
                        $ret = redirect(url('admin/show/' . $target->id));
                        $send_mail = env('SEND_MAIL', false);
                    }
                    else if ($request->has('group_id')) {
                        $group = Group::findOrFail($request->input('group_id'));
                        $folder = $group->name;
                        $ret = redirect(url('admin/groups/'));
                        $send_mail = false;
                    }

                    if ($user->testAccess($folder))
                        Cloud::loadFile($path, $folder, $filename);
                    else
                        abort(403);

                    if ($send_mail == true && $target != null) {
                        $target->deliverDocument($path, $filename, false);
                    }

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
        /*
            La funzione deleteFileAfterSend() usata sotto non sembra funzionare
            molto bene, sicché qui per sicurezza provvediamo a rimuovere tutti
            gli eventuali files più vecchi di 24 ore onde evitare di riempire
            a sproposito il disco
        */
        try {
            $existing = glob(sys_get_temp_dir() . 'download*');
            $expiration = time() - (60 * 60 * 24);
            foreach($existing as $e) {
                $info = stat($e);
                if ($info['atime'] < $expiration)
                    @unlink($e);
            }
        }
        catch(\Exception $e) {
            Log::error('Errore rimuovendo vecchi files scaricati');
        }

        $user = Auth::user();

        if ($user->testAccess($folder) == true) {
            $path = Cloud::localPark($folder, $filename);

            $document = Document::where('folder', $folder)->where('filename', $filename)->first();
            if ($document == null) {
                $document = new Document();
                $document->folder = $folder;
                $document->filename = $filename;
                $document->downloaded = true;
                $document->save();
            }
            else {
                $document->downloaded = true;
                $document->save();
            }

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
            Tlog::write('files', 'File ' . $filename . ' rimosso manualmente');
            return redirect(url('admin'));
        }
        else {
            abort(403);
        }
    }
}
