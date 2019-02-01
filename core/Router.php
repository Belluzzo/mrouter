<?php

namespace Core;

use Exception;
use ReflectionMethod;

class Router
{
    /**
     * @var string
     */
    private $uri;
    /**
     * @var string
     */
    private $method;
    /**
     * @var array
     */
    private $routes = [];
    /**
     * @var array
     */
    private $parameters = [];
    /**
     * Request
     */
    private $request;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->request = new Request();
    }

    /**
     * @param $pattern
     * @param $callback
     * @param $method
     */
    public function addRoute($pattern, $callback, $method)
    {
        $this->routes[] = [
          'pattern' => $pattern,
          'callback' => $callback,
          'method' => $method
        ];
    }

    /**
     *
     */
    public function getUri()
    {
       return $this->uri;
    }

    /**
     * @param $parameter
     * @param $value
     */
    private function setParameter($parameter, $value)
    {
        $parameter = str_replace(['{', ':', '}'], ['','',''], $parameter);
        $this->parameters[$parameter] = $value;
    }

    /**
     * @param $pattern
     * @return string
     */
    private function resolvePlaceHolder($pattern)
    {
        if (!substr_count($pattern, '{:')) {
            return $pattern;
        }

        $pattern_pieces = array_values(array_filter(explode('/', $pattern)));
        $uri_pieces = array_values(array_filter(explode('/', $this->uri)));

        if (count($pattern_pieces) !== count($uri_pieces)) {
            return $pattern;
        }

        for ($i = 0; $i < count($pattern_pieces); $i++) {
            if (preg_match("/\{\:[a-zA-Z]+\}/", $pattern_pieces[$i])) {
                $this->setParameter($pattern_pieces[$i], $uri_pieces[$i]);
                $pattern_pieces[$i] = $uri_pieces[$i];
            }
        }

        return "/" . implode('/', $pattern_pieces);
    }

    /**
     * @param $action
     * @return bool|string
     */
    private function getController($action)
    {
        return substr($action, 0, strpos($action, '@'));
    }

    /**
     * @param $action
     * @return bool|string
     */
    private function getMethod($action)
    {
        return substr($action, strpos($action, '@') + 1, strlen($action));
    }

    /**
     * @param $action
     * @return mixed
     * @throws \ReflectionException
     */
    public function call($action)
    {
        if (is_callable($action)) {
            return call_user_func($action);
        }

        $controller = $this->getController($action);
        $method = $this->getMethod($action);

        $controller = new $controller();
        $reflectionMethod = new ReflectionMethod($controller, $method);
        $parameters = $reflectionMethod->getParameters();

        $args = [];

        foreach ($parameters as $parameter) {
            $arg = $parameter->getName();

            if (isset($this->parameters[$arg])) {
                $args[] = $this->parameters[$arg];
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
                continue;
            }

            if (!$parameter->hasType()) {
                $args[] = null;
                continue;
            }

            $type = $parameter->getClass()->getName();

            if (class_exists($type)) {
                $args[] = new $type;
                continue;
            }

            $args[] = null;
        }

        return $reflectionMethod->invokeArgs($controller, $args);
    }

    /**
     * @return bool|string
     * @throws Exception
     */
    public function run()
    {
        if (empty($this->routes)) {
            throw new Exception('No route was loaded.');
        }

        foreach ($this->routes as $route) {
            $pattern = $route['pattern'];
            $callback = $route['callback'];
            $method = $route['method'];

            $pattern = $this->resolvePlaceHolder($pattern);

            if ($pattern === $this->uri && strtoupper($method) === strtoupper($this->method)) {
                $this->call($callback);
            }
        }
    }
}