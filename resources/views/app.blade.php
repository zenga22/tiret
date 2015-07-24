<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php if(isset($pagetitle)) echo $pagetitle ?></title>
        <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    </head>
    <body>
        @yield('content')

        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
        <script src="{{ url('js/app.js') }}"></script>
    </body>
</html>