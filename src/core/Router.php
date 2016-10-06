<?php

class Router
{
    protected $routes = [];
    protected $controllersPath;

    public function __construct()
    {
        $web = Application::config('web');

        $this->controllersPath = $web['source_path'] . trim($web['controllers_path'], '/').'/';

        //Check if the page error handler controll exists
        if (! file_exists($this->controllersPath . 'PageErrorController.php')) {
            echo 'PageErrorController does not exists.';
            exit;
        }

        //Load the page error handler
        $this->routes = array_merge($this->routes, ['error_404' => 'PageErrorController@error404']);

        //Load abstract Controller
        require $this->controllersPath . 'Controller.php';
    }

    public static function load($routes)
    {
        $router = new static;
        $router->routes = array_merge($router->routes, $routes);

        return $router;
    }

    public function direct($uri)
    {
        //Load the target Controller
        // var_dump($this->routes);exit;
        if (array_key_exists($uri, $this->routes)) {
            
            list($class, $method) = explode('@', $this->routes[$uri]);

            require $this->controllersPath . $class . '.php';

            $controller = new $class;
            $controller->{$method}();

            return $this;
        }

        //Show a page not found.
        $this->error404();
    }

    public function error404()
    {
        $this->direct('error_404');
    }
}