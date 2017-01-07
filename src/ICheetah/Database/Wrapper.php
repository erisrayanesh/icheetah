<?php

namespace ICheetah\Database;

use Rafael\Database\Structure\Expression;

class Wrapper
{
    
    /**
     * Table prefix.
     *
     * @var string
     */
    protected $tablePrefix = '';
    
    /**
     * Table postfix.
     *
     * @var string
     */
    protected $tablePostfix = '';

    protected function quote($value)
    {
        if (is_array($value)){
            array_map([$this, "wrapValue"], $value);
        }
        
        if ($this->isExpression($value)) {
            return $this->value($value);
        }
        
        if ($value === '*') {
            return $value;
        }

        return '"'.str_replace('"', '""', $value).'"';
    }
    
    protected function wrapKeyword($keyword)
    {
        if (is_array($keyword)){
            array_map([$this, "wrapKeyword"], $keyword);
        }
        
        if ($this->isExpression($keyword)) {
            return $this->value($keyword);
        }
        
        if ($keyword === '*') {
            return $keyword;
        }
        
//        if (strpos(strtolower($keyword), ' as ') !== false){
//            
//        }
        
        return "`$keyword`";
    }

    public function unifyColumns(array $columns)
    {
        return implode(', ', array_map([$this, 'wrapKeyword'], $columns));
    }

    public function unifyParameters(array $values)
    {
        return implode(', ', array_map([$this, 'parameter'], $values));
    }

    public function parameter($value)
    {
        return $this->isExpression($value) ? $this->value($value) : '?';
    }

    public function value(Expression $expression)
    {
        return $expression->value();
    }

    public function isExpression($value)
    {
        return $value instanceof Expression;
    }

    
    // Properties
    
    /**
     * Get the grammar's table prefix.
     *
     * @return string
     */
    public function tablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * Set the grammar's table prefix.
     *
     * @param  string  $prefix
     * @return $this
     */
    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;
        return $this;
    }
    
    /**
     * Get the grammar's table postfix.
     *
     * @return string
     */
    public function tablePostfix()
    {
        return $this->tablePostfix;
    }

    /**
     * Set the grammar's table postfix.
     *
     * @param  string  $postfix
     * @return $this
     */
    public function setTablePostfix($postfix)
    {
        $this->tablePostfix = $postfix;
        return $this;
    }
    
}

?>