<?php

namespace ICheetah\Application;

use ICheetah\Http\Inputs;
use ICheetah\Http\Request;
use ICheetah\Tools\Collection;
use ICheetah\UI\Template;
use ICheetah\Http\Session;
use \ICheetah\Http\Response;

abstract class Application1 extends \ICheetah\Foundation\Singleton
{
    protected static $defaultControllerName = "index";
    protected static $instance = null;
    protected $defaultNamespace = "\\Controllers\\";
    
    /**
     *
     * @var Collection 
     */
    protected $globalConfig = null;
    
    abstract protected function initGlobalConfig();
    
    protected function route(Collection &$routeSegments = null)
    {
        //Check if $uri[0] is a valid service name
        if (!is_null($routeSegments) && !is_null($routeSegments->first()) && self::controllerExist($routeSegments->first())){
            $controller = strtolower(trim($routeSegments->extractFirst()));
        } else {
            $controller = self::$defaultControllerName;
        }
        
        
        //No problem to set service
        //if the request method is post the post, the service value will set to service
        $this->setControllerName($controller);
        
        //Setup default layout
        Template::getInstance()->setLayout(strtolower(self::getName()) . ".main");
        
        //Find user custom layout
        if (!is_null($routeSegments)) {
            $match = $routeSegments->where("/layout-\w+/")->first();
            if ($match != null) {
                //unset($route[$match["key"]]);
                $routeSegments->remove($match);
                $layout = explode("-", $match["value"]);
                Inputs::set("layout", $layout[1], $_GET);
                Inputs::regenerateGlobalRequest();
            }
        }
        
        if (strlen(Inputs::get("layout", "")) > 0) {
            Template::getInstance()->setLayout(strtolower(self::getName()) . "." . strtolower(Inputs::get("layout", "main")));
        }        
        
    }
    
    public function run(Collection &$routeSegments = null)
    {
        
        try {
            
            Session::getInstance()->initSession();
            
            $this->route($routeSegments);

            //Get user required service
            $controller = self::getControllerName();
            
            //attribute_options => AttributeOptions 
            str_to_studly_case($controller);
            
//            $ctrl = explode("_", $controller);
//            $ctrl = array_map("ucfirst", $ctrl);
//            $ctrl = implode("", $ctrl);

            //Create controller path
            //$file = APP_PATH_BASE . DS . "controllers" . DS . strtolower(self::getName()) . DS . $ctrl . ".php";
            //require_once $file;

            $controller = $this->getDefaultNamespace() . self::getName() . "\\" . $controller;
        
            
//            if (!class_exists($className)){
//                throw new PageNotFoundException("Class $className not found");
//            }

            $ctrl = new $controller();            
            $ctrl->run($routeSegments);
            
        } catch (ClassNotFoundException $exc) {
            Template::getInstance()->setLayout("404");
            Response::getInstance()->setStatusCode("404");
        } catch (PageNotFoundException $exc) {
            Template::getInstance()->setLayout("404");
            Response::getInstance()->setStatusCode("404");
        }

        if (Request::isAjax()){
            $retVal = Template::getInstance()->getData("content");
        } else {
            $retVal = Template::getInstance()->render();
        }
        
        Response::getInstance()->setContent($retVal);
        
    }
            
    public static function getName()
    {
        return array_pop(explode("\\", static::class));
    }

    public static function getControllerName()
    {
        return Inputs::get("service", self::$defaultControllerName);
    }
    
    public function setControllerName($service, $get = true, $post = false)
    {
        if ($get){
            Inputs::set("service", $service, $_GET);
        }
        
        if ($post){
            Inputs::set("service", $service, $_POST);
        }
        
        Inputs::regenerateGlobalRequest();
    }

    public static function controllerExist($controllerName)
    {
//        $controllerName = strtolower(trim($controllerName));
//        $controllerName = explode("_", $controllerName);
//        $controllerName = array_map("ucfirst", $controllerName);
//        $controllerName = implode("", $controllerName);
        
        $controllerName = str_to_studly_case(strtolower($controllerName));

        //Create controller path
        $file = APP_PATH_BASE . DS . "assets" . DS . "Controllers" . DS . self::getName() . DS . $controllerName . ".php";
        return file_exists($file);
    }
    
    public function getGlobalConfig($name = null, $default = null)
    {
        if (is_null($this->globalConfig)){
            $this->initGlobalConfig();
        }
        
        if (is_null($name)){
            return $this->globalConfig;
        } else {
            $retVal = $this->globalConfig->get($name);
            return !is_null($retVal)? $retVal : $default;
        }
    }

    /**
     * 
     * @return Application
     */
    public static function getAppInstance()
    {
        $app = static::getAppName();
        if (empty($app)){
            return null;
        }
        //Create app path
        //$file = APP_PATH_BASE . DS . "applications" . DS . "$app.php";
        //if (file_exists($file)){
            //require_once $file;
            $className = "\\Applications\\$app";
            return $className::getInstance();
        //} else {
            //return null;
        //}
    }
    
    public static function getAppName($default = null)
    {
        $app = explode("_", Inputs::get("app", $default));
        $app = array_map("ucfirst", $app);
        $app = implode("", $app);
        return $app;
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


}

?>