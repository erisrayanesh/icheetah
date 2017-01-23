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
        return $this->isCached("view", $name);
    }
    
    public function isExpired($type, $name, $file)
    {
        
    }

    public function getView($name)
    {
        return $this->getCache("view", $name);
    }
    
    public function saveView($name, $content)
    {
        return $this->saveCache("view", $name, $content);
    }
    
    /**
     * Returns an array of driver information
     * @return array
     * @throws CacheEngineException
     */
    public function getStorage($name = null)
    {
        
        $default = empty($name)? config("cache.default") : $name;
        if (empty($default)) {
            throw new CacheEngineException("No name or default storage name");
        }
        
        $driver = config("cache.storages.$default");
        if (empty($driver)) {
            throw new CacheEngineException("Storage not found or bad structure");            
        }
        
        if (!isset($driver["type"])) {
            throw new CacheEngineException("Storage type not available");
        }
        
        return $driver;
    }
    
    public function isCached($type, $name)
    {
        return $this->getCache($type, $name) !== null;
    }

    public function getCache($type, $name)
    {
        //Get driver info
        $driver = $this->getStorage();
        //Find processor
        $method = "getCacheFrom" . ucfirst($driver["type"]);
        if (!method_exists($this, $method)) {
            throw new CacheEngineException("Storage type is invalid");
        }
        
        return call_user_func_array([$this, $method], compact('driver', 'type', 'name', 'content'));
    }
    
    public function saveCache($type, $name, $content)
    {
        //Get driver info
        $driver = $this->getStorage();
        //Find processor
        $method = "saveCacheTo" . ucfirst($driver["type"]);
        if (!method_exists($this, $method)) {
            throw new CacheEngineException("Storage type is invalid");
        }
        
        return call_user_func_array([$this, $method], compact('driver', 'type', 'name', 'content'));
    }
    
    protected function getCacheFromFile(array $driver, $type, $name)
    {
        if (!isset($driver["location"])){
            throw new CacheEngineException('Storage location not available');
        }

        $path = Path::join([$driver["location"], strtolower($type), md5($name) . ".php"], DIRECTORY_SEPARATOR);
        if (!Findder::isFile($path)){
            return null;
        }
        
        return $path;
    }

    protected function saveCacheToFile(array $driver, $type, $name, $content)
    {
        if (!isset($driver["location"])){
            throw new CacheEngineException('Storage location not available');
        }

        $path = Path::join([$driver["location"], strtolower($type), md5($name) . ".php"], DIRECTORY_SEPARATOR);
        Findder::createFile($path, $content, true);
        return $path;
    }
    
    
}
