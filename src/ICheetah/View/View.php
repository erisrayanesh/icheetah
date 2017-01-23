<?php

namespace ICheetah\View;


class View
{
    
    private $strName;
    
    /**
     *
     * @var array
     */
    private $data;
    
    public function __construct($strName, array $data = [])
    {
        $this->data = $data;
        $this->setName($strName);
    }
    
    /**
     * Renders the view
     * @return \ICheetah\Http\Response\HtmlResponse
     */
    public function render()
    {
        $engin = new BlackSpots();
        $contents = $engin->render($this);
        return new \ICheetah\Http\Response\HtmlResponse($contents);
    }
    
    public function __get($name)
    {
        if (isset($this->data[$name])){
            return $this->data[$name];
        }
        return null;
    }
    
    public function __set($name, $value)
    {
        return $this->data[$name] = $value ;
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
        
}

?>