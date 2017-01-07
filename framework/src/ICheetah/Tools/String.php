<?php

namespace ICheetah\Tools;

class String implements \ICheetah\Foundation\IConvertable
{

    const CASE_SENSETIVE = 0;
    const CASE_INSENSETIVE = 1;
    
    const TRIM_BOTH = 0;
    const TRIM_LEFT = 1;
    const TRIM_RIGHT = 2;

    /**
     *
     * @var String 
     */
    private $str;

    /**
     * Creates new string object
     * @param string $string
     * @return String
     */
    public static function str($string)
    {
        return new self($string);
    }

    /**
     * 
     * @param string $string
     */
    public function __construct($string)
    {
        $this->set($string);
    }

    public function __toString ()
    {
        return $this->str;
    }
    
    public function set($string)
    {
        if (!is_string($string))
            $this->str = Convert::toString($string);
        else
            $this->str = $string;
    }
    
    public function __invoke($str)
    {
        $this->set($str);
    }
    
    public function toString()
    {
        return $this->str;
    }
    
    public function toBool()
    {
        return Convert::toBool($this->str);
    }

    public function toFloat()
    {
        return Convert::toFloat($this->str);
    }

    public function toInt()
    {
        return Convert::toInt($this->str);
    }

    public function toInteger()
    {
        return $this->toInt();
    }

    /**
     * Returns string lenght.
     * @return integer
     */
    public function lenght()
    {
        return strlen($this->str);
    }

    /**
     * 
     * @param string $strText
     * @return boolean
     */
    public function beginsWith ($strText)
    {
        // search backwards starting from haystack length characters from the end
        return $strText === "" || strrpos($this->str, $strText, -strlen($this->str)) !== false;
        //return (substr($this->str, 0, strlen($strText)) == $strText);
    }

    public function endsWith ($strText)
    {
        // search forward starting from end minus needle length characters
        return $strText === "" || (($temp = strlen($this->str) - strlen($strText)) >= 0 && strpos($this->str, $strText, $temp) !== false);
        //return (substr($this->str, 0, strlen($strText)) == $strText);
    }

    /**
     * Returns part of string
     * @param int $start Start index
     * @param int $lenght Substring lenght
     * @return string
     */
    public function mid ($start, $lenght = null)
    {
        if (function_exists("mb_substr")){
            return (mb_substr($this->str, $start, $lenght, "utf-8"));
        } else {
            return (substr($this->str, $start, $lenght));
        }
    }
    
    public function left ($lenght = null)
    {
        return $this->mid(0, $lenght);
    }
    
    public function right ($lenght = null)
    {
        return $this->mid(strlen($this->str) - $lenght, $lenght);
    }
    
    /**
     * 
     * @param integer $side
     * @param string $char
     * @return string
     */
    public function trim ($side = self::TRIM_BOTH, $char = ' ')
    {
        switch ($side) {
            case self::TRIM_RIGHT:
                $this->set(rtrim($this->str, $char));
                break;
            case self::TRIM_LEFT:
                $this->set(ltrim($this->str, $char));
                break;
            default:
                $this->set(trim($this->str, $char));
        }
    }

    /**
     * 
     * @param integer $sensetive
     * @param string $replace
     * @param string$subject
     * @param int $count
     * @return mixed
     */
    public function replace ($search, $replace, $sensetive = self::CASE_INSENSETIVE, $count = null)
    {
        if ($sensetive == self::CASE_INSENSETIVE) {
            return str_ireplace($search, $replace, $this->str, $count);
        } elseif ($sensetive == self::CASE_SENSETIVE) {
            return str_replace($search, $replace, $this->str, $count);
        }
    }

    /**
     * 
     * @param string $str
     */
    public function prepend($str)
    {
        if ($str != "") {
            $this->str = $str . $this->str;
        }
        return $this;
    }

    /**
     * 
     * @param String $str
     */
    public function append($str)
    {
        if ($str != ""){
            $this->str .= $str;
        }
        return $this;
    }
    
    public function wrap($begin, $end = null)
    {
        $end = $end ?: $begin;
        if ($begin != ""){
            $this->str = $begin . $this->str . $end;
        }
        return $this;
    }

