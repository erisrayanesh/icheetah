<?php
namespace ICheetah\Tools;

class Collection implements \ArrayAccess, \Iterator
{
    
    use \ICheetah\Traits\StdArrayAccess;
    
    public function __construct($items = array())
    {
        $this->merge($items);
    }
    
    public static function from($items = array())
    {
        return new static($items);
    }
    
    public static function has($array, $value, $strict = false)
    {
        $retVal = false;
        if (is_array($array) && array_search($value, $array, $strict) !== false){
            return true;
        }
        return $retVal;
    }
    
    public static function hasKey($array, $key)
    {
        $retVal = false;
        if (is_array($array) && array_key_exists($key, $array) !== false){
            return true;
        }
        return $retVal;
    }

    //ArrayAccess
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->items[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->items[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    //Iterator
    
    public function current()
    {
        return current($this->items);
    }

    public function key()
    {
        return key($this->items);
    }

    public function next()
    {
        return next($this->items);
    }

    public function rewind()
    {
        reset($this->items);
    }

    public function valid()
    {
        $key = key($this->items);
        $var = ($key !== null && $key !== false);
        return $var;
    }
        
}

?>