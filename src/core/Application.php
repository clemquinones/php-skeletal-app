<?php

class Application
{
    public $config = [];

    public function __construct()
    {
        $this->config['web'] = require 'config/web.php';
        $this->config['database'] = require 'config/database.php';
        $this->config['routes'] = require 'config/routes.php';
    }

    public function run()
    {
        Router::load($this->config['routes'])
            ->direct(Request::uri());
    }

    public static function config($key)
    {
        return (new static)->config[$key];
    }
}