<?php

namespace ICheetah\Application;

use \ICheetah\Http\Router;
use \ICheetah\View;
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
        
        $response = null;
        
        try {
            $response = $this->getRouter()->run();
        } catch (Router\RouteNotFoundException $exc) {
            $response = "500";
        } catch (View\ViewNotFoundException $exc) {
            $response = "404";
        }
        
        if ($response instanceof \ICheetah\Http\Response\Response){
            return $response->send();            
        } else {
            echo $response;
        }
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