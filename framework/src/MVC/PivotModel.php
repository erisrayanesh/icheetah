<?php

namespace ICheetah\MVC;


class PivotModel implements \ArrayAccess, \Iterator
{
    protected $pivotDataList;
    protected $relationKey = "";
    protected $relatedModel;
    protected $foreignKey;
    protected $foreignKeyValue;
    
    protected $table;
    private $data = array();
    private $join;
    private $where;
    private $order;
    private $group;
    private $columns = "*";
    private $limit = 0;
    private $offset = 0;
    private $maxPageCount = 1;
    private $pagination = false;
    private $list = array();
    private $lastQuery;
    
    public function __construct($table, $relatedModel)
    {
        $this->setTable($table);
        $this->setRelatedModel($relatedModel);
    }
    
    
    //1,2,3
    //[1=>[expire = true]]
    public function add($ids, array $additional = array())
    {
        $retVal = false;
        try {
            $ids = is_array($ids) ? $ids : func_get_args();
            
            if (count($ids) == 0){
                return;
            }
            
            foreach ($ids as $key => $value) {
                
                $data = array();
                $data[$this->getForeignKey()] = $this->getForeignKeyValue();
                
                if (is_array($value)) {
                    $data[$this->getRelationKey()] = $key;                    
                    $data = array_merge($data, $value, $additional);
                } else {
                    $data[$this->getRelationKey()] = $value;                    
                }
                
                $strSql = "INSERT INTO `{$this->table}` (`" . implode("`, `", array_keys($data)) . "`) VALUES (%s)";
                
                $values = array();
                foreach (array_values($data) as $data_value) {
                    $values[] = Model::quoted($data_value);                    
                }
                $strSql = sprintf($strSql, implode(", ", $values));
                //Debug::out($strSql . "\n", TRUE);
                $result = Database::getInstance()->doMySQLiNonQuery($strSql);
            }
            
//            if ($result !== false) {
//                $retVal = true;
//            } else {
//                throw new \Exception("Not created");
//            }
        } catch (\Exception $ex) {
            $retVal = false;
        }
    }
    
    public function remove(array $ids = array())
    {
        $retVal = false;
        try {
            $ids = is_array($ids) ? array_values($ids) : func_get_args();
            
            $strSql = "DELETE FROM `{$this->table}` WHERE `{$this->getForeignKey()}` = {$this->getForeignKeyValue()}" ;
            if (count($ids) > 0)
            {
                $strSql .= " AND " . $this->whereIn($this->getRelationKey(), $ids);
            }
            
            $result = Database::getInstance()->doMySQLiNonQuery($strSql);
            if ($result !== false) {
                $retVal = true;
            } else {
                throw new \Exception("Not deleted");
            }
        } catch (\Exception $ex) {
            $retVal = false;
        }
    }
    
    public function removeAll()
    {
        return $this->remove();
    }

    public function get()
    {
        try {
            
            if(is_string($this->getColumns())){
                $colums = trim($this->getColumns());
            }elseif (is_array($this->getColumns())) {
                $colums = implode(", ", array_values($this->getColumns()));
            }  else {
                $colums = "*";
            }
            
            $strSql = "SELECT $colums FROM `{$this->table}`";
            
            $strSql .= " WHERE (`{$this->getForeignKey()}` = {$this->getForeignKeyValue()})";
            
            if ($this->getWhere() != ""){
                $strSql .= " AND " . $this->getWhere();
            }
            
            if ($this->getOrder() != ""){
                $strSql .= " ORDER BY " . $this->getOrder();
            }
            
            if ($this->getGroup() != ""){
                $strSql .= " GROUP BY " . $this->getGroup();
            }
            
            $this->setLastQuery($strSql);
            if($this->getPagination()){
                $result = Database::getInstance()->doMySQLiQuery($strSql);
                if ($result){
                    $this->setMaxPageCount($this->num_rows);
                } else {
                    throw new \Exception("No result");
                }
            }
            
            if ($this->limit > 0){
                $strSql .= " LIMIT " . $this->limit;
            }
            
            if ($this->offset > 0){
                $strSql .= " OFFSET " . $this->offset;
            }
            $this->setLastQuery($strSql);
            $result = Database::getInstance()->doMySQLiQuery($strSql);
            if ($result){
                $this->list = array();
                while ($row = $result->fetch_assoc()) {
                    $name = $this->getRelatedModel();
                    //$m = new $name();
                    $m = $name::find($row[$this->getRelationKey()]);
                    //if ($m->find($row[$this->getRelationKey()])){
                    if ($m !== null){
                        $m->pivot = (object) $row;
                        $this->list[] = $m;
                    }
                }                
            } else {
                throw new \Exception("No result");
            }
        } catch (\Exception $exc) {
            //Debug::out($exc->getMessage() . "\n", true);
        }
        return $this;
    }
    
