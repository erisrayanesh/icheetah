<?php

namespace ICheetah\Plugins;

abstract class Plugin
{
    abstract public function run(array $options = null);

    private $name;
    
    private $group;
    
    private $dependencies = array();

    public function __construct($strName, $strGroup)
    {
        $this->setName($strName);
        $this->setGroup($strGroup);
    }
    
    public function getName()
    {
        return $this->name;
    }

    private function setName($strName)
    {
        $this->name = $strName;
    }
    
    public function getGroup()
    {
        return $this->group;
    }

    private function setGroup($strGroup)
    {
        $this->group = $strGroup;
    }
    
    public function getDependencies()
    {
        return $this->dependencies;
    }

    protected function addDependency($strName, $strGroup = "General", array $options = array())
    {
        $this->dependencies[] = array($strName, $strGroup, $options);
        return $this;
    }
    
    protected function getPath($path = null)
    {
        $retVal = Plugins::getRootRelative($this->getGroup(), $this->getName());
        if (!is_null($path)){
            $retVal .= $path;
        }
        return $retVal;
    }
    
}

?>