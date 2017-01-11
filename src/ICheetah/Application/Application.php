<?php

namespace ICheetah\Application;

use \ICheetah\Http\Router;
use ICheetah\Http\Session\Session;
use \ICheetah\Database\Database;
use \ICheetah\Database\Connections;


class Application
{
    
    use \ICheetah\Traits\Singleton;
    
    /**
     *
     * @var Router
     */
    protected $router;

    protected function __construct()
    {
        parent::__construct();
        $this->router = new Router();        
    }
    
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
        
        
        $q = Database::table("users");
        $q instanceof \ICheetah\Database\Query\QueryBuilder;
//        $q->where(function (\ICheetah\Database\Query\QueryBuilder $query) {
            $q->where("name", "ab");
//        });
        $ret = $q->get();
        die(print_r($ret, true));
        
        
        //$this->getRouter()->run();        
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