<?php

class HomeController extends Controller
{
    public function index()
    {
        $user = [
            'name' => 'John Doe'
        ];

        return View::make('home', compact('user'));
    }
}