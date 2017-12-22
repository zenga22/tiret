<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Bican\Roles\Traits\HasRoleAndPermission;
use Bican\Roles\Contracts\HasRoleAndPermission as HasRoleAndPermissionContract;

use Storage;
use Mail;

use App\Group;
use App\Mlog;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract, HasRoleAndPermissionContract
{
    use Authenticatable, CanResetPassword, HasRoleAndPermission;

    protected $table = 'users';
    protected $fillable = ['name', 'surname', 'username', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    public function group()
    {
        return $this->belongsTo('App\Group');
    }

    public function getEmailsAttribute()
    {
        $ret = [];

        if (!empty($this->email))
            $ret[] = $this->email;
        if (!empty($this->email2))
            $ret[] = $this->email2;
        if (!empty($this->email3))
            $ret[] = $this->email3;

        return $ret;
    }

    public function testAccess($folder)
    {
        if ($this->username == $folder)
            return true;

        if ($this->group->name == $folder)
            return true;

        if ($this->is('admin'))
            return true;

        if ($this->is('groupadmin')) {
            $u = User::where('username', '=', $folder)->first();
            if ($u != null)
                if ($u->group->name == $this->group->name)
                    return true;
        }

        return false;
    }

    private function prepareMail($m, $filename, $filepath)
    {
        $m->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        $m->to($this->email, $this->name . ' ' . $this->surname);
        $m->subject('nuovo documento disponibile: ' . $filename);

        if (empty($this->email2) == false)
            $m->cc($this->email2);
        if (empty($this->email3) == false)
            $m->cc($this->email3);

        if($filepath != null)
            $m->attach($filepath, ['as' => $filename]);

        if(!empty($this->group->email))
            $m->replyTo($this->group->email);

        /*
            Purtroppo non è possibile (o comunque è molto scomodo) intercettare
            l'ID della mail generato da SES, in modo da poi matchare la notifica
            in arrivo da SNS.
            Sicché qui aggiungo come header della mail il nome del file
            trattato, per poter identificare e trattare la risposta.
            https://laracasts.com/discuss/channels/laravel/mail-and-the-message-id
        */
        $m->getSwiftMessage()->getHeaders()->addTextHeader('X-Tiret-Filename', $filename);

        return $m;
    }

    public function deliverDocument($filepath, $filename, $update)
    {
        $filesize = filesize($filepath);
        $user = $this;

        Mlog::addStatus($this->id, $filename);

        /*
            Attenzione: SES ha un limite di 10MB per gli allegati.
            Per scaramanzia, vengono inviati solo quelli sotto i 9MB.
            Se superano questa soglia, si invia solo una mail di notifica
        */
        if ($filesize > 1024 * 1024 * 9) {
            Mail::send('emails.notify', ['text' => $user->group->lightmailtext], function ($m) use ($user, $filename) {
                $user->prepareMail($m, $filename, null);
            });
        }
        else {
            if ($update)
                $mailtext = $user->group->updatemailtext;
            else
                $mailtext = $user->group->mailtext;

            Mail::send('emails.notify', ['text' => $mailtext], function ($m) use ($user, $filepath, $filename) {
                $user->prepareMail($m, $filename, $filepath);
            });
        }
    }
}
