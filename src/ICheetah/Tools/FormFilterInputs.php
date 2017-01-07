<?php

namespace ICheetah\Tools;

class FormFilterInputs
{
    
    private $filterVar = "filter";
    private $allowEmpty = false;
    private $data = array();
    
    
    public static function init($setup = true, $filterVar = "filter", $allowEmpty = false)
    {
        $filter = new static($filterVar);
        $filter->setAllowEmpty($allowEmpty);
        if ($setup) {
            $filter->setup();
        }
        return $filter;
    }

    public function __construct($filterVar = "filter")
    {
        $this->setFilterVar($filterVar);
    }
    
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function merge($array)
    {
        foreach ($array as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }
    
    public function get($name, $filter = null, $options = null)
    {
        if (array_key_exists($name, $this->data)) {
            $value = $this->data[$name];
            if (!is_null($filter)){
                $value = Inputs::filter($value, $filter, $options);            
            }
        } else {
            $value = null;
        }        
        return $value;
    }

    public function has($name)
    {
        return isset($this->$name);
    }
    
    public function quoted($name)
    {
        return "'".$this->$name."'";
    }

    public function getFilterVar()
    {
        return $this->filterVar;
    }

    public function setFilterVar($variableName)
    {
        $this->filterVar = $variableName;
        return $this;
    }

    public function getAllowEmpty()
    {
        return $this->allowEmpty;
    }

    public function setAllowEmpty($allowEmpty)
    {
        $this->allowEmpty = $allowEmpty;
        return $this;
    }

    public function setup()
    {
        $filter = \Libs\Inputs::get($this->getFilterVar(), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
        if (is_array($filter)) {
            if (!$this->getAllowEmpty()) {
                foreach ($filter as $key => $value) {
                    if (empty(trim($value))) {
                        unset($filter[$key]);
                    }
                }
            }
            $this->merge($filter);
        }
    }
    
    public function toArray()
    {
        return $this->data;
    }
    
    public function toJSON($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
    
    public function isEmpty()
    {
        return count($this->data) == 0;
    }

}

?>