    /**
     * 
     * @param int $sensetive
     * @param string $str2
     * @return type
     */
    public function compare ($str2, $sensetive = self::CASE_INSENSETIVE)
    {
        if ($sensetive === self::CASE_INSENSETIVE) {
            return strcmp($this->str, $str2);
        } elseif ($sensetive === self::CASE_SENSETIVE) {
            return strcasecmp($this->str, $str2);
        }
    }

    /**
     * 
     * @return string
     */
    public function toUpper()
    {
        $this->str = strtoupper($this->str);
    }

    /**
     * 
     * @return string
     */
    public function toLower()
    {
        $this->str = strtolower($this->str);
    }

    /**
     * Splits string to parts
     * @param string $delimiter
     * @param int $limit
     * @return Collection
     */
    public function split ($delimiter, $limit = null)
    {
        if (!empty($limit) && is_int($limit)){
            $parts = explode($delimiter, $this->str, $limit);            
        } else {
            $parts = explode($delimiter, $this->str);            
        }
        return new Collection($parts);
    }

    /**
     * 
     * @return string
     */
    public function md5 ()
    {
        $this->str = md5($this->str);
    }
    
    /**
     * 
     * @return string
     */
    public function sha1($raw_output = false)
    {
        $this->str = sha1($this->str, $raw_output);
    }
    
    /**
     * 
     * @return string
     */
    public function reverse ()
    {
        $this->str = strrev($this->str);
    }

    /**
     * 
     * @param string $needle
     * @param int $offset
     * @param int $sensetive
     * @return int
     */
    public function find($str, $offset, $sensetive = self::CASE_INSENSETIVE)
    {
        if ($sensetive === self::CASE_INSENSETIVE) {
            return stripos($this->str, $str, $offset);
        } elseif ($sensetive === self::CASE_SENSETIVE) {
            return stripos($this->str, $str, $offset);
        }
    }   

    /**
     * 
     * @param string $str
     * @param int $sensetive
     * @return int
     */
    public function findFirstPos ($str, $sensetive = self::CASE_INSENSETIVE)
    {
        if ($sensetive === self::CASE_INSENSETIVE) {
            return strpos($this->str, $str);
        } elseif ($sensetive === self::CASE_SENSETIVE) {
            return stripos($this->str, $str);
        }
    }

    /**
     * 
     * @param String $str
     * @param int $sensetive
     * @return int
     */
    public function findLastPos ($str, $sensetive = self::CASE_INSENSETIVE)
    {
        if ($sensetive === self::CASE_INSENSETIVE) {
            return strrpos($this->str, $str);
        } elseif ($sensetive === self::CASE_SENSETIVE) {
            return strripos($this->str, $str);
        }
    }
    
    public function has($str, $sensetive = self::CASE_INSENSETIVE)
    {
        return $this->findFirstPos($str, $sensetive);
    }

    public function insert($insertion, $pos)
    {
        $this->str = substr($this->str, $insertion, $pos, 0);
        return $this;
    }
    
    public function toCamelCase($wildcard = "_", $includeWildcard = false)
    {
        $this->str = lcfirst($this->toStudlyCase($wildcard));
        return $this;
    }
    
    public function toStudlyCase($wildcard = "_", $includeWildcard = false)
    {
        if ($includeWildcard){
            //$this->str = preg_replace("/(?<=\\$wildcard)([A-Za-z])/", "\\U$1", $this->str);
            $this->str = implode($wildcard, array_map("ucfirst", explode($wildcard, $this->str)));
        } else {
            $this->str = str_replace(' ', '', ucwords(str_replace($wildcard, ' ', $this->str)));            
        }
        
        
        //$this->str = str_replace(' ', '', ucwords(str_replace($wildcard, ' ', $this->str)));
        return $this;
    }
    
    public function toSnakeCase($wildcard = '_')
    {
        if (! ctype_lower($this->str)) {
            $this->str = preg_replace('/\s+/', '', $this->str);
            $this->str = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1' . $wildcard, $this->str));
        }
        return $this;
    }
    
    public function toTitleCase()
    {
        $this->str = mb_convert_case($this->str, MB_CASE_TITLE, 'UTF-8');
        return $this;
    }
    
    
    
    
    
}

?>