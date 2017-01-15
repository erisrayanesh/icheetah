<?php

namespace ICheetah\Tools;

class Findder
{
    
    public static function getAbsPath($relativePath = "")
    {
        $absRoot = app()->getRootDir();
        
        if (!empty($relativePath)){
            $relativePath = Path::switchSeparator(Path::clean($relativePath), "/", DIRECTORY_SEPARATOR);
        }
        return $absRoot . DIRECTORY_SEPARATOR . $relativePath;
    }
    
    public static function fileExist($path)
    {
        return file_exists($path);
    }
    
    
    
}
