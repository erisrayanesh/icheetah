<?php

namespace ICheetah\Http;

use ICheetah\Tools\Arr;
use \ICheetah\Tools\Collection;

class Request
{

    public static $methods = array ("GET", "POST", "PUT", "DELETE");
    
    protected static $currentMethod = null;

    public static function set($name, $value, &$array = null)
    {
        $array = $array ?: $_REQUEST;
        Arr::set($array, $name, $value);
    }

    public static function delete($name, &$array = null)
    {
        $array = $array ?: $_REQUEST;
        Arr::delete($array, $name);
    }
    
    public static function setCookie($name, $value = null, $expire = 0, $path = null, $domain = null, $secure = false, $httponly = false)
    {
        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public static function regenerateGlobalRequest($name = null)
    {
        $order = ini_get('request_order') ?: ini_get('variables_order');
        $order = preg_replace('#[^cgp]#', '', strtolower($order)) ?: 'gp';
        //$order  = strtolower(ini_get("request_order"));
        $_REQUEST = array();
        foreach (str_split($order) as $value) {
            switch ($value) {
                case "g":
                    $_REQUEST = self::merge($_REQUEST, $_GET, $name);
                    break;
                case "p":
                    $_REQUEST = self::merge($_REQUEST, $_POST, $name);
                    break;
                case "c":
                    $_REQUEST = self::merge($_REQUEST, $_COOKIE, $name);
                    break;
            }
        }
    }
    
    protected static function merge(array $firstArr, array $secondArr, $key = null)
    {
        if (!empty($key) && isset($secondArr[$key])){
            $firstArr[$key] = $secondArr[$key];
            return $firstArr;
        } else {
            return array_merge($firstArr, $secondArr);
        }
    }
    
    
    public static function __callStatic($name, $arguments)
    {
        $default = Arr::get($arguments, 0);
        $filterID = Arr::get($arguments, 1);
        $options = Arr::get($arguments, 2);
        return self::input($name, $default, $filterID, $options);
    }
        
    public static function exist($name)
    {
        return Arr::keyExist($_REQUEST, $name);
    }

    public static function allInputs()
    {
        return $_REQUEST;
    }
    
    public static function allInputsOnly($items)
    {
        if (!is_array($items)){
            $items = array($items);
        }
        $retVal = array();
        foreach ($items as $key) {
            self::input($key);
        }
        return $retVal;
    }
    
    public static function allInputsExcept($items)
    {
        if (!is_array($items)){
            $items = array($items);
        }        
        $all = Collection::from(self::allInputs());
        $all->removeAt($items);
        return $all->toArray();
    }

    public static function input($name, $default = null, $filterID = null, $options = null)
    {
        return Arr::get($_REQUEST, $name, $default, $filterID, $options);
    }
    
    public static function get($name, $default, $filterID = null, $options = null)
    {
        return Arr::get($_GET, $name, $default, $filterID, $options);
    }
    
    public static function post($name, $default, $filterID = null, $options = null)
    {
        return Arr::get($_POST, $name, $default, $filterID, $options);
    }
    
    public static function cookie($name, $default, $filterID = null, $options = null)
    {
        return Arr::get($_COOKIE, $name, $default, $filterID, $options);
    }
    
    public static function server($name, $default, $filterID = null, $options = null)
    {
        return Arr::get($_SERVER, $name, $default, $filterID, $options);
    }
    
    public static function session($name, $default, $filterID = null, $options = null)
    {
        return Arr::get($_SESSION, $name, $default, $filterID, $options);
    }
    
    public static function files($name)
    {
        
        $files = array();
        
        //If no file uploaded
        if (!isset($_FILES[$name])) {
            return $files;
        }
        
        $filesInfo = $_FILES[$name];
        
        if (is_array($filesInfo['name'])){
            $filesCount = count($filesInfo['name']);
        } else {
            $filesCount = 1;
        }
        
        for ($i = 0; $i < $filesCount; $i++) {
            foreach ($filesInfo as $key => $value) {
                if (is_array($value)) {
                    $files[$i][$key] = $value[$i];                    
                } else {
                    $files[$i][$key] = $value;
                }
            }
        }

        return $files;
    }
    

    /**
     * 
     * @return Collection
     */
    public static function previous()
    {
        return new Collection(self::session("_request", array()));        
    }
    
    /**
     * Save request variables
     */
    public static function keep()
    {
        self::set("_request", self::allInputs());
    }    

    public static function method()
    {
        if (self::$currentMethod == null){
            $method = self::server("REQUEST_METHOD", "GET");
            $post_method = self::post("_method", null, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW || FILTER_FLAG_STRIP_HIGH);
            self::$currentMethod = strtoupper($post_method ?: $method);
        }
        return self::$currentMethod;
        
    }
    
    public static function isMethod($name)
    {
        return self::method() === strtoupper($name);
    }

    public static function isAjax()
    {
        //Find request is ajax or not
        $ajax = Inputs::get("HTTP_X_REQUESTED_WITH", "", $_SERVER);
        return strtolower($ajax) == 'xmlhttprequest';
    }

    /**
     *Client Browser name
     * @return string
     */
    public static function agent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }
    
    /**
     *Client IP address
     * @return string
     */
    public static function IP()
    {
        return $_SERVER['REMOTE_ADDR'];
    }
    
    /**
     *
     * @param type $user_agent
     * @return string
     */
    public static function browser()
    {
        $browser = "Unknown Browser";

        $browser_array = array(
            '/msie/i' => 'Internet Explorer',
            '/firefox/i' => 'Firefox',
            '/safari/i' => 'Safari',
            '/chrome/i' => 'Chrome',
            '/edge/i' => 'Edge',
            '/opera/i' => 'Opera',
            '/netscape/i' => 'Netscape',
            '/maxthon/i' => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i' => 'Handheld Browser'
        );

        foreach ($browser_array as $regex => $value) {
            if (preg_match($regex, self::agent())) {
                $browser = $value;
                break;
            }
        }

        return $browser;
    }
    
    public static function browserVersion()
    {
        
    }
    
    public static function OS()
    {

        $os_platform = "Unknown OS Platform";

        $os_array = array(
            '/windows nt 10/i' => 'Windows 10',
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        );

        foreach ($os_array as $regex => $value) {

            if (preg_match($regex, self::agent())) {
                $os_platform = $value;
            }
        }

        return $os_platform;
    }
    
    public static function OSVersion()
    {
        return null;
    }
    
    public static function device()
    {
        if(self::isMobile()){
            return 'mobile';
        }  else {
            return 'desktop';
        }
    }
    
    public static function isMobile()
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }
    
}

?>