<?php
class Controller
{

// include model file and return its object
    public function model($model)
    {
        if (file_exists('../app/models/' . $model . '.php'))
        {
            require_once '../app/models/' . $model . '.php';
            return new $model();
        }
    }

    // include model file and return its object
    public function view($view , $data = array())
    {
        if (file_exists('../app/views/' . $view . '.php'))
        {
            require_once '../app/views/' . $view . '.php';
        }
    }

    // redirect spcific file
    public function redirect($path)
    {
        $fullPath = HOME.$path;
        header("Location: $fullPath");
    }
}