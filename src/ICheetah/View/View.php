<?php

namespace ICheetah\View;

use \ICheetah\Tools\Collection;

class View
{
    
    private $strName;
    
    /**
     *
     * @var array
     */
    private $data;
    
    private $sections = array();
    
    public function __construct($strName, $data = array())
    {
        if (!is_array($data)){
            $data = (array) $data;
        }
        $this->data = $data;
        $this->data = $data;
        $this->setName($strName);
    }
    
    public function render()
    {
        $engin = new BlackSpots();
        $content = $engin->render($this);
        return $content;
    }
    
    public function __get($name)
    {
        return $this->getData()->get($name);
    }
    
    public function __set($name, $value)
    {
        $this->getData()->set($name, $value);
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * 
     * @param string $name
     * @param mixed $value
     * @return \ICheetah\View\View
     */
    public function setData($name, $value)
    {
        $this->getData()->set($name, $value);
        return $this;
    }
    
    public function getName()
    {
        return $this->strName;
    }

    public function setName($strName)
    {
        $this->strName = $strName;
        return $this;
    }
    
    public function put($section, $content)
    {
        $this->sections[$section] = $content;
        return $this;
    }
    
    public function append($section, $content)
    {
        if (array_key_exists($section, $this->sections)){
            $this->sections[$section] = $this->sections[$section] . $content;
        } else {
            $this->put($section, $content);
        }
        return $this;
    }
    
    
}

?>