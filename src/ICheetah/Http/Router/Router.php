<?php

namespace ICheetah\Http\Router;
use ICheetah\Tools\Arr;
use ICheetah\Foundation\Exceptions;

class Router
{
    
    /**
     *
     * @var array
     */
    protected $routes = array();
    
    protected $uri = null;
    
    protected $groups = array();
    
    protected $currentRoute = null;

    public function __construct()
    {
        
    }

    /**
     * Executes added route patterns of uri and runs assigned callback
     * @return Response
     * @throws Exceptions\NoRouterEngine
     */
    public function run()
    {
        if (is_null($this->getEngine())){
            throw new Exceptions\NoRouterEngine();
        }
        
        call_user_method("route", $this->getEngine());        
    }
    
    
    public function firstMatch($pattern, $route = null)
    {
        $retVal = null;
        $route = $route !== null? $route : $this->getRoute();
        foreach ($route as $key => $value) {
            if (preg_match($pattern, $value)){
                $retVal = ["key" => $key, "value" => $value];
                break;
            }
        }
        return $retVal;
    }
    
    public function group($groupParams, \Closure $callback)
    {
        if (is_string($groupParams)){
            $groupParams = array("prefix" => $groupParams);
        }
        array_push($this->groups, $groupParams);
        call_user_func($callback, $this);
        array_pop($this->groups);
    }
    
    public function get($pattern, $callback)
    {
        return $this->addRoute(["GET"], $pattern, $callback);
    }
    
    public function post($pattern, $callback)
    {
        return $this->addRoute(["POST"], $pattern, $callback);
    }
    
    public function put($pattern, $callback)
    {
        return $this->addRoute(["PUT"], $pattern, $callback);
    }
    
    public function delete($pattern, $callback)
    {
        return $this->addRoute(["DELETE"], $pattern, $callback);        
    }
    
    public function any($pattern, $callback)
    {
        return $this->addRoute(["GET", "POST", "PUT", "DELETE"], $pattern, $callback);        
    }
            
    public function rest($pattern, $controllerName, $options = array())
    {
        //$defaultActions = array("display", "index", "create", "edit", "update", "insert", "delete");
        
        $defaultActions = array("index", "display", "create", "edit", "insert", "update", "delete");
        //get default actions based on user specified
        $actions = $this->getAvailableActions($defaultActions, $options);        
        
        foreach ($actions as $value) {
            $this->{"add".ucfirst($value)."Route"}($pattern, $controllerName, $options);
        }
               
    }

    public function route()
    {
        //Find route that match current request
        $route = $this->findRoute();
        
        $this->setCurrentRoute($route);
        
        //if no route found
        if (is_null($route)){
            return;
        }
        
        die (print_r($this->routes, true));
        
        list($controller, $action) = $this->initController();
        $this->runController($controller, $action, $this->getParameters());
    }
    
    /**
     * Return Controller instance and possible action name
     * @return array
     */
    protected function initController()
    {
        $route = $this->getCurrentRoute();
        
        //Check if route callback is a closure. If true it has to be executed and break
        $controller = $action = null;
        
        if ($route->isClosure()){
            $controller = $route->getController();
        } else {
            //So the route callback is not a closure and is string
            //It's time to parse route callback string
            $ctrlName = $route->getController();
            $controller = new $ctrlName();
            if ($route->hasAction()){
                $action = $route->getAction();
            }
        }
        return array($controller, $action);
    }

    protected function getGroupsPrefixes($merge = true)
    {
        $retVal = $this->collect($this->groups, "prefix");
        if ($merge){
            $retVal = implode("/", $retVal);
        }
        return $retVal;
    }
    
    protected function getGroupsNamespaces($merge = true)
    {
        $retVal = $this->collect($this->groups, "namespace");
        if ($merge){
            $retVal = implode("\\", $retVal);
        }
        return $retVal;
    }
    
    protected function collect($arr, $field)
    {
        $retVal = array();
        foreach ($arr as $group) {
            if (isset($group[$field]) && !empty($group[$field])){
                $retVal[] = $group[$field];
            }
        }
        return $retVal;
    }
    
    protected function addRoute(array $method, $pattern, $callback)
    {
        $pattern = $this->getGroupsPrefixes() . "/" . $pattern;
        $this->formatRegular($pattern);
        $callback = $this->getControllersNamespace() . trim($this->getGroupsNamespaces(), "\\") . "\\" . $callback;
        $this->routes[] = $this->initRoute($method, $pattern, $callback);
        return end($this->routes);
    }    
    
    protected function initRoute(array $method, $pattern, $callback)
    {
        return new Route($method,$pattern, $callback);
    }
    
    protected function getAvailableActions($defaults, $options)
    {
        if (isset($options['only'])) {
            return array_intersect($defaults, (array) $options['only']);
        } elseif (isset($options['except'])) {
            return array_diff($defaults, (array) $options['except']);
        }
        return $defaults;
    }
    
    
    protected function addIndexRoute($pattern, $controllerName, $options = array())
    {
        $connection = $this->getMethodName($controllerName, "index", $options = array());
        $this->addRoute(["GET"], $pattern, $connection, $options);
    }   
    
    protected function addDisplayRoute($pattern, $controllerName, $options = array())
    {
        $this->addRoute(["GET"], $pattern . "/{num}", "$controllerName.display", $options);        
    }
    
    protected function addCreateRoute($pattern, $controllerName, $options = array())
    {
        $this->addRoute(["GET"], $pattern . "/create", "$controllerName.create", $options);        
    }
    
    protected function addEditRoute($pattern, $controllerName, $options = array())
    {
        $this->addRoute(["GET"], $pattern . "/edit/{param1}", "$controllerName.edit", $options);        
    }
    
    protected function addInsertRoute($pattern, $controllerName, $options = array())
    {
        $this->addRoute(["POST"], $pattern . "", "$controllerName.insert", $options);        
    }
    
    protected function addUpdateRoute($pattern, $controllerName, $options = array())
    {
        $this->addRoute(["PUT"], $pattern . "/{param1}", "$controllerName.update", $options);        
    }
    
    protected function addDeleteRoute($pattern, $controllerName, $options = array())
    {
        $this->addRoute(["DELETE"], $pattern . "/{param1}", "$controllerName.delete", $options);
    }

    protected function getMethodName($controllerName, $method, $options = array())
    {
        $retVal = "$controllerName.$method";
        $callback = Arr::get($options, "aliases.$method", null);
        if (!is_null($callback)){
            if (str($callback)->has(".")){
                $retVal = $callback;
            } else {
                $retVal = "$controllerName.$callback";
            }
        }        
        return $retVal;
    }

    /**
     * 
     * @return Route
     */
    protected function findRoute()
    {
        $uri = Uri::getUriSegmentsString();
        foreach ($this->routes as $route) {
            if ($route->matches($this, $uri)){
                return $route;
            }
        }
        return null;
    }
    
    /**
     * Formats the pattern string to a valid regular expression
     * @param string $pattern
     */
    protected function formatRegular(&$pattern)
    {
        $pattern = addcslashes(trim($pattern, "\\\/"), "\\\/");
    }
    
    
    // Properties
    
    /**
     * 
     * @return Route
     */
    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }

    public function setCurrentRoute($currentRoute)
    {
        $this->currentRoute = $currentRoute;
        return $this;
    }
    
}