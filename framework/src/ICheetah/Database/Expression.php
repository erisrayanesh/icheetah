<?php

namespace ICheetah\Database;

class Expression
{

    protected $value;

    
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function value()
    {
        return $this->value;
    }


    public function __toString()
    {
        return (string) $this->getValue();
    }
}
?>