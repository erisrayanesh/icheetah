<?php

namespace ICheetah\Application;

use \ICheetah\Http\Router;
use ICheetah\Http\Session\Session;
use \ICheetah\Database\Database;
use \ICheetah\Database\Connections;


class Application
{
    
    use \ICheetah\Traits\Singleton;
    
    protected $rootDir = "";

    /**
     *
     * @var Router
     */
    protected $router;
    
    /**
     * 
     * @return Application
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }
    
    public function run()
    {
        //$this->initSession();        
        
//        $q = Database::table("users");
//        $q instanceof \ICheetah\Database\Query\QueryBuilder;
////        $q->where(function (\ICheetah\Database\Query\QueryBuilder $query) {
//            $q->where("name", "ab");
////        });
//        $ret = $q->get();
//        die(print_r($ret, true));
                
        return $this->getRouter()->run();        
    }
    
    public function getRootDir()
    {
        return $this->rootDir;
    }

    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
        return $this;
    }
        
    public function getRouter()
    {
        return $this->router;
    }

    public function setRouter(Router\Router $router)
    {
        $this->router = $router;
    }
    
    protected function initSession()
    {
        $session = Session::getInstance();
        $session->setMaxLifeTime(config("session.maxlifetime", 60));
        $session->setSessionName(md5("arvand"));
        $session->init();
    }
    
}