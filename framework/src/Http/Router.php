<?php

namespace ICheetah\Http;

use ICheetah\Foundation\Exceptions;

class Router
{
            
    protected static $instance = null;
    
    /**
     *
     * @var IRouterEngine
     */
    protected $engine = null;
    
    public function __construct(IRouterEngine $engine = null)
    {
        $this->setEngine($engine);
    }
        
    /**
     * Sets engine for an instance of router
     * @param \Closure|array|string $engine
     * @return Router
     */
    public static function init(IRouterEngine $engine)
    {
        return new static($engine);        
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

    /**
     * 
     * @return IRouterEngine
     */
    public function getEngine()
    {
        return $this->engine;
    }

    public function setEngine(IRouterEngine $engine = null)
    {
        $this->engine = $engine;
        return $this;
    }

    
   
    
}
?>