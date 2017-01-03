<?php

namespace ICheetah\Plugins;


class Plugins extends \ICheetah\Foundation\Singleton
{
    protected static $instance = null;
    protected $loaded = array();

    /**
     * 
     * @return Plugins
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }
    
    /**
     * 
     * @param type $strName
     * @param type $strGroup
     * 
     */
    public function load($strName, $strGroup = "General")
    {
        $file = self::getRoot() . DS . $strGroup . DS . $strName . DS . "$strName.php";
        //Check if plugin was loaded
        if (in_array($file, $this->loaded)){
            return true;
        }
        
        if (file_exists($file)){
            require_once $file;
            $this->loaded[] = $file;
            return true;
        } else {
            return false;
        }
    }
    
    public function loadAndRun($strName, $strGroup = "General", array $options = null)
    {
        if ($this->load($strName, $strGroup)){
            $instance = "Plugins\\$strGroup\\$strName\\$strName";
            $plugin = new $instance();
            if ($plugin instanceof Plugin){
                $this->loadDependencies($plugin->getDependencies());
                $plugin->run($options);                
            }
        }
    }
    
    public static function getRoot()
    {
        return APP_PATH_LIBS . DS . "Plugins";
    }
    
    public static function getRootRelative($strGroup = null, $strName = null)
    {
        $path =  APP_URI . "libs/Plugins/";
        if (!is_null($strGroup)){
            $path .= $strGroup . "/";
        }
        if (!is_null($strName)){
            $path .= $strName . "/";
        }
        return $path;
    }
    
    protected function loadDependencies(array $dependencies)
    {
        foreach ($dependencies as $value) {
            list($strName, $strGroup, $options) = $value;
            $this->loadAndRun($strName, $strGroup, $options);
        }
    }

}

?>