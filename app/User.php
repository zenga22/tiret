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
use Log;

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

    public function deliverDocument($filepath, $filename, $update)
    {
        $user = $this;

        Mlog::addStatus($this->id, $filename);
        $found = false;
        $mailtext = '';

        foreach(MailText::where('fallback', false)->get() as $text) {
            if ($text->applies($filename)) {
                list($filepath, $mailtext) = $text->getMessage($filepath, $update);
                $found = true;
                break;
            }
        }

        if ($found == false) {
            $text = MailText::where('fallback', true)->first();
            if ($text) {
                list($filepath, $mailtext) = $text->getMessage($filepath, $update);
            }
            else {
                Log::error('Testo mail di default non definito!');
            }
        }

        if (empty(trim($mailtext))) {
            Log::error('Testo della mail vuoto!');
        }

        $mailtext .= "\n\n" . $user->group->signature;

        Mail::send('emails.notify', ['text' => $mailtext], function ($m) use ($user, $text, $filepath, $filename) {
            $m->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $m->to($user->email, $user->name . ' ' . $user->surname);
            $m->subject($text ? $text->getSubject($filename) : '');

            if (empty($user->email2) == false)
                $m->cc($user->email2);
            if (empty($user->email3) == false)
                $m->cc($user->email3);

            if($filepath != null)
                $m->attach($filepath, ['as' => $filename]);

            if(!empty($user->group->email))
                $m->replyTo($user->group->email);

            /*
                Purtroppo non è possibile (o comunque è molto scomodo) intercettare
                l'ID della mail generato da SES, in modo da poi matchare la notifica
                in arrivo da SNS.
                Sicché qui aggiungo come header della mail il nome del file
                trattato, per poter identificare e trattare la risposta.
                https://laracasts.com/discuss/channels/laravel/mail-and-the-message-id
            */
            $m->getSwiftMessage()->getHeaders()->addTextHeader('X-Tiret-Filename', $filename);
        });
    }
}
