<p>
    Gentile {{ $user->name }} {{ $user->surname }},
</p>

<p>
    è stato creato per lei un nuovo account su {{ url('/') }} con cui potrà accedere ai suoi files.
</p>

<p>
    Le credenziali per accedere sono<br/>
    username: {{ $user->username }}<br/>
    password: {{ $password }}<br/>
</p>
