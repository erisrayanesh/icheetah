<?php

namespace ICheetah\Traits;

trait StdArrayAccess
{
    /**
     * array items
     */
    protected $items = array();
    
    
    protected function isInRange($index)
    {
        return is_int($index) && ($index < $this->count()) && ($index > -1);
    }
    
    public function toArray()
    {
        return $this->items;
    }

    public function count()
    {
        return count($this->items);
    }
    
    public function clear()
    {
        $this->items = array();
    }
    
    public function merge($array)
    {
        if ($array instanceof \stdClass){
            $array = json_decode(json_encode($array), true);
        }
        
        if ($array instanceof static){
            $array = $array->toArray();
        }
        
        if (is_array($array)){
            $this->items = array_merge($this->items, $array);            
        }
        return $this;
    }
    
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __isset($name)
    {
        return isset($this->items[$name]);
    }

    public function __unset($name)
    {
        unset($this->items[$name]);
    }
    
    public function get($key, $default = null)
    {
        if (isset($this->items[$key])){
            return $this->items[$key];
        } else {
            return $default;
        }
    }
    
    public function getInt($key, $default = null)
    {
        return \ICheetah\Convert::toInt($this->get($key, $default));
    }
    
    public function getFloat($key, $default = null)
    {
        return \ICheetah\Convert::toFloat($this->get($key, $default));
    }
    
    public function exist($value, $strict = false)
    {
        return in_array($value, $this->items, $strict);
    }
    
    public function keyOf($value, $strict = false)
    {
        return array_search($value, $this->items, $strict);
    }
    
    public function keyExist($key)
    {
        return array_key_exists($key, $this->items);
    }

    public function add($item)
    {
        $this->items[] = $item;
    }
    
    public function remove($item, $strict = false)
    {
        $keys = $this->keysOf($item, $strict);
        if (!empty($keys) && is_array($keys)){
            foreach ($keys as $key) {
                unset($this->items[$key]);
            }
        }
        return $this;
    }
    
    public function removeAt($key)
    {
        if (is_array($key)){
            foreach ($key as $k) {
                $this->removeAt($k);
            }
        } else {
            if ($this->keyExist($key)){
                unset($this->items[$key]);
            }
        }        
        return $this;
    }
    
    public function insert($key, $item)
    {
        if ($this->isInRange($key)) {
            $lst = array_splice($this->items, $key, 0, array($item));
            return is_array($lst);
        } elseif ($key < 0) {
            return $this->insert(0, $item);
        } elseif ($key >= $this->count()) {
            return $this->add($item);
        }
    }

    public function set($key, $item)
    {
        if (!is_null($key)){
            $this->items[$key] = $item;            
        }
    }
    
    public function first()
    {
        if (count($this->items)){
            return array_values($this->items)[0];            
        } else {
            return null;
        }
    }
    
    public function last()
    {
        return array_pop(array_values($this->items));
    }
    
    public function isEmpty()
    {
        return $this->count() == 0;
    }
    
    public function isNotEmpty()
    {
        return $this->count() > 0;
    }
    
    public function extract($key)
    {
        $retVal = $this->get($key);
        if ($retVal != null) {
            $this->removeAt($key);
        }
    }
    
    public function prepend($value)
    {
        array_unshift($this->items, $value);
    }
    
    public function extractFirst()
    {
        return array_shift($this->items);
    }
    
    public function extractLast()
    {
        return array_pop($this->items);
    }
    
    /**
     * Returns a new collection of filtered items
     * @param string|Callable $filter
     * @return \ICheetah\Collection
     */
    public function where($filter)
    {
        $filtered = [];

        foreach ($this->items as $key => $value) {
            //Check if $filter is a callable
            if (is_callable($filter) && call_user_func($filter, $key, $value) === true) {
                $filtered[$key] = $value;
            }
            
            if (is_array($filter && in_array($value, $filtered) === true)){
                $filtered[$key] = $value;
            }
            
            //check if $filter is a regexpr string
            if (is_string($filter) && preg_match($filter, $value)) {
                $filtered[$key] = $value;                    
            }
        }

        return new static($filtered);
    }
    
    public function each(callable $callback)
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }
        return $this;
    }
    
}

?>