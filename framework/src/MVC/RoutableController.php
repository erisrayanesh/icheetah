<?php

namespace ICheetah\MVC;

use ICheetah\Tools\Collection;
use ICheetah\Tools\Convert;
use ICheetah\Http\Inputs;
use ICheetah\Foundation\Exceptions\PageNotFoundException;

abstract class RoutableController extends Controller
{

    private $lstActions;
    
    private $defaultAction;

    private $action;

    public function __construct()
    {
        parent::__construct();
        $this->setupValidActions();
    }
    
    final public function run()
    {
        if (!is_null($route) && !empty($route->first())){
            //$action = $route[0];
            $action = $route->extractFirst();
            if (!$this->isActionValid($action)){
                $action = $this->getDefaultAction();
            }
            Inputs::set("action", $action, $_GET);
            Inputs::regenerateGlobalRequest();
        }
        
        $this->route($route);
        
        $action = Inputs::get("action", $this->getDefaultAction());
        if (!$this->isActionValid($action)){
            $action = $this->getDefaultAction();
        }
        $this->setAction($action);
    }
    
    protected function route(Collection &$route = null)
    {
        return;
    }
    
    protected function setupValidActions()
    {
        // Get the public methods in this class using reflection.
        $classRef = new \ReflectionClass($this);
        $pubMethods = $classRef->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        foreach ($pubMethods as $method)
        {
            $method instanceof \ReflectionMethod;
            //Method must be public, Not constructor, Nor static, Nor inherited
            if ($method->getDeclaringClass()->getName() === $classRef->getName() &&
                !$method->isConstructor() && !$method->isStatic() && $method->getName() !== "run"){
                $this->lstActions[] = strtolower($method->getName());
            }
        }
    }
    
    /**
     * 
     * @return array
     */
    final protected function validActions()
    {
        return $this->lstActions;
    }
    
    final protected function isActionValid($action)
    {
        return in_array($action, $this->validActions()) !== false;
    }
    
    final protected function runAction($action, array $options = array())
    {
        $retVal = false;
        if ($this->isActionValid($action)){
            //$permite = $this->isAuthorized($action, $defAuth, $user);
            //if ($permite === null || $permite === 1){
                $retVal = $this->$action($options);
            //} elseif ($permite == 0) {
                //Access denied
                //\Web::me()->addMessage($this->phrases->find("RESTRICTED_ACCESS"), $this->phrases->find("OPERATION_FAILURED"), \MessageItem::CRITICAL);
                //\Client::redirect("index.php");
            //}
        } else {
            throw new PageNotFoundException("Action \"$action\" not found");
        }
        return $retVal;
    }

    public function getAction()
    {
        return $this->action;
    }

    protected function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function getDefaultAction()
    {
        return $this->defaultAction;
    }

    public function setDefaultAction($defaultAction)
    {
        $this->defaultAction = $defaultAction;
        return $this;
    }
    
        
}
?>