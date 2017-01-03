<?php

namespace ICheetah\Http;

use \ICheetah\Tools\Collection;
use \ICheetah\Tools\Arr;

class Route
{
    
    protected $method;

    /**
     * Pattern to matche against uri
     * @var string 
     */
    protected $pattern;
    
    /**
     * Callback to run after route matched
     * @var mixed
     */
    protected $callback;
    
    /**
     * Controller part of callback string
     * @var string
     */
    protected $controller = null;
    
    /**
     * Action part of callback string
     * @var string
     */
    protected $action = null;
    
//    protected $namespace;
    
    //public function __construct(array $method, $pattern, $callback, $namespace = null)
    public function __construct(array $method, $pattern, $callback)
    {
        $this->setMethod($method);
        $this->setPattern($pattern);
        $this->setCallback($callback);
//        $this->setNamespace($namespace);
    }    

    public function matches(ManualRouter $router, $uri)
    {
        
        if (!Arr::exist($this->getMethod(), Request::method())){
            return false;
        }
        
        $pattern = $this->translate($this->getPattern());
            
        //If the route is closure or controller action was specified, 
        //Then the route pattern must have begin and end anchors
        if ($this->isClosure() || $this->hasAction()){
            $pattern = "^$pattern$";
        }
        
        $matches = array();
        if (preg_match("/$pattern/i", $uri, $matches)){
            //Extract the matched string from matches array
            array_shift($matches);
            $router->setParameters($matches);
            return true;
        } else {
            return false;
        }
    }    
    
    protected function translate($pattern)
    {
        $retVal = preg_replace("/\{(\w+)\}/i", '([\w+|\-]+)', $pattern);
        if (!empty($retVal)){
            return $retVal;
        }
        return $pattern;
    }
    
    public function isClosure()
    {
        return $this->getCallback() instanceof \Closure;
    }
    
    public function hasAction()
    {
        return $this->action != null;
    }
    
    public function getController()
    {
//        $retVal = $this->controller;
//        if ($namespace){
//            $retVal = $this->getNamespace() . "\\" . $retVal;
//        }
//        return $retVal;
        return $this->controller;
    }
    
    public function getAction()
    {
        return $this->action;
    }
    
    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod(array $method)
    {
        $this->method = $method;
        return $this;
    }

        
    public function getPattern()
    {
        return $this->pattern;
    }

    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function setCallback($callback)
    {
        $this->callback = $callback;
        
        if (is_string($callback)){
            $callback = str($callback);
            if ($callback->has(".")){
                $parts = $callback->split(".");
                $this->controller = normalize_class_name($parts->extractFirst(), true);
                $this->action = str_to_camel_case($parts->extractFirst());
            } else {
                $this->controller = str_to_studly_case($callback);
            }
        }
        
        return $this;
    }

//    public function getNamespace()
//    {
//        return $this->namespace;
//    }
//
//    public function setNamespace($namespace)
//    {
//        $this->namespace = implode("\\", array_map("ucwords", explode("\\", $namespace)));
//        return $this;
//    }

    
    
}

?>