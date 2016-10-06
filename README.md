#A simple skeletal app made by PHP.


#create a controller:
    src/controllers/TasksController.php

#define a route (src/core/config/routes.php):
    'tasks' => 'TasksController@index',
    'tasks/create' => 'TasksController@create',

#load a view:
class TasksController
{
    public function index()
    {
        $tasks = [];

        return View::make('tasks', compact('tasks'));
    }
}

#available helpers:
redirect(<uri>)
dd(<var>)
d(<var)