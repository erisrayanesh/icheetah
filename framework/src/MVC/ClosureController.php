<?php

namespace ICheetah\MVC;

class ClosureController extends Controller
{

    protected $callback;
    
    public function __construct(callable $callback)
    {
        parent::__construct();
        $this->setCallback($callback);
    }
    
    public function run()
    {
        
    }

    
    public function getCallback()
    {
        return $this->callback;
    }

    public function setCallback($callback)
    {
        $this->callback = $callback;
        return $this;
    }


    
}

?>