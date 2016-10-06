<?php

//Define the routes
return [
    ''             => 'HomeController@index',
    'login'        => 'AuthController@login',
    'logout'       => 'AuthController@logout',
    'users'        => 'UsersController@index',
    'registration' => 'UsersController@register',
];