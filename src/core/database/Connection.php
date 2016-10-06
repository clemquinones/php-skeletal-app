<?php

class Connection
{
    /**
     * Make a PDO connection.
     * 
     * @return PDO
     */
    public static function make($config)
    {
        try {
            return new PDO(
                'mysql:host='.$config['host'].';dbname='.$config['name'],
                $config['user'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit;
        }
    }

}