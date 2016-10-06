<?php

class Request
{
    /**
     * Get the current request uri
     * 
     */
    public static function uri()
    {
        return trim($_SERVER['REQUEST_URI'], '/');
    }
}