<?php

namespace ICheetah\Tools;

class Uploader
{
    
    /**
     * 
     * @param array $files
     * @param string $targetDir
     * @param int $limit
     * @param bool $hash
     * @param int $maxFileSize
     * @param bool $overWrite
     * @return array
     */
    public static function uploadFiles(array $files, $targetDir, $limit = 0, $hash = false, $maxFileSize = 2000000, $overWrite = true)
    {
        $filesPath = array();
        $filesCount = count($files);
        if ($limit > 0){
            $filesCount = \Libs\Convert::toRange($filesCount, 0, $limit);            
        }
        
        for ($i = 0; $i < $filesCount; $i++) {
            $file = $files[$i];
            $retVal = self::uploadFile($file, $targetDir, $hash, $maxFileSize, $overWrite);
            if ($retVal != false){
                $filesPath[] = $retVal;
            }
        }
        
        return $filesPath;
    }
    
    /**
     * 
     * @param array $file
     * @param string $targetDir
     * @param boolean $hash
     * @param int $maxFileSize
     * @param boolean $overWrite
     * @return string|boolean
     */
    public static function uploadFile($file, $targetDir, $hash = false, $maxFileSize = 2000000, $overWrite = true)
    {
        $retVal = false;
        try {
            
            if((int)$file["error"] == 0 && 
                    (int)$file["size"] > 0 && 
                    (int)$file["size"] <= $maxFileSize) {
                
                $fileType = pathinfo($file["name"], PATHINFO_EXTENSION);
                $fileName = basename($file["name"], $fileType);
                
                if ($hash){
                    $fileName = md5($fileName);
                }
                
                if (!is_writable($targetDir)){
                    chmod($targetDir, "0755");
                }
                
                $targetFile = $targetDir . DS . $fileName . ".$fileType";
                
                if ($overWrite == false && file_exists($targetFile)){
                    $retVal = false;
                }
                
                if (move_uploaded_file($file["tmp_name"], $targetFile)){
                    $retVal = $targetFile;
                } else {
                    $retVal = false;
                }
                
                
            }
        } catch (\RuntimeException $exc) {
            $retVal = false;
        }
        
        return $retVal;
    }
    
}

?>