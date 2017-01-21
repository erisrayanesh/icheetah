<?php

if (!function_exists("collect")){
    function collect($items)
    {
        return new \ICheetah\Tools\Collection($items);
    }
}

if (!function_exists("string")){
    /**
     * Returns a string object
     * @param string $str
     * @return \ICheetah\Tools\String
     */
    function string($str)
    {
        return new ICheetah\Tools\String($str);
    }
}

if (!function_exists("str")){
    /**
     * Alias to string function
     * @param string $str
     * @return \ICheetah\Tools\String
     */
    function str($str)
    {
        return string($str);
    }
}

if (!function_exists("str_to_camel_case")){
    function str_to_camel_case($value, $wildcard = '_')
    {
        return str($value)->toCamelCase($wildcard)->toString();
    }
}

if (!function_exists("str_to_studly_case")){
    function str_to_studly_case($value, $wildcard = '_', $includeWildcard = false)
    {
        return str($value)->toStudlyCase($wildcard, $includeWildcard)->toString();
    }
}

if (!function_exists("str_to_snake_case")){
    function str_to_snake_case($value, $wildcard = '_')   
    {
        return str($value)->toSnakeCase($wildcard)->toString();
    }
}

if (!function_exists("str_to_title_case")){
    function str_to_title_case($value)
    {
        return str($value)->toTitleCase()->toString();
    }
}

if (!function_exists("get_real_class_name")){
    
    /**
     * Returns well class name
     * @param string $class
     * @return string
     */
    function get_real_class_name($class)
    {
        return implode("", array_map("ucfirst", explode("_", $class)));
    }
}

if (!function_exists("get_class_name")){
    /**
     * Returns only class name
     * @param string $class
     * @return string
     */
    function get_class_name($class)
    {
        return end(explode("\\", $class));
    }
}

if (!function_exists("normalize_class_name")){
    function normalize_class_name($class)
    {
        $name = get_class_name($class);
        //replace it in original class name
        $class = str_replace($name, get_real_class_name(get_class_name($name)), $class);
        return str_to_studly_case($class, "\\", true);        
    }
}

if (!function_exists("config")){
    function config($key, $default = null)
    {
        return \ICheetah\Foundation\Config::get($key, $default);
    }
}

if (!function_exists("logger")){
    /**
     * 
     * @return ICheetah\Tools\Log
     */
    function logger($message, array $context = array())
    {
        $instance = ICheetah\Tools\Log::getInstance();
        if (!empty($message)){
            $instance->debug($message, $context);
        }
        return $instance;
    }
}

if (!function_exists("absPath")){
    /**
     * 
     * @return ICheetah\Tools\Log
     */
    function absPath($relativePath = "")
    {
        return ICheetah\Tools\Findder::getAbsPath($relativePath);
    }
}

if (!function_exists("app")){
    /**
     * 
     * @return \ICheetah\Application\Application
     */
    function app()
    {
        return \ICheetah\Application\Application::getInstance();
    }
}

if (!function_exists("cache")){
    /**
     * 
     * @return ICheetah\Cache\CanineCache
     */
    function cache()
    {
        return \ICheetah\Cache\CanineCache::getInstance();
    }
}

if (!function_exists("view")){
    /**
     * 
     * @param string $strName view name
     * @param array $data
     * @return \ICheetah\View\View
     */
    function view($strName, array $data = [])
    {
        return new ICheetah\View\View($strName, $data);       
    }
}

?>