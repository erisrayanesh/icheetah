<?php

namespace ICheetah\Application;

use \ICheetah\Foundation\Singleton;
use \ICheetah\Http\Router;
use \ICheetah\Http\IRouterEngine;
use ICheetah\Tools\Collection;
use ICheetah\Http\Session;
use \ICheetah\Database\Database;
use \ICheetah\Database\Connections;


class Application extends Singleton
{
    
    protected static $defaultControllerName = "index";
    protected $defaultNamespace = "\\Controllers\\";
    
    protected static $instance = null;
    
    /**
     *
     * @var Router
     */
    protected $router;


    /**
     *
     * @var Collection 
     */
    protected $globalConfig = null;
    
    protected function __construct()
    {
        parent::__construct();
        $this->globalConfig = new Collection();
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
        $this->initDatabase();
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

    public function setRouterEngine(IRouterEngine $engine)
    {
        $this->getRouter()->setEngine($engine);
    }

    public function config()
    {
        return $this->globalConfig;
    }

    public function getConfig($name, $default = null)
    {
        return $this->config()->get($name, $default);
    }
            
    public function getDefaultNamespace()
    {
        return $this->defaultNamespace;
    }

    public function setDefaultNamespace($defaultNamespace)
    {
        $this->defaultNamespace = $defaultNamespace;
        return $this;
    }
    
    protected function initSession()
    {
        $session = Session::getInstance();
        $session->setMaxLifeTime($this->getConfig("session_expire"));
        $session->setSessionName(md5("arvand"));
        Session::getInstance()->initSession();
    }
    
    protected function initDatabase()
    {

        $dbConfig = array (
            "driver"    => \Config::DATABASE_DRIVER,
            "host"    => \Config::DATABASE_SERVER,
            "database"  => \Config::DATABASE_NAME,
            "username"  => \Config::DATABASE_USER,
            "password"  => \Config::DATABASE_PASSWORD,
            "charset"   => \Config::DATABASE_CHARSET,
            "collation" => \Config::DATABASE_COLLATION
        );
        
        $connection = null;
        
        switch (\Config::DATABASE_DRIVER) {
            case "mysql":
                $connection = new Connections\MySqlConnection($dbConfig);
                break;
        }
        
        $connection->open();
        
        Database::getInstance()->addConnection("main", $connection, true);
    }
    
    
}

?>