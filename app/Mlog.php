<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Log;
use App\Cloud;

class Mlog extends Model
{
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getStringDescriptionAttribute()
    {
        $text = '';

        switch($this->status) {
            case 'try':
                $text = 'In attesa';
                break;
            case 'sent':
                $text = 'Inviata';
                break;
            case 'fail':
                $text = 'Fallita';
                break;
            case 'reschedule':
                $text = 'Riprovare';
                break;
        }

        return $text;
    }

    public function getDescriptionAttribute()
    {
        switch($this->status) {
            case 'try':
                $icon = 'question-sign';
                break;
            case 'sent':
                $icon = 'ok';
                break;
            case 'fail':
                $icon = 'remove';
                break;
            case 'reschedule':
                $icon = 'time';
                break;
        }

        return sprintf('<span class="glyphicon glyphicon-%s" aria-hidden="true"></span> %s', $icon, $this->string_description);
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
        if (env('TRACK_MAIL_STATUS', false) == false)
            return;

        if (is_array($filename)) {
            foreach($filename as $f)
                self::addStatus($user_id, $f);
        }
        else {
            $actual = Mlog::where('user_id', $user_id)->where('filename', $filename)->first();
            if ($actual == null) {
                $actual = new Mlog();
                $actual->user_id = $user_id;
                $actual->filename = $filename;
            }

            $actual->status = 'try';
            $actual->save();

            $current_path = Mlog::originalFilePath($filename);
            if (file_exists($current_path)) {
                $archive_path = Mlog::archiveFilePath($filename);
                copy($current_path, $archive_path);
            }
        }
    }

    public static function updateStatus($filename, $status)
    {
        if (env('TRACK_MAIL_STATUS', false) == false)
            return;

        if (is_array($filename)) {
            foreach($filename as $f)
                self::updateStatus($f, $status);
        }
        else {
            $actual = Mlog::where('filename', $filename)->first();
            if ($actual == null) {
                Log::error('Aggiornamento di messaggio non noto: ' . $message_id);
                return;
            }

            Log::debug(sprintf('Aggiorno stato messaggio %d / %s: %s', $actual->id, $actual->filename, $status));
            $actual->status = $status;
            $actual->save();

            $filepath = Mlog::archiveFilePath($actual->filename);

            switch($status) {
                case 'sent':
                case 'fail':
                    if (file_exists($filepath))
                        unlink($filepath);
                    else
                        Log::error('Aggiornamento di messaggio, file non trovato: ' . $filepath);

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

    public function copyFileBack()
    {
        $original = Mlog::originalFilePath($this->filename);
        Cloud::localPark($this->user->username, $this->filename, $original);
    }
}
