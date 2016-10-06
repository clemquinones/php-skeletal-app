<?php

class Router
{
    protected $routes = [];
    protected $app;
    protected $controllersPath;

    public function __construct()
    {
        $this->app = new Application;

        //Complete the controllers directory path
        $this->controllersPath  = $this->app->config['web']['source_path'] .
                                  trim($this->app->config['web']['controllers_path'], '/').'/';

        //Load routes
        $this->routes = array_merge(
            ['error_404' => $this->app->config['web']['error_404']],
            $this->app->config['routes']
        );
    }

    /**
     * Load the target controller's method
     * 
     * @param string $uri
     */
    public static function direct($uri)
    {
        $router = new static;

        //Load the target Controller
        if (array_key_exists($uri, $router->routes)) {
            return $router->callClassMethod($router->routes[$uri]);
        }

        //Show a page not found.
        return $router->error404();
    }


    /**
     * Execute the controller's method
     * 
     * @param string $target
     */
    protected function callClassMethod($target)
    {
        list($class, $method) = explode('@', $target);

        //Load parent Controller
        if (! class_exists('Controller')) {
            require $this->controllersPath . 'Controller.php';            
        }

        if (! class_exists($class)) {
            require $this->controllersPath . $class . '.php';
            return (new $class($this->app))->{$method}();
        }

        throw new Exception('Controller not found');
    }


    /**
     * Load the default 404 page error route
     * 
     */
    public function error404()
    {
        return $this->callClassMethod($this->app->config['web']['error_404']);
    }
}