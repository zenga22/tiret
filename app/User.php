<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Bican\Roles\Traits\HasRoleAndPermission;
use Bican\Roles\Contracts\HasRoleAndPermission as HasRoleAndPermissionContract;

use Mail;

use App\Group;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract, HasRoleAndPermissionContract
{
    use Authenticatable, CanResetPassword, HasRoleAndPermission;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'surname', 'username', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
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
        $filesize = filesize($filepath);
        $user = $this;

        /*
            Attenzione: SES ha un limite di 10MB per gli allegati. In tal caso
            si manda una mail di notifica senza il file allegato
        */
        if ($filesize > 1024 * 1024 * 10) {
            Mail::send('emails.notify', ['text' => $user->group->lightmailtext], function ($m) use ($user, $filename) {
                $m->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                $m->to($user->email, $user->name . ' ' . $user->surname)->subject('nuovo documento disponibile: ' . $filename);

                if (empty($user->email2) == false)
                    $m->cc($user->email2);
                if (empty($user->email3) == false)
                    $m->cc($user->email3);
            });
        }
        else {
            if ($update)
                $mailtext = $user->group->updatemailtext;
            else
                $mailtext = $user->group->mailtext;

            Mail::send('emails.notify', ['text' => $mailtext], function ($m) use ($user, $filepath, $filename) {
                $m->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                $m->to($user->email, $user->name . ' ' . $user->surname)->subject('nuovo documento disponibile: ' . $filename);

                if (empty($user->email2) == false)
                    $m->cc($user->email2);
                if (empty($user->email3) == false)
                    $m->cc($user->email3);

                $m->attach($filepath, ['as' => $filename]);
            });
        }
    }
}
