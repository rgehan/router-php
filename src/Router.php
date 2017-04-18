<?php

namespace rgehan\RouterPHP;

use rgehan\RouterPHP\Exceptions\NoSuchRouteException;

class Router
{
    private static $controllerNamespace = "";

    private static $routes = [
        'GET' => [],
        'POST' => [],
        'UPDATE' => [],
        'DELETE' => [],
    ];

    private static $globalParams = [];

    /**
     * Register a GET route
     */
    public static function get($route, $target)
    {
        self::registerRoute($route, $target, 'GET');
    }

    /**
     * Register a POST route
     */
    public static function post($route, $target)
    {
        self::registerRoute($route, $target, 'POST');
    }

    /**
     * Register a DELETE route
     */
    public static function delete($route, $target)
    {
        self::registerRoute($route, $target, 'DELETE');
    }

    /**
     * Register a UPDATE route
     */
    public static function update($route, $target)
    {
        self::registerRoute($route, $target, 'UPDATE');
    }

    /**
     * Register any kind of route
     */
    private static function registerRoute($route, $target, $verb)
    {
        // Cleanup the route
        $route = self::cleanupPath($route);

        // Gets the controller/method name
        $targets = self::extractTargets($target);
        $controllerName = $targets['controller'];
        $methodName = $targets['method'];

        // Checks if the controller exists
        if(!class_exists($controllerName))
            throw new InvalidArgumentException("The controller '$controllerName' doesn't exist!");

        // Checks if the method exists in the controller
        if(!method_exists($controllerName, $methodName))
            throw new InvalidArgumentException("The method '$methodName' doesn't exist in the controller!");

        // Everything is fine, we can save the route
        self::$routes[$verb][$route] = [
            'controller' => $controllerName,
            'method' => $methodName,
        ];
    }

    /**
     * Gets the path from a URL, starting with a slash, without consecutive
     * slashes nor GET parameters
     */
    private static function cleanupPath($path)
    {
        // Eliminates GET arguments
        $path = explode('?', $path)[0];

        // Gets all path elements 
        $pathElements = explode('/', $path);

        // Eliminates empty elements
        $pathElements = array_filter($pathElements, function($el) {
            return $el != "";
        });

        return '/' . implode('/', $pathElements);
    }

    /**
     * Extracts a controller and a method name from a 'target string'
     * MyController@myMethod => ['controller' => 'MyController', 'method' => 'myMethod']
     */
    private static function extractTargets($targetStr)
    {
        $tokens = explode('@', $targetStr);

        // If the target string is improperly formatted
        if(count($tokens) != 2)
            throw new InvalidArgumentException("You must specify a controller and a method");

        return [
            'controller' => self::$controllerNamespace . $tokens[0],
            'method' => $tokens[1],
        ];
    }

    /**
     * Sets up the base namespace for our controllers
     */
    public static function setControllerNamespace($baseNamespace)
    {
        self::$controllerNamespace = $baseNamespace;
    }

    /**
     * Show a human-readable route table
     */
    public static function showRoutes()
    {
        echo "<table>";
        foreach(self::$routes as $verb => $routes)
        {
            foreach($routes as $route => $targets)
            {
                $controller = $targets['controller'];
                $method = $targets['method'];

                echo "<tr><td>$verb</td><td>$route</td><td>$controller</td><td>$method</td></tr>";                
            }
        }
        echo "</table>";
    }

    /**
     * Sets the parameters that are going to be set on all routes
     */
    public static function setRoutesGlobalParameters($params)
    {
        self::$globalParams = $params;
    }

    /**
     * Tries to dispatch a request to the proper controller and its method
     * @return [type] [description]
     */
    public static function dispatch()
    {
        // The HTTP verb used
        $verb = $_SERVER['REQUEST_METHOD'];

        // The route we're trying to access
        $route = $_SERVER['REQUEST_URI'];
        $route = self::cleanupPath($route);
        
        // If there is no matching route
        if(!isset(self::$routes[$verb][$route]))
            throw new NoSuchRouteException("Couldn't find any matching route!");

        // Gets the route data
        $routeData = self::$routes[$verb][$route];
        
        // Extracts the controller and its method names
        $method = $routeData['method'];
        $controller = new $routeData['controller'];

        // Calls the method and pass the global parameters
        call_user_func_array([$controller, $method], self::$globalParams);
    }
}