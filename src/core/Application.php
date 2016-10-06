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

        $this->loadDatabase();
    }


    public function loadDatabase()
    {
        if ($this->db) return;

        //Establised the connection
        $this->db = new QueryBuilder(
            Connection::make($this->config['database'])
        );
    }


    /**
     * Boot the app
     * 
     */
    public static function run()
    {
       //Direct traffic to target controller based on current uri
        Router::direct(Request::uri());
    }


    public static function config($key)
    {
        return (new static)->config[$key];
    }
}