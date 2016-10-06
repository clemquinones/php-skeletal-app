<?php

class View
{
    public static function make($template, $variables = null)
    {
        //Load the web configuration
        $web = Application::config('web');

        //Extract the variables
        if (! is_null($variables)) {
            extract($variables);            
        }

        //Normalize paths
        $template = rtrim($template, '.php') . '.php';
        $viewsPath = $web['source_path'] .
                     trim($web['views_path'], '/').'/';

        //Load the view
        require $viewsPath . $template;
    }
}