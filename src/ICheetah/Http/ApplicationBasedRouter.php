<?php

namespace ICheetah\Http;

use ICheetah\Application\Application;
use ICheetah\Foundation\IConfigManager;
use ICheetah\Http\Inputs;
use ICheetah\Tools\Uri;
use ICheetah\UI\Template;

class ApplicationBasedRouter extends RouterEngine implements IRouterEngine
{
    
    protected $defauteApp = "Site";

    /**
     *
     * @var IConfigManager
     */
    protected $configManager;

    public function __construct()
    {
        
        //First instansiate templating engin
        $template = Template::getInstance();
        
        //Define app uri
        define("APP_URI", Uri::getScriptUri()->getUri() . "/");
                
        define("SITE_URI", Uri::getRootUrl()->getUri());
        
        define("APP_FULL_URI", Uri::getRootUrl()->getUri() . "/" . APP_URI);
                
        $template->addHeader("Cache-Control: no-cache, must-revalidate");
        $template->addHeader("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        $template->addHeader("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
    }
    
    public function route()
    {
        
        $response = Response::getInstance();
        //check search engin friendly url status
        $uri = Uri::getUriSegments();
        
        $app = Application::getAppName($this->getDefauteApp());
                
        if ($uri->isNotEmpty()){
            if (in_array(strtolower($uri->first()), self::getApps())){
                $app = strtolower(trim($uri->extractFirst()));
            } else {
                $app = $this->getDefauteApp();
            }
        }
        
        //setup app and service to $_GET
        Inputs::set("app", $app, $_GET);                

        //regenerate global $_REQUEST. Very important
        Inputs::regenerateGlobalRequest();
        
        //Get user required app
        $app = Application::getAppInstance();
        
        $app->run($uri);
        
    }
    
    //Properties
    
    public function getDefauteApp()
    {
        return $this->defauteApp;
    }

    public function setDefauteApp($defauteApp)
    {
        $this->defauteApp = $defauteApp;
        return $this;
    }

    /**
     * 
     * @return IConfigManager 
     */
    public function getConfigManager()
    {
        return $this->configManager;
    }

    public function setConfigManager(IConfigManager $configManager)
    {
        $this->configManager = $configManager;
        return $this;
    }

    public static function getApps()
    {
        $apps = glob(APP_PATH_BASE . DS . "apps" . DS . "*.php");
        $apps = array_map(function($value){
            return basename($value, ".php");
        }, $apps);
        //return array("admin","site","install");
        return $apps;
    }
}

?>