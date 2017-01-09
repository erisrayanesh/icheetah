<?php

namespace ICheetah\Tools;

class Path
{
    
    protected $path;
    
    protected $allowDots;
    
    protected $delimiter = "/";
    
    public function __construct($path = "", $allowDots = false, $delimiter = "/")
    {   
        $this->delimiter = $delimiter;
        $this->allowDots = $allowDots;
        $this->path = $this->toArray($this->clean($path));        
    }
    
    public function combine($path)
    {
        $this->path[] = $this->toArray($this->clean($path));
    }
    
    public function __toString()
    {
        return $this->toString();
    }
    
    public function toString()
    {
        return implode($this->getDelimiter(), $this->path);
    }
    
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    public function getAllowDots()
    {
        return $this->allowDots;
    }

    public function setAllowDots($allowDots)
    {
        $this->allowDots = $allowDots;
        return $this;
    }

    
    protected function clean($path)
    {
        if (!$this->getAllowDots()) {
            //remove sequential dot
            $path = preg_replace("/\.+/", "", $path);
        }
        
        //remove backslash
        $path = preg_replace("/\\+/", "", $path);
        
        //remove sequential slash
        $path = preg_replace("/\/+/", "", $path);
        
        return $path;
    }
    
    protected function toArray($path)
    {
        return explode("/", $path);
    }
    
}