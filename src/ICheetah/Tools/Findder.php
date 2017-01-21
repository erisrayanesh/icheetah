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
    
    public static function isFilename($path)
    {
        return is_file($path);
    }
    
    public static function isFile($path)
    {
        return self::isFilename($path) && self::fileExist($path);
    }

    public static function getContents($file)
    {
        if (self::isFile($file)){
            return file_get_contents($file);
        } else {
            throw new Exceptions\FileNotFoundException();
        }
    }
    
    public static function createFile($filename, $initialContent = null, $overwrite = false)
    {
        if (static::fileExist($filename) && !$overwrite){
            throw new Exceptions\FileDouplicateException();
        }
        
        $dir = dirname($filename);
        if (is_writable($dir) && ($handle = fopen($filename, "w+"))) {
            if (!is_null($initialContent) && fwrite($handle, $initialContent)  === false) {
                throw new Exceptions\FileNotWritableException();
            }
            fclose($handle);
            return true;
        } else {
            throw new Exceptions\FileNotWritableException();
        }        
    }
    
    public static function openFile($filename, $mode, $useIncludPath = false)
    {
        if (!static::isFile($filename)){
            throw new Exceptions\FileNotFoundException();
        }        
        return fopen($filename, $mode, $useIncludPath);            
    }
    
    public static function putContents($file, $contents, $append = false)
    {
        if (is_string($file)) {
            $file = static::openFile($file, $append? "a+" : "w+");
        }
        
        if (is_resource($file)) {
            if (fwrite($file, $contents) === false) {
                throw new Exceptions\FileNotWritableException();
            }
            fclose($file);
        }        
    }

    public static function createTempFile($content = null, $prefix = "tmp")
    {
        $filename = tempnam(config("application.temp"), $prefix);
        if (!is_null($content)){
            static::putContents($filename, $content);
        }
        return $filename;
    }
    
    public static function deleteFile($filename)
    {
        if (static::isFile($filename)) {
            return unlink($filename);
        } else {
            throw new Exceptions\FileNotFoundException();
        }
    }


    // Directory methods
    
    
    
}
