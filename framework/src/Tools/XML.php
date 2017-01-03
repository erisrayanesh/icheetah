<?php

namespace ICheetah\Tools;

class XML
{
    /**
     * Opens a dom document object
     * @param string $file
     * @return \DOMDocument|boolean
     */
    public static function openDOMDoc($file)
    {
        if (file_exists($file)){
            $doc = new \DOMDocument("1.0", "utf-8");
            if ($doc->load($file) !== false){
                return $doc;
            } else {
                return false;
            }            
        } else {
            return false;
        }
    }
    
    public static function createDOMDoc($file = null)
    {
        $doc = new \DOMDocument("1.0", "utf-8");
        if ($file  != null && !file_exists($file)){
            $doc->save($file);
        }
        return $doc;
    }
}

?>