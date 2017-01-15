<?php

namespace ICheetah\Http\Response;

class JsonResponse extends Response
{
    public function __construct()
    {
        parent::__construct();
        
        
    }
    
    public function setContent($content)
    {
        if ($this->isSerializable($content)){
            $content = json_encode($content);
        }
        
        parent::setContent($content);
    }
    
    protected function isSerializable($content)
    {
        return $content instanceof ArrayObject ||
               $content instanceof JsonSerializable ||
               is_array($content);
    }
}
