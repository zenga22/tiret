<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    protected $except = [
        /*
            Path per le notifiche SNS dello stato delle mail
        */
        '/mail/status'
    ];
}
