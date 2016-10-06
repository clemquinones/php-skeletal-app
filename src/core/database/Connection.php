<?php
/**
 *
 * MySQL Query Builder
 *
 * @author     Clemente Quiñones Jr.
 * @copyright  Copyright (c) 2016
 * @since      Version 0.0.7
 * @link       https://github.com/clemquinones/mysql-querybuilder
 */


class Connection
{
    public static function make($config)
    {
        try {
            return new PDO(
                "mysql:host={$config['host']};dbname={$config['name']}",
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