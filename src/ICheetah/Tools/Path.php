<?php

namespace ICheetah\Tools;

class Path
{
    public static function clean($path, $allowDots = true)
    {
        if (!$allowDots) {
            //remove sequential dot
            $path = preg_replace("/\.+/", "", $path);
        }
        
        //remove backslash
        $path = preg_replace("/\\+/", "\\", $path);
        
        //remove sequential slash
        $path = preg_replace("/\/+/", "/", $path);
        
        return $path;
    }
    
    public static function split($path, $delimiter = "/")
    {
        return explode($delimiter, $path);
    }
    
    public static function join($pieces, $delimiter = "/")
    {
        return join($delimiter, $pieces);
    }
    
    public static function switchSeparator($path, $from, $to)
    {
        return self::join(self::split($path, $from) , $to);
    }
    
}