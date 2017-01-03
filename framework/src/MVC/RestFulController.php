<?php

namespace ICheetah\MVC;

use \ICheetah\Http\Request;
use \ICheetah\Http\Route;
use ICheetah\Tools\Collection;
use \ICheetah\Foundation\Exceptions;
use \ICheetah\Http\IRouterEngine;

abstract class RestFulController extends Controller
{
    protected $route;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    final public function run()
    {
        $processor = "run" . Request::method();
        $this->callMethodIfExist($processor, $arguments);
    }
    
    final protected function runGET(Collection &$arguments = null)
    {
        //die(print_r($arguments, true));
        if ($arguments->isEmpty()){
            $this->callMethodIfExist("index");
        }
    }
    
    final protected function runPOST(Collection &$arguments = null)
    {
        $this->callMethodIfExist("insert", $arguments);
    }
    
    final protected function runPUT(Collection &$arguments = null)
    {
        $this->callMethodIfExist("update", $arguments);
    }
    
    final protected function runDELETE(Collection &$arguments = null)
    {
        $this->callMethodIfExist("delete", $arguments);
    }
    
    final protected function callMethodIfExist($name, $arguments)
    {
        if (method_exists($this, $name)){
            call_user_method($name, $this, $arguments);
        } else {
            throw new Exceptions\PageNotFoundException();
        }
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    
    
}
?>