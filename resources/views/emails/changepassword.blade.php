<p>
    Gentile {{ $user->name }} {{ $user->surname }},
</p>

<p>
    è stata modificata la password del suo account su {{ url('/') }}
</p>

<p>
    Le nuove credenziali per accedere sono<br/>
    username: {{ $user->username }}<br/>
    password: {{ $password }}<br/>
</p>
