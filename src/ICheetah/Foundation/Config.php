<?php

namespace ICheetah\Foundation;

class Config
{
    use \ICheetah\Traits\Singleton;
    
    Const FILE = "file";
    Const DB = "db";
    
    /**
     *
     * @var \ICheetah\Tools\Collection
     */
    public $cache;
    
    /**
     * Config source
     * @var string
     */
    protected $source = "file";
    
    /**
     * Database source mode table name
     * @var string
     */
    protected $tableName = "config";

    /**
     * File source mode directory name
     * @var string
     */
    protected $repository;


    protected function __construct()
    {
        $this->cache = new \ICheetah\Tools\Collection();
    }
        
    /**
     * 
     * @param string $key
     * @param mixed $default
     * @return \ICheetah\Tools\String
     */
    public function item($key, $default = null)
    {
        $this->tryCache($key);
        return \ICheetah\Tools\Arr::get($this->cache->toArray(), $key, $default);        
    }    

    /**
     * 
     * @param string $key
     * @param mixed $default
     * @return \ICheetah\Tools\String
     */
    public static function get($key, $default = null)
    {
        return self::getInstance()->item($key, $default);
    }
    
    /**
     * 
     * @param string $key
     * @param mixed $default
     * @return int
     */
    public static function getInt($key, $default = null)
    {
        return self::getInstance()->item($key, $default)->toInt();
    }
    
    /**
     * 
     * @param string $key
     * @param mixed $default
     * @return float
     */
    public static function getFloat($key, $default = null)
    {
        return self::getInstance()->item($key, $default)->toFloat();
    }
    
    
    public function getSource()
    {
        return $this->source;
    }

    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }
        
    public function getTableName()
    {
        return $this->tableName;
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function setRepository($repository)
    {
        $this->repository = $repository;
        return $this;
    }
    
    private function tryCache ($key)
    {
        $methodName = "cacheFrom" . ucfirst($this->getSource());
        if (!method_exists($this, $methodName)) {
            return;
        }
        call_user_func([$this, $methodName], $key);        
    }
    
    private function cacheFromFile($key)
    {
        if (stripos($key, ".")){
            $key = mb_substr($key, 0, stripos($key, "."));
        }
        
        if ($this->cache->hasKey($key)){
            return;
        }
        
        $filename = $this->getRepository() . "/$key.php";
        if (file_exists($filename)) {
            $config = include_once $filename;
            $this->cache->set($key, $config);
        }
    }
    
    private function cacheFromDB($key)
    {
        
    }
    
}

