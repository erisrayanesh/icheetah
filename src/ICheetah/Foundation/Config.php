<?php

namespace ICheetah\Foundation;

class Config
{
    use \ICheetah\Traits\Singleton;
    
    /**
     *
     * @var \ICheetah\Tools\Collection
     */
    protected $cache;
    
    
    protected $repository;


    protected function __construct()
    {
        parent::__construct();
        $this->cache = new \ICheetah\Tools\Collection();
    }
        
    public function item($key, $default = null, $delimiter = null)
    {
        if (is_null($delimiter)){
            $delimiter = ".";
        }
        
        $this->load($key);
        
        //split and reverse the keys order
        //example: "application.root" => ["root", "application"]
        //$keys = array_reverse(explode($delimiter, $key));
        //look for item at cache with the last key name at keys array
        //acording to the example it is "application"
//        $lastKey = array_pop($keys);
//        $has = $this->cache->hasKey($lastKey);
        //if $has is null, It means it is a name of new collection which has naver been loaded
//        if (is_null($has)){
            
//        }
        
        
//        while (is_array($has)){
//            $has = $has->get($key);
//        }
        
    }    

    public static function get($key, $default = null, $delimiter = null)
    {
        return self::getInstance()->item($key, $default);
    }
    
    public static function getInt($key, $default = null, $delimiter = null)
    {
        
    }
    
    public static function getFloat($key, $default = null, $delimiter = null)
    {
        
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
    
        
    
    private function load ($key)
    {
        $config = include_once $this->getRepository() . "/$key.php";
        if (is_array($config)){
            $config = \ICheetah\Tools\Arr::flatten($config);
            $this->cache->set($key, $config);
        }
    }
    
    
}

