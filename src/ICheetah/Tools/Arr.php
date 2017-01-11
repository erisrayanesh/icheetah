<?php

namespace ICheetah\Tools;

use ICheetah\Tools\Validator;

class Arr
{
    public static function get(array $array, $key, $default = null, $filterID = null, $options = null)
    {
        $retVal = $default;
        
        if (str($key)->has(".")){
            $parts = str($key)->split(".");
            $key = $parts->extractLast();
            foreach ($parts as $k) {
                if (isset($array[$k]) && is_array($array[$k])){
                    $array = $array[$k];
                } else {
                    break;
                }
            }
        }        
                
        if (is_array($array)){
            $retVal = isset($array[$key]) ? $array[$key] : $default;
        }
        
        //Debug::out("befor $name = $retVal\n", true);
        $retVal = Validator::filter($retVal, $filterID, $options);        
        //Debug::out("befor $name = " . print_r($retVal, true) . "\n", true);        
        return $retVal;
    }
    
    public static function set(array &$array, $key, $value)
    {
        if(is_array($array)){
            $array[$key] = $value;   
        }
    }
    
    public static function append(array &$array, $value)
    {
        if(is_array($array)){
            $array[] = $value;
        }
    }
    
    public static function add(array &$array, $value)
    {
        self::append($array, $value);
    }
    
    public static function delete(array &$array, $key)
    {
        if(is_array($array)){
            unset ($array[$key]);
        }
    }
        
    public static function has(array $array, $value)
    {
        return array_search($value, $array) !== false;
    }
    
    public static function hasKey(array $array, $key)
    {
        return array_key_exists($key, $array);
    }
    
    public static function isAssociative(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
    
    public static function isAssoc(array $arr)
    {
        return $this->isAssociative($arr);
    }
        
    public static function flatten($array, $depth = INF)
    {
        $result = [];
        foreach ($array as $item) {
            $item = $item instanceof Collection ? $item->toArray() : $item;

            if (is_array($item)) {
                if ($depth === 1) {
                    $result = array_merge($result, $item);
                    continue;
                }

                $result = array_merge($result, static::flatten($item, $depth - 1));
                continue;
            }

            $result[] = $item;
        }
        return $result;
    }
    
    public static function flattenWithKeys($array, $prefix = '', $depth = INF)
    {
        $result = [];
        foreach ($array as $key => $item) {
            $item = $item instanceof Collection ? $item->toArray() : $item;
            $newKey = $prefix . (empty($prefix) ? '' : '.') . $key;
            if (is_array($item)) {

                if ($depth === 1) {
                    $result[$newKey] = $item;
                    continue;
                }

                $result = array_merge($result, self::flattenWithKeys($item, $newKey, $depth - 1));
                continue;
            }

            $result[$newKey] = $item;
        }
        return $result;
    }
}

?>