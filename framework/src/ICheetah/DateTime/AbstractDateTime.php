<?php
namespace ICheetah\DateTime;

abstract class AbstractDateTime implements IDateTime
{
    
    private $format = "Y-m-d H:i:s";
    private $timezone = "Europe/London";
    private $timestamp = 0;
    
    public function __construct($timestamp = null, $format = null, $timezone = null)
    {
        $this->setTimestamp(time());
        $this->setTimeZone($timezone);
        $this->setFormat($format);
        $this->setTimestamp($timestamp);
        
//        $a = new DateTime();
//        $a->
    }
    
    public function format()
    {
        return $this->format;
    }

    public function setFormat($format)
    {
        if ($format != null && $format != ""){
            $this->format = $format;
        }
    }
    
    public function timezone()
    {
        return $this->timezone;
    }

    public function setTimeZone($timezone)
    {
        if ($timezone != null && $timezone != ""){
            $this->timezone = $timezone;
        }
    }
    
    public function timestamp() 
    {
        return $this->timestamp;
    }

    public function setTimestamp($timestamp)
    {
        if ($timestamp != null){
            $this->timestamp = $timestamp;
        }
    }    
    
}

?>