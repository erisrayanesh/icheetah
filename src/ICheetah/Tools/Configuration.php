<?php

namespace ICheetah\Tools;

use ICheetah\Tools\Collection;
use ICheetah\Tools\Convert;
use ICheetah\UI\Parameters\Parameters;

class Configuration implements \ICheetah\Foundation\IConfigManager
{
            
    public static function getDOMDoc($category)
    {
        return Tools\XML::openDOMDoc(self::getCategoryDocPath($category));
    }
    
    public static function getCategoryDocPath($category)
    {
        if (strpos($category, ".")){
            $category = str_replace(".", DS, $category);
        }        
        return APP_PATH_BASE . DS . "res" . DS . "settings" . DS . $category .  ".xml";
    }

    public static function getTitle($category)
    {
        $category = Convert::snakeToCamelCase(strtolower($category));
        $doc = self::getDOMDoc($category);
        if ($doc !== false){
            return $doc->documentElement->getAttribute("title");            
        } else {
            return null;
        }
    }

    /**
     * Prepares settings form fields
     * @param string $category settings file name
     * @param string $options if true, individual data options will loaded
     * @return array
     */
    public static function getFields($category, $options = false)
    {
        $category = Convert::snakeToCamelCase(strtolower($category));
        if ($options){
            $category = "options.$category";
        }
        $category = self::getCategoryDocPath($category);        
        return Parameters\Parameters::getFields($category);
    }
    
    public static function getDefaultValues($category)
    {
        $category = Convert::snakeToCamelCase(strtolower($category));
        return Parameters::getDefaultValues(self::getCategoryDocPath($category));
    }
    
    /**
     * Returns saved value of a given category
     * @param string $category category name
     * @param string $name option name
     * @return \Models\Settings
     */
    public static function getDatabaseValues($category, $name = null)
    {
        $me = new static();
        $model = new \Models\Settings();
        $model->where("category =  " . $model->quoted($category));
        if (!empty($name)){
            $model->where("name =  " . $model->quoted($name));
        }
        return $model;
    }    
    
    public static function __callStatic($name, $arguments)
    {
        return self::get(str_replace("_", ".", $name), $arguments);
    }
    
    /**
     * Get final values of category or given setting name
     * @param string $name = null
     * @param array $default array of individual settings to merge with category settings value
     * @return Collection|null
     */
    public static function get($name = null, $default = null)
    {
        
        if (!empty($default)){
            if (!is_array($default)){
                //Options for individual record
                $default = \Libs\Configuration::decode($default);
            }
        } else {
            $default = array();
        }
        
        list ($category, $name) = self::getOptionInfo($name);
        
        
//        if (is_array($name)){
//                        
//            $settings = new \ICheetah\Collection();
//            foreach ($name as $item) {
//                $val = self::getValues($item);
//                $settings->merge($val);                    
//            }
//            //$settings->merge($values);
//            
//            if ($name != null){
//                return $settings->get($name);
//            }
//            
//            return $settings;
//        }
        
        //Default options values
        $modelOptions = self::getDefaultValues($category);
        
        //Options saved values
        $dbOptions = self::getDatabaseValues($category);
        
        foreach ($dbOptions->get() as $opt) {
            $modelOptions[$opt->name] = $opt->value;
        }
        
        //$retVal = \ICheetah\Collection::from(array_merge($modelOptions, $default));
        $retVal = Collection::from($modelOptions);
        
        if ($name != null) {
            //$retVal = isset($modelOptions[$name]) ? $modelOptions[$name] : null;
            $retVal = $retVal->get($name, !is_array($default)? $default : null);
        }
        
        
        //if $retVal is still an instance of collection,
        // then merges the $default into collection
        if ($retVal instanceof Collection && is_array($default)) {
            $retVal->merge($default);
        }
        
        //Debug::out(print_r($retVal, true), true);
        return $retVal;
    }
    
    public static function getInt($key, $default)
    {
        
    }
    
    public static function getFloat($key, $default)
    {
        
    }
    
    public static function getMultiple(array $categories, $name = null, $default = null)
    {
        $name = !empty($name)? ".$name" : null;
        foreach ($categories as $cat) {
            $values = self::get($cat.$name);
        }
    }
    
    /**
     * Adds a new option value
     * @param string $category
     * @param string $name
     * @param string $value
     * @return bool
     */
    public static function add($name, $value)
    {
        list ($category, $name) = self::getOptionInfo($name);
        
        $me = new static();
        $me->model("Settings");        
        $model = new \Models\Settings();
        $model->category = $category;
        $model->name = $name;
        $model->value = $value;
        return $model->save();
    }
    
    /**
     * Remove Option(s)
     * @param string $category
     * @param string $name
     * @param \Models\Settings $value
     */
    public static function remove($name = null)
    {
        $me = new static();
        $me->model("Settings");        
        $model = new \Models\Settings();
        
        if (!empty($name)){
            
            list ($category, $name) = self::getOptionInfo($name);
            
            if (!empty($category)){
                $model->where("category =  " . $model->quoted($category));
            }
            
            if (!empty($name)){
                $model->where("name = " . $model->quoted($name));
            }
        }
                
        $model->get();
        
        foreach ($model as $value) {
            $value->delete();
        }
        
    }
    
    /**
     * Decodes string to array
     * @param string $options string to decode
     * @return array
     */
    public static function decode($options)
    {
        return json_decode($options, true);
    }
    
    /**
     * Encodes array to string
     * @param array $options
     * @return string
     */
    public static function encode($options)
    {
        $options = array_filter($options, function($value){ 
            return strlen($value) > 0;
        });
        return json_encode($options);
    }
    
    
    protected static function getOptionInfo($name)
    {
        $retVal = array(
            0 => $name,
            1 => null
        );
        if (strpos($name, ".")){
            $name = explode(".", $name);
            $retVal[1] = array_pop($name);
            $retVal[0] = array_pop($name);
        }
        return $retVal;
    }
}

?>