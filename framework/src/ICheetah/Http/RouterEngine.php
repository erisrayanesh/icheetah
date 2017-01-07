<?php

namespace ICheetah\Http;

abstract class RouterEngine
{
    
    /**
     * List of request found arguments
     * @var array
     */
    protected $parameters = array();
    
    //protected $segments = array();

    protected $controllersNamespace = "\\Controllers\\";

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


    
    
//    public function getSegments()
//    {
//        return $this->segments;
//    }
//
//    public function setSegments($segments)
//    {
//        $this->segments = $segments;
//        return $this;
//    }

    

}

?>