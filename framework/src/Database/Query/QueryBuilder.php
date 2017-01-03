<?php

namespace ICheetah\Database\Query;

use \ICheetah\Database\Connections\IConnection;
use ICheetah\Database\Query\Grammar\Grammar;
use ICheetah\Tools\Arr;
use \ICheetah\Tools\Collection;

class QueryBuilder
{
    
    /**
     *
     * @var Connections\Connection 
     */
    protected $connection;
    
//    protected $distinct = false;
//    protected $columns = array("*");
//    protected $from = array();
//    protected $joins = array();
//    protected $wheres = array();
//    protected $groups = array();
//    protected $havings = array();
//    protected $orders = array();
//    protected $limit;
//    protected $offset;
    
    protected $parts = [
        'distinct' => false,
        'columns' => ["*"],
        'from' => [],
        'joins'   => [],
        'wheres'  => [],
        'groups'  => [],
        'havings' => [],
        'orders'  => [],
        'limit'  => null,
        'offset'  => null,
    ];
    
    protected $bindings = [
        'select' => [],
        'join'   => [],
        'where'  => [],
        'having' => [],
        'order'  => [],
        'union'  => [],
    ];


    public function __construct(IConnection $connection)
    {
        $this->connection = $connection;
    }

    public function select($columns = ['*'])
    {
        if ($columns instanceof \Closure) {
            $columns = call_user_func($columns, $this);
        }
        
        $this->addToPart("columns", $columns);
        return $this;
    }
    
    public function selectRaw($raw, $bindings = [], $alias = null)
    {
        if (!is_null($alias)){
            $raw .= " as " . $this->grammar()->wrap($alias);
        }
        
        $this->addBinding($bindings, "select");
        
        $this->addToPart("columns", $raw);
    }
    
    public function from($table, $alias = null)
    {
        if (is_array($table)){
            $this->parts["from"] = array_merge($this->parts["from"], array_values($table));
        } else {
            $this->addToPart("from", $table);
        }
        return $this;
    }
    
    public function join($table, $one, $operator = null, $two = null, $type = 'inner', $where = false)
    {
        
    }
    
    public function where($column, $condition = null, $value = null, $boolean = 'and')
    {
        if (is_array($column)){
            return $this->whereArray($column, $boolean);
        }
        
        if ($column instanceof \Closure){
            return call_user_method("whereGroup", $this, $column);
        }
        
        //Two params with no condition
        //where("name", "ali")
        if (func_num_args() == 2) {
            list($condition, $value) = array('=', $condition);
        }
        
        //Value is still null like orWhere method
        if (is_null($value)){
            list($condition, $value) = array("=", $condition);
        }
            
        if (is_null($value)) {
            //die(func_num_args()." column= $column cond= $condition value= $value bool= $boolean");
            throw new \InvalidArgumentException('Null value passed to method');
        }
        
        $type = "Normal";
        $where = compact("type", "column", "condition", "value", "boolean");
        $this->addToPart("wheres", $where);
        
        if (!$value instanceof Expression) {
            $this->addBinding($value);
        }
        
        return $this;
    }
    
    public function whereArray(array $array, $boolean = "and")
    {
        foreach ($array as $key => $value) {
            if (is_numeric($key) && is_array($value)) {
                call_user_method("where", $this, $value);
            } else {
                $this->where($key, "=", $value);
            }
        }
    }
    
