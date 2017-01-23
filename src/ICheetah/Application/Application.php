<?php

namespace ICheetah\Application;

use \ICheetah\Http\Router;
use ICheetah\View\View;
use ICheetah\Http\Response;
use ICheetah\Http\Session\Session;

class Application
{
    
    use \ICheetah\Traits\Singleton {
        getInstance as singletonGetInstance;
    }
    
    protected $rootDir = "";

    /**
     *
     * @var Router
     */
    protected $router;
    
    protected function __construct()
    {
        $this->router = new Router\Router();
    }
    
    /**
     * 
     * @return Application
     */
    public static function getInstance()
    {
        return static::singletonGetInstance();
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
        
        try {
            $response = $this->getRouter()->run();
            
            if ($response instanceof View){
                $response = $response->render();
            }
            
            if ($response instanceof Response\Response){
                $response->send();     
            } else {
                echo $response;
            }
            
        } catch (\Exception $exc) {
            
        }        
        
        $time_end = microtime(true);
        $execution_time = $time_end - ICHEETAH_START;        
        logger($execution_time);
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
        
    /**
     * 
     * @return Router\Router
     */
    public function getRouter()
    {
        return $this->router;
    }
    
    protected function initSession()
    {
        $session = Session::getInstance();
        $session->setMaxLifeTime(config("session.maxlifetime", 60));
        $session->setSessionName(md5("arvand"));
        $session->init();
    }
    
}