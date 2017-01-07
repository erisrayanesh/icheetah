<?php

namespace ICheetah\Database\Query\Grammar;

use ICheetah\Database\Expression;
use ICheetah\Database\Query\QueryBuilder;
use \ICheetah\Database\Wrapper;

class Grammar extends Wrapper
{
    
    protected $parts = array(
            'aggregate',
            'columns',
            'from',
            'joins',
            'wheres',
            'groups',
            'havings',
            'orders',
            'limit',
            'offset',
            'unions',
            'lock',
        );


    public function prepareSelect(QueryBuilder $builder)
    {
        $parts = $this->combineParts($builder);
        return implode(' ', array_filter($parts, function ($value) { return (string) $value !== ''; }));        
    }
    
    public function prepareInsert(Builder $query, array $values)
    {
        $table = $this->wrapTable($query->from);

        if (! is_array(reset($values))) {
            $values = [$values];
        }

        $columns = $this->columnize(array_keys(reset($values)));

        // We need to build a list of parameter place-holders of values that are bound
        // to the query. Each insert should have the exact same amount of parameter
        // bindings so we will loop through the record and parameterize them all.
        $parameters = [];

        foreach ($values as $record) {
            $parameters[] = '('.$this->parameterize($record).')';
        }

        $parameters = implode(', ', $parameters);

        return "insert into $table ($columns) values $parameters";
    }
    
    public function prepareUpdate()
    {
        
    }
    
    public function prepareDelete()
    {
        
    }
        
    protected function combineParts(QueryBuilder $builder)
    {
        $query = array();
        foreach ($this->parts as $value) {
            $method = "prepare". ucfirst($value);
            if (method_exists($this, $method)){
                $query[$value] = $this->$method($builder, $builder->getPart($value));
            }
        }
        return $query;
    }
    
    
    protected function prepareColumns(QueryBuilder $builder, $part = null)
    {
        if (empty($part)){
            $builder->select(["*"]);
        }
        
        $select = $builder->getPart("distinct") ? 'SELECT DISTINCT ' : "SELECT ";
        return $select.$this->unifyColumns($part);        
    }
    
    protected function prepareFrom(QueryBuilder $builder, $part = null)
    {
        return "FROM " . implode(", ", array_map([$this, "wrapKeyword"], $part));
    }
    
    protected function prepareJoins(QueryBuilder $builder, $part = null)
    {
        return "";
    }
    
    protected function prepareWheres(QueryBuilder $builder, $part = null)
    {
        $where = array();
        
        if (is_null($part)) {
            return "";
        }        
        foreach ($part as $item) {
            $method = "prepareWhere" . $item['type'];
            $where[] = $item['boolean'].' '.$this->$method($builder, $item);
        }
        
        if (count($where) > 0) {
            return "WHERE " . $this->removeFirstBoolean(implode(" ", $where));
        }

        return "";
    }
    
    protected function prepareWhereNormal(QueryBuilder $builder, $part = null)
    {
        $value = $this->parameter($part['value']);
        return $this->wrapKeyword($part["column"]) . " " .$part['condition'] . " " . $value;
    }
    
    protected function prepareWhereGroup(QueryBuilder $builder, $part = null)
    {
        return "(" . substr($this->prepareWheres($builder, $part["value"]), 6) . ")";
    }
    
    
    
    protected function removeFirstBoolean($value)
    {
        return preg_replace('/and |or /i', '', $value, 1);
    }
    
}

?>