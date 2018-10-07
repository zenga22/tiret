<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MailText extends Model
{
    public function applies($filename)
    {
        if (strpos($filename, $this->rule) !== false)
            return true;
        else
            return false;
    }

    public function getSubject($filename)
    {
        if (strpos($this->subject, '%s') !== null)
            return sprintf($this->subject, $filename);
        else
            return $this->subject;
    }

    public function getMessage($filepaths, $update)
    {
        $filesize = 0;

        foreach($filepaths as $filepath)
            $filesize += filesize($filepath);

        /*
            Attenzione: SES ha un limite di 10MB per gli allegati.
            Per scaramanzia, vengono inviati solo quelli sotto i 7MB.
            Se superano questa soglia, si invia solo una mail di notifica
        */
        if ($filesize > 1024 * 1024 * 7) {
            $mailtext = $this->light;
            $filepath = [];
        }
        else {
            if ($update)
                $mailtext = $this->update;
            else
                $mailtext = $this->plain;
        }

        return [$filepaths, $mailtext];
    }
}