    public function whereGroup(\Closure $callback, $boolean = "and")
    {
        $query = $this->newQuery($this->getPart("from"));
        call_user_func($callback, $query);
        if (count($query->getPart("wheres")) > 0) {
            $type = 'Group';
            $value = $query->getPart("wheres");
            $this->addToPart("wheres", compact('type', 'value', 'boolean'));
            $this->addBinding($query->bindings(), 'where');
        }
        return $this;        
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, "or");        
    }    
    
    public function whereRaw($expr, array $bindings = [], $boolean = "and")
    {
        $type = "Raw";
        $where  = compact("type", "boolean", "expr");        
        $this->addToPart("wheres", $where);
        $this->addBinding($bindings);
        return $this;
    }
        
    public function orWhereRaw($expr, array $bindings = [])
    {
        return $this->whereRaw($expr, $bindings, "or");
    }
    
    public function whereIn($column, $values, $boolean = 'and', $negative = false)
    {
        $type = "In";

//        if ($values instanceof static) {            
//            return $this->whereInSub($column, function ($query) use ($values) {
//                return $values;
//            }, $boolean, $negative);
//            
//        }

        if ($values instanceof Closure) {
            return $this->whereInSub($column, $values, $boolean, $negative);
        }

        if ($values instanceof Collection) {
            $values = $values->toArray();
        }

        $this->addToPart("wheres", compact('type', 'column', 'values', 'boolean', 'negative'));

        $this->addBinding($values, 'where');

        return $this;
    }
    
    public function orWhereIn($column, $values)
    {
        return $this->whereIn($column, $values, "or");
    }
    
    public function whereNotIn($column, $values, $boolean = 'and')
    {
        return $this->whereIn($column, $values, $boolean, true);
    }
    
    public function orWhereNotIn($column, $values)
    {
        return $this->whereNotIn($column, $values, "or");
    }
    
    public function whereBetween($column, array $values, $boolean = 'and', $negative = false)
    {
        $type = "Between";
        $this->wheres[] = compact('column', 'type', 'boolean', "negative");
        $this->addBinding($values);
        return $this;
    }

    public function orWhereBetween($column, array $values)
    {
        return $this->whereBetween($column, $values, 'or');
    }

    public function whereNotBetween($column, array $values, $boolean = 'and')
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    public function orWhereNotBetween($column, array $values)
    {
        return $this->whereNotBetween($column, $values, 'or');
    }
    
    public function whereNull($column, $boolean = 'and', $negative = false)
    {
        $type = "Null";
        $this->wheres[] = compact('type', 'column', 'boolean', "negative");
        return $this;
    }

    public function orWhereNull($column)
    {
        return $this->whereNull($column, 'or');
    }

    public function whereNotNull($column, $boolean = 'and')
    {
        return $this->whereNull($column, $boolean, true);
    }

    public function orWhereNotNull($column)
    {
        return $this->whereNotNull($column, 'or');
    }
    
    public function whereExists(Closure $callback, $boolean = 'and', $not = false)
    {
        $query = $this->newQuery();
        call_user_func($callback, $query);
        return $this->addWhereExistsQuery($query, $boolean, $not);
    }

    public function orWhereExists(Closure $callback, $not = false)
    {
        return $this->whereExists($callback, 'or', $not);
    }

    public function whereNotExists(Closure $callback, $boolean = 'and')
    {
        return $this->whereExists($callback, $boolean, true);
    }

    public function orWhereNotExists(Closure $callback)
    {
        return $this->orWhereExists($callback, true);
    }
    
    public function whereInSub($column, Closure $callback, $boolean, $negative)
    {
        $type = $negative ? 'NotInSub' : 'InSub';

        $query = $this->connection()->query();
        call_user_func($callback, $query);

        $this->wheres[] = compact('type', 'column', 'query', 'boolean');

        $this->addBinding($query->getBindings(), 'where');

        return $this;
    }
    
//    inSub
//    orInSub
//    notInSub
//    orNotInSub
//    InRaw
//    orInRaw
//    notInRaw
//    orNotInRaw
//    exist
//    orExist
//    notExist
//    orNotExist
    

    public function get()
    {
        return $this->connection()->select($this->sqlQuery(), $this->bindings());
    }
    
    public function sqlQuery()
    {
        return $this->grammar()->prepareSelect($this, $this->bindings());
    }
    
    public function insert($values)
    {
        
        if (!is_array(reset($values))) {
            $values = [$values];
        }

        $bindings = [];

        foreach ($values as $row) {
            foreach ($row as $value) {
                $bindings[] = $value;
            }
        }

        //Compile step
        $sql = $this->grammar()->compileInsert($this, $values);

        return $this->connection->insert($sql, $bindings);
        
    }
    
    public function update($values)
    {
        
    }
    
    public function delete($id = null)
    {
        
    }    
    
    public function bindings($raw = false)
    {
        return $raw? $this->bindings : Arr::flatten($this->bindings);
    }    

    public function setBinding(array $binding, $part = "where")
    {
        if (Arr::keyExist($this->bindings, $part)){
            Arr::set($this->bindings, $part, $binding);
        }
        return $this;
    }
    
    public function addBinding($value, $part = "where")
    {
        if (!Arr::keyExist($this->bindings, $part)){
            throw new InvalidArgumentException("Invalid binding part: {$part}.");
        }
        
        if (is_array($value)) {
            Arr::set($this->bindings, $part, array_values(array_merge($this->bindings[$part], $value)));
        } else {
            Arr::add($this->bindings[$part], $value);
        }

        return $this;
    }
    
    /**
     * 
     * @return IConnection
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * 
     * @return Grammar
     */
    public function grammar()
    {
        return $this->connection()->getQueryGrammar();
    }
    
    public function newQuery($table = null)
    {
        $instance = new static($this->connection());
        if (!is_null($table)){
            $instance->from($table);
        }
        return $instance;
    }

    public function distinct()
    {
        $this->setRawPart("distinct", true);
        return $this;
    }
    
    public function distinctOff()
    {
        $this->setRawPart("distinct", false);
        return $this;
    }
    
    public function getPart($name, $default = null)
    {
        return Arr::get($this->parts, $name, $default);
    }
    
    protected function addToPart($name, $value)
    {
        if (Arr::keyExist($this->parts, $name)){
            return Arr::add($this->parts[$name], $value);
        }
    }
    
    protected function setRawPart($name, $value)
    {
        Arr::set($this->parts, $name, $value);
    }
    
}

?>