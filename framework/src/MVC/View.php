<?php

namespace ICheetah\MVC;


use \ICheetah\Tools\Collection;

class View
{
    
    private $strName;
    
    /**
     *
     * @var \ICheetah\Tools\Collection
     */
    private $data;

    public static function make($strName, $data = array())
    {
        return new static($strName, $data);
    }
    
    public function __construct($strName, $data = array())
    {
        $this->data = new Collection($data);
        $this->setName($strName);
        $this->setData($data);
    }
    
    public function render()
    {
        if($models == null){
            $models = new \stdClass();
        }
        
        $file = APP_PATH_ASSETS . DS . "Views";
        if (strpos($strName, ".")){
            $path = explode(".", $strName);
            $strName = array_pop($path);
            $path = implode(DS, $path);
            $file .=  DS . $path;
        }
        
        ob_start();
        require_once $file . DS . "$strName.php";
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content;
    }
    
    public function __get($name)
    {
        return $this->getData()->get($name);
    }
    
    public function getData()
    {
        return $this->data;
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