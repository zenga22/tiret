<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Cloud;

class Mlog extends Model
{
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getDescriptionAttribute()
    {
        switch($this->status) {
            case 'try':
                $icon = 'question-sign';
                $text = 'In attesa';
                break;
            case 'sent':
                $icon = 'ok';
                $text = 'Inviata';
                break;
            case 'fail':
                $icon = 'remove';
                $text = 'Fallita';
                break;
            case 'reschedule':
                $icon = 'time';
                $text = 'Riprovare';
                break;
        }

        return sprintf('<span class="glyphicon glyphicon-%s" aria-hidden="true"></span> %s', $icon, $text);
    }

    public static function registerMessageId($message_id)
    {
        $actual = Mlog::orderBy('id', 'desc')->first();
        $actual->message_id = $message_id;
        $actual->save();
    }

    private static function archiveFilePath($filename)
    {
        $storage = Cloud::mainLocalFolder();
        return sprintf('%s/.archive/%s', $storage, $filename);
    }

    private static function originalFilePath($filename)
    {
        $storage = Cloud::mainLocalFolder();
        return sprintf('%s/%s', $storage, $filename);
    }

    public static function addStatus($user_id, $filename)
    {
        $actual = Mlog::where('user_id', $user_id)->where('filename', $filename)->first();
        if ($actual == null) {
            $actual = new Mlog();
            $actual->user_id = $user_id;
            $actual->filename = $filename;
        }

        $actual->status = 'try';
        $actual->save();

        $current_path = Mlog::originalFilePath($filename);
        $archive_path = Mlog::archiveFilePath($filename);
        copy($current_path, $archive_path);
    }

    public static function updateStatus($message_id, $status)
    {
        $actual = Mlog::where('message_id', $message_id)->first();
        $actual->status = $status;
        $actual->save();

        $filepath = Mlog::archiveFilePath($actual->filename);

        switch($status) {
            case 'sent':
            case 'fail':
                unlink($filepath);
                break;

            case 'reschedule':
                /*
                    Se è già stata caricata un'altra copia del file, quello
                    precedente viene eliminato
                */
                $original = Mlog::originalFilePath($actual->filename);
                if (file_exists($original))
                    unlink($filepath);
                else
                    rename($filepath, $original);

                break;
        }
    }
}
