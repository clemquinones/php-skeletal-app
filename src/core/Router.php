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

        //Load the page error handler
        $this->routes = array_merge($this->routes, [
            'error_404' => $this->app->config['web']['error_404']
        ]);

        //Load abstract Controller
        require $this->controllersPath . 'Controller.php';
    }


    /**
     * Defined the routes
     * 
     * @param array $routes
     * @return Router
     */
    public static function load(array $routes)
    {
        $router = new static;
        $router->routes = array_merge($router->routes, $routes);

        return $router;
    }


    /**
     * Load the target controller's method
     * 
     * @param string $uri
     */
    public function direct($uri)
    {
        //Load the target Controller
        if (array_key_exists($uri, $this->routes)) {
            return $this->callClassMethod($this->routes[$uri]);
        }

        //Show a page not found.
        return $this->error404();
    }


    /**
     * Execute the controller's method
     * 
     * @param string $target
     */
    protected function callClassMethod($target)
    {
        list($class, $method) = explode('@', $target);

        require $this->controllersPath . $class . '.php';

        $controller = new $class;

        return $controller->{$method}();
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