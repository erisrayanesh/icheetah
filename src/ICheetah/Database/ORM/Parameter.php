<?php
namespace Rafael\Database\ORM;

class Parameter
{
   
    const TYPE_INT = 1;
    const TYPE_VARCHAR = 2;
    const TYPE_TEXT = 3;
    const TYPE_DATE = 4;
    const TYPE_TIME = 5;
    const TYPE_DATETIME = 6;
    const TYPE_BOOLEAN = 7;
    const TYPE_RAW = 8;
    
    
    const UNSIGNED_1B = 1;
    const SIGNED_1B = 2;
    const UNSIGNED_2B = 3;
    const SIGNED_2B = 4;
    const UNSIGNED_3B = 5;
    const SIGNED_3B = 6;
    const UNSIGNED_4B = 7;
    const SIGNED_4B = 8;
    const UNSIGNED_8B = 9;
    const SIGNED_8B = 10;
    
    /**
     *
     * @var String 
     */
    private $strName;
    
    /**
     *
     * @var object 
     */
    private $value = null;

    /**
     *
     * @var Integer 
     */
    private $intType;
    
    /**
     *
     * @var Integer 
     */
    private $intSize = 0;

    private $isEscapeQuotes = false;

    /**
     *
     * @param String $name
     * @param String $type
     * @param String $size
     * @param String $column
     * @param object $value 
     */
    public function __construct ($name, $type, $size = null, $value = null)        
    {
        $this->setName($name);
        $this->setSize($size);
        $this->setValue($value);
        $this->setType($type);
    }

    public function __toString ()        
    {
        return $this->toString();
    }

    public function toString ()        
    {
        return $this->get();
    }

    public function get ()
    {
        switch ($this->type()) {
            case SQLParameter::TYPE_INT:
                return $this->toInt();
                break;
            case SQLParameter::TYPE_VARCHAR:
            case SQLParameter::TYPE_TEXT:
                return $this->toChar();
                break;
            case SQLParameter::TYPE_DATE:
                $this->setSize(10);
                return $this->toChar();
                break;
            case SQLParameter::TYPE_TIME:
                $this->setSize(8);
                return $this->toChar();
                break;
            case SQLParameter::TYPE_DATETIME:
                $this->setSize(19);
                return $this->toChar();
                break;
            case SQLParameter::TYPE_BOOLEAN:
                if ($this->value() !== null && $this->value() !== false && $this->value() != 0 && strlen($this->value()) > 0)
                    return 1;
                else
                    return 0;
                break;
            case SQLParameter::TYPE_RAW:
                return $this->value();
        }
    }
       
    public function name()
    {
        return $this->strName;
    }

    public function setName($name)
    {
        $this->strName = $name;
    }

    public function value()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function type()
    {
        return $this->intType;
    }

    public function setType($type)
    {
        $this->intType = Convert::toInt($type);
    }

    public function size()
    {
        return $this->intSize;
    }

    public function setSize($size)
    {
        $this->intSize = Convert::toInt($size);
    }

    public function column()
    {
        return $this->column;
    }

    public function setColumn($column)
    {
        $this->column = $column;
    }
    
    public function escapeQuotes()
    {
        return $this->isEscapeQuotes;
    }

    public function setEscapeQuotes($escapeQuotes)
    {
        $this->isEscapeQuotes = \Convert::toBool($escapeQuotes);
    }
    
    
    protected function toInt ()
    {   
        if (!is_int($this->value())) {
            $this->setValue(intval($this->value()));
        }
        
        switch ($this->size()) {
            case SQLParameter::UNSIGNED_1B:
                return $this->toRange($this->value(), 0, 255);
                break;
            case SQLParameter::SIGNED_1B:
                return $this->toRange($this->value(), -128, 127);
                break;
            case SQLParameter::UNSIGNED_2B:
                return $this->toRange($this->value(), 0, 65535);
                break;
            case SQLParameter::SIGNED_2B:
                return $this->toRange($this->value(), -32768, 32767);
                break;
            case SQLParameter::UNSIGNED_3B:
                return $this->toRange($this->value(), 0, 16777215);
                break;
            case SQLParameter::SIGNED_3B:
                return $this->toRange($this->value(), -8388608, 8388607);
                break;
            case SQLParameter::UNSIGNED_4B:
                return $this->toRange($this->value(), 0, 4294967295);
                break;
            case SQLParameter::SIGNED_4B:
                return $this->toRange($this->value(), -2147483648, 2147483647);
                break;
            case SQLParameter::UNSIGNED_8B:
                return $this->toRange($this->value(), 0, 18446744073709551615);
                break;
            case SQLParameter::SIGNED_8B:
                return $this->toRange($this->value(), -9223372036854775808, 9223372036854775807);
                break;
            default :
                return $this->value();
                break;
        }
        
    }
    
    protected function toChar()
    {
        $retVal = strval($this->value());

        if ($this->size() > 0){
            //$retVal = substr($retVal, 0, $this->size());
            $retVal = mb_substr($retVal, 0, $this->size(), 'UTF-8');
        }
        
        if (!$this->escapeQuotes()){
            $retVal =  "'$retVal'";
        }
        
        return $retVal;
    }

    protected function toRange($value, $min, $max)
    {
        if ($value >= $min && $value <= $max){
            return $value;
        } else if ($value < $min){
            return $min; 
        } else if ($value > $max) {
            return $max;
        }
      
    } 


}
?>