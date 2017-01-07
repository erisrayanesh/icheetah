<?php

namespace ICheetah\Http;

class Messages
{
    
    CONST MESSAGES_FIELD = "messages";
    
    CONST MESSAGE_INFO = 1;
    CONST MESSAGE_SUCCESS = 2;
    CONST MESSAGE_CRITICAL = 3;
    CONST MESSAGE_WARNING = 4;
//    
//    /**
//     * @return Messages
//     */
//    public static function getInstance()
//    {
//        parent::getInstance();
//    }
    
    public static function add($message, $type = 1, $details = "")
    {
        if (!isset($_SESSION[self::MESSAGES_FIELD])){
            return $_SESSION[self::MESSAGES_FIELD] = array();
        }
        $_SESSION[self::MESSAGES_FIELD][] = array("message" => $message,
                                                "type" => $type,
                                                "details" => $details);
    }
    
    public static function clear()
    {
        $_SESSION[self::MESSAGES_FIELD] = array();
    }
    
    public static function count()
    {
        return count(self::all());
    }
    
    public static function all()
    {
        if (isset($_SESSION[self::MESSAGES_FIELD])){
            return $_SESSION[self::MESSAGES_FIELD];
        } else {
            return $_SESSION[self::MESSAGES_FIELD] = array();
        }
    }
    
    public static function iterator()
    {
        return new \ArrayIterator($_SESSION[self::MESSAGES_FIELD]) ;
    }
    
}
?>