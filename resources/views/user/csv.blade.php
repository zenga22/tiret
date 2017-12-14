Nome,Cognome,Username
@foreach($users as $user)
"{{ $user->name }}","{{ $user->surname }}","{{ $user->username }}"
@endforeach
