<?php

namespace ICheetah\Http\Router;
use ICheetah\Tools\Arr;
use \ICheetah\Tools\Uri;
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
    
    /**
     * List of request found arguments
     * @var array
     */
    protected $parameters = array();
    
    protected $controllersNamespace = "\\Controllers\\";

    public function __construct()
    {
        
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
        // push current group to stack
        array_push($this->groups, $groupParams);
        // run the callback
        call_user_func($callback, $this);
        // pop current group from stack
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

    /**
     * Executes added route patterns of uri and runs assigned callback
     * @return Response
     * @throws Exceptions\NoRouterEngine
     */
    public function run()
    {
        //Find route that match current request
        $key = $this->findRoute();
        //If no route found return 404 response
        if (is_null($key)){
            throw new RouteNotFoundException();
        }
        //Get route of found key
        $route = $this->routes[$key];
        //Set found route as current proccing route
        $this->setCurrentRoute($route);
        //find route bussines logic
        list($controller, $action) = $this->initController($route);
        return $this->runController($controller, $action, $this->getParameters());            
    }    
    
    // Private methods
    
    /**
     * 
     * @return Route
     */
    protected function findRoute()
    {
        $uri = Uri::getSegmentsString();
        foreach ($this->routes as $key => $route) {
            if ($route->matches($this, $uri)){
                return $key;
            }
        }
        return null;
    }
    
    /**
     * Return Controller instance and possible action name
     * @return array
     */
    protected function initController(Route $route)
    {
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
    
    protected function runController($controller, $action = null, $params = array())
    {
        if ($controller instanceof \Closure){
            return call_user_func_array($controller, $params);
        }
        
        //Check if controller is routable.
        if ($controller instanceof \ICheetah\MVC\RoutableController){
            //Call run method of controll to proceed the routing
            return $controller->run();
        } else {
            //The controller is simple controller.
            //If action is available then it will be called. 
            //Otherwise controller __invoke would be called.
            if (!is_null($action)){
                return call_user_method_array($action, $controller, $params);                
            } else {
                //Call __invoke
                return $controller($params);
            }            
        }        
        
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
        if (is_string($callback)){
            $callback = $this->getControllersNamespace() . trim($this->getGroupsNamespaces(), "\\") . "\\" . $callback;            
        }
        $this->routes[] = $this->createRoute($method, $pattern, $callback);        
        return end($this->routes);
    }
    
    /**
     * Formats the pattern string to a valid regular expression
     * @param string $pattern
     */
    protected function formatRegular(&$pattern)
    {
        $pattern = addcslashes(trim($pattern, "\\\/"), "\\\/");
    }
    
    protected function createRoute(array $method, $pattern, $callback)
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
    
    public function getParameters()
    {
        return $this->parameters;
    }

    public function setParameters($arguments)
    {
        $this->parameters = $arguments;
        return $this;
    }
    
    public function getControllersNamespace()
    {
        return $this->controllersNamespace;
    }

    public function setControllersNamespace($controllersNamespace)
    {
        $this->controllersNamespace = $controllersNamespace;
        return $this;
    }

}