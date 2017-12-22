Nome,Cognome,Username,EMail,EMail 2,EMail 3
@foreach($users as $user)
"{{ $user->name }}","{{ $user->surname }}","{{ $user->username }}","{{ $user->email }}","{{ $user->email2 }}","{{ $user->email3 }}"
@endforeach
