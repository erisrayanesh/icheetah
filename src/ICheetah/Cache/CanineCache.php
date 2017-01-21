<?php

namespace ICheetah\Cache;

use \ICheetah\Cache\Exceptions\CacheEngineException;
use \ICheetah\Tools\Path;
use \ICheetah\Tools\Findder;

class CanineCache
{
    
    use \ICheetah\Traits\Singleton {
        getInstance as singletonGetInstance;
    }
    
    protected function __construct()
    {
        
    }
    
    /**
     * 
     * @return CanineCache
     */
    public static function getInstance()
    {
        return static::singletonGetInstance();
    }
    
    public function hasView($name)
    {
        
    }
    
    public function isExpired($type, $name, $file)
    {
        
    }

    public function getView($name, $default = null)
    {
        
    }
    
    public function saveView($name, $content)
    {
        return $this->saveCache("view", $name, $content);
    }
    
    
    protected function getCache($type, $name, $default = null)
    {
        
    }
    
    protected function saveCache($type, $name, $content)
    {
        $default = config("cache.default");
        if (is_null($default)) {
            throw new CacheEngineException("No default storage");
        }
        
        $driver = config("cache.storages.$default");
        if (!is_array($driver)) {
            throw new CacheEngineException("Storage not found or bad structure");            
        }
        
        if (!isset($driver["type"])) {
            throw new CacheEngineException("Storage type not available");
        }
        
        $method = "saveCacheBy" . ucfirst($driver["type"]);
        if (!method_exists($this, $method)) {
            throw new CacheEngineException("Storage type is invalid");
        }
        
        return call_user_func_array([$this, $method], compact('driver', 'type', 'name', 'content'));        
    }

    protected function saveCacheByFile(array $driver, $type, $name, $content)
    {
        if (!isset($driver["location"])){
            throw new CacheEngineException('Storage location not available');
        }

        $path = Path::join([$driver["location"], strtolower($type), md5($name) . ".php"], DIRECTORY_SEPARATOR);
        Findder::createFile($path, $content, true);
        return $path;
    }
    
    
    
}
