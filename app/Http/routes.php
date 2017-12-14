<?php

Route::get('/', 'HomeController@index');
Route::get('/home', function() {
    return redirect(url('/'));
});

Route::controllers([
    'auth' => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
    'admin' => 'AdminController',
    'user' => 'UserController'
]);

Route::get('file/delete/{folder}/{name}', 'FileController@destroy');
Route::get('file/{folder}/{name}', 'FileController@show');
Route::resource('file', 'FileController');
