<?php

namespace Core;

class App
{
    /**
     * @var Router
     */
    private $router;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->router = new Router();
    }

    public function on($pattern, $callback, $method)
    {
        $this->router->addRoute($pattern, $callback, $method);
    }

    /**
     * @param $pattern
     * @param $callback
     */
    public function get($pattern, $callback)
    {
        $this->on($pattern, $callback, 'GET');
    }

    /**
     * @param $pattern
     * @param $callback
     */
    public function post($pattern, $callback)
    {
        $this->on($pattern, $callback, 'POST');
    }

    /**
     * @return bool|string
     * @throws \Exception
     */
    public function run()
    {
        return $this->router->run();
    }
}