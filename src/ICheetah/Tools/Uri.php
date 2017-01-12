<?php

namespace ICheetah\Tools;

use ICheetah\Http\Request;

class Uri
{
            
    protected $uri;
    public $parsed;    
    protected $query;
            
    public function __construct($uri = "")
    {
        $this->parsed = parse_url($uri);
        $this->uri = $uri;
    }
    
    public function getUri()
    {
        return $this->uri;
    }
    
    public function __get($name)
    {
        if (array_key_exists($name, $this->parsed)){
            return $this->parsed[$name];
        } else {
            return null;
        }
    }
    
    /**
     * 
     * @return \ICheetah\Uri
     */
    public static function getRootUrl()
    {
        $isHTTPS = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
        $port = (isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));
        $port = ($port) ? ':' . $_SERVER["SERVER_PORT"] : '';        
        $url = ($isHTTPS ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . $port;
        return new static($url);
    }
    
    /**
     * 
     * @return \ICheetah\Uri
     */
    public static function getScriptUri($includeRoot = false)
    {
        //Define app uri
        $file = Request::server("SCRIPT_NAME", "");
        $file = explode("/", $file);
        array_pop($file);
        return new static($includeRoot? self::getRootUrl()->getUri() . "/" : "" . implode("/", $file));
    }
       
    /**
     * 
     * @return string
     */
    public static function getSegmentsString()
    {
        //Get request uri
        $uri = Request::server("REQUEST_URI", "");
        //Debug::out(print_r($uri, true) . "\n" , true);
        $uri = trim($uri, "\/");
        $uri = str_replace("\\", "/", $uri);
        //Get executed file path
        $file = Request::server("SCRIPT_NAME", "");
        $file = trim($file, "\/");
        //remove query string from request
        $query = Request::server("QUERY_STRING", "");
        $uri = str_replace("?".$query, "", $uri);
        //split file path to array
        $file = explode("/", $file);
        //remove last item which is the file and extention name
        $filename = array_pop($file);
        //join file path to string.
        //Now $file is parent directory of execute file
        $file = implode("/", $file);
        //remove file directory path from request uri
        $uri = str_replace($file, "", $uri);
        //remove file name from anywhere in request uri
        $uri = str_replace($filename, "", $uri);
        //remove any double slash to single slash
        $uri = str_replace("//", "/", $uri);
        //remove any slashes from sides of string
        $uri = trim($uri, "\/");
        return $uri;
    }
    
    /**
     * 
     * @return \ICheetah\Tools\Collection
     */
    public static function getSegments()
    {
        $uri = self::getSegmentsString();        
        if (!empty($uri)){
            //split uri to array
            $uri = explode("/", $uri);            
        } else {
            $uri = array();
        }
        return new Collection($uri);
        
    }
}

?>