<?php

class PageErrorController extends Controller
{
    public function error404()
    {
        echo '404 Page Not Found.';
        exit;
    }

    
}