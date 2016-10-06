<?php

class Application
{
    public $config = [];
    public $db;

    public function __construct()
    {
        //Load required configurations
        $this->config['web'] = require 'config/web.php';
        $this->config['database'] = require 'config/database.php';
        $this->config['routes'] = require 'config/routes.php';
    }

    /**
     * Boot the app
     * 
     */
    public function run()
    {
        //Establised the connection
        $this->db = new QueryBuilder(
            Connection::make($this->config['database'])
        );

        //Load defined routes and direct traffic to target controller based on current uri
        Router::load($this->config['routes'])
            ->direct(Request::uri());
    }

    public static function config($key)
    {
        return (new static)->config[$key];
    }
}