    public function getPage($pageNum, $listCount)
    {
        $this->setPagination(true);
        $this->limit = $listCount;
        $this->offset = ($pageNum - 1) * $listCount;
        return $this->get();
    }
    
    public function getAll()
    {
        $this->setPagination(false);
        $this->limit = 0;
        $this->offset = 0;
        return $this->get();
    }
    
    public static function all()
    {
        $model = new static();
        $model->getAll();
        return $model;
    }    
    
    public function where($where)
    {
        if (is_array($where)){
            $where = implode(" AND ", $where);
        }
        
        if (strlen($this->where) > 0){
            $this->where .= " AND ";
        }
        $this->where .= $where;
        
        return $this;
    }
    
    public function orWhere($where)
    {
        if (is_array($where)){
            $where = implode(" OR ", $where);
        }
        
        if (strlen($this->where) > 0){
            $this->where .= " OR ";
        }
        
        $this->where .= $where;
        
        return $this;
    }
    
    public static function whereIn($column, array $values)
    {
        return "$column IN (" . implode(",", array_values(Convert::quoteStringValues($values))) . ")";
    }
    
    public function clearWhere()
    {
        $this->where = "";
    }

    public function orderBy($order)
    {
        $this->order = $order;
        return $this;
    }
    
    public function clearOrders()
    {
        $this->order = "";
    }

    public function groupBy($group)
    {
        $this->group = $group;
        return $this;
    }
    
    public function clearGroups()
    {
        $this->group = "";
    }

    public function columns($columns)
    {
        $this->columns = $columns;
        return $this;
    }
    
    public function count()
    {
        return count($this->list);
    }
    
    /**
     * Return first list item
     * @return static
     */
    public function first()
    {
        if ($this->count() <= 0){
            $this->get();
        }
        
        if ($this->count() > 0){
            return $this->list[0];
        } else {
            return null;
        }
    }
    
    /**
     * Return last list item
     * @return static
     */
    public function last()
    {
        if ($this->count() <= 0){
            $this->get();
        }
        
        if ($this->count() > 0){
            return $this->list[count($this->list)-1];
        } else {
            return null;
        }
    }

    public function getWhere()
    {
        return $this->where;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function getColumns()
    {
        return $this->columns;
    }
    
    public function getPagination()
    {
        return $this->pagination;
    }

    public function setPagination($pagination)
    {
        $this->pagination = (bool) $pagination;
        return $this;
    }

    public function getMaxPageCount()
    {
        return $this->maxPageCount;
    }

    protected function setMaxPageCount($maxPageCount)
    {
        $this->maxPageCount = $maxPageCount;
        return $this;
    }

    public function lastQuery()
    {
        return $this->lastQuery;
    }

    protected function setLastQuery($query)
    {
        $this->lastQuery = $query;
        return $this;
    }

    public function getTable()
    {
        return $this->table;
    }

    protected function setTable($table)
    {
        $this->table = $table;
        return $this;
    }
    
    public function getRelationKey()
    {
        return $this->relationKey;
    }

    public function setRelationKey($relationKey)
    {
        $this->relationKey = $relationKey;
        return $this;
    }

    public function getRelatedModel()
    {
        return $this->relatedModel;
    }

    public function setRelatedModel($relatedModel)
    {
        $this->relatedModel = $relatedModel;
        return $this;
    }
    
    public function getForeignKey()
    {
        return $this->foreignKey;
    }
    
    public function setForeignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;
        return $this;
    }

    public function getForeignKeyValue()
    {
        return $this->foreignKeyValue;
    }    

    public function setForeignKeyValue($foreignKeyValue)
    {
        $this->foreignKeyValue = $foreignKeyValue;
        return $this;
    }

       
    //ArrayAccess
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->list[] = $value;
        } else {
            $this->list[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->list[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->list[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->list[$offset]) ? $this->list[$offset] : null;
    }

    //Iterator
    
    public function current()
    {
        return current($this->list);
    }

    public function key()
    {
        return key($this->list);
    }

    public function next()
    {
        return next($this->list);
    }

    public function rewind()
    {
        reset($this->list);
    }

    public function valid()
    {
        $key = key($this->list);
        $var = ($key !== null && $key !== false);
        return $var;
    }
    
}

?>