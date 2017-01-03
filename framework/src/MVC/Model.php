<?php

namespace ICheetah\MVC;

use ICheetah\Database\Database;
use ICheetah\Tools\Convert;

class Model implements \ArrayAccess, \Iterator
{
    
    CONST JOIN_INNER = "INNER";
    CONST JOIN_LEFT = "LEFT";
    CONST JOIN_LEFT_OUTER = "LEFT OUTER";
    CONST JOIN_RIGHT = "RIGHT";
    CONST JOIN_RIGHT_OUTER = "RIGHT OUTER";
    CONST JOIN_CROSS = "CROSS";
    
    
    protected $table;
    protected $slugColumn = "slug";
    protected $primaryKey = "id";
    protected $primaryKeyType = "int";
    protected $increamentable = true;
    protected $createdAt = "created_at";
    protected $modifiedAt = "modified_at";
    protected $exist = false;
    protected $autoTimestamp = false;
    protected $realationConstraints = array();

    private $lastQuery;
    private $data = array();
    private $join;
    private $where;
    private $group;
    private $order;
    private $columns = array();
    private $limit = 0;
    private $offset = 0;
    private $activePage = 1;
    private $maxRowsCount = 1;    
    private $pagination = false;
    private $list = array();

    public function __construct(array $data = array())
    {
        $this->merge($data);
    }
    
    public function merge($array)
    {
        foreach ($array as $key => $value) {
            $this->data[$key] = $value;
        }
    }
    
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            return null;
        }
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    public function create()
    {
        return $this->insert();
    }

    public function save()
    {
        $retVal = false;
        if ($this->exist()){
            $retVal = $this->update();
        } else {
            $retVal = $this->insert();
        }
        return $retVal;
    }
    
    public function delete()
    {
        $retVal = false;
        try {
            $strSql = "DELETE FROM `{$this->table}` WHERE `{$this->getPrimaryKey()}` = {$this->getPrimaryKeyValue()}";
            //\Libs\Debug::out($strSql . "\n", true);
            $result = Database::getInstance()->doMySQLiNonQuery($strSql);
            if ($result !== false) {
                $retVal = true;
            } else {
                throw new \Exception("Not delete");
            }
        } catch (\Exception $exc) {
            $retVal = false;
        }
        return $retVal;
    }
    
    public static function destroy($ids)
    {
        $ids = is_array($ids) ? $ids : func_get_args();
        $retVal = false;
        foreach ($ids as $value) {
            $id = Convert::toInt($value);
            $model = self::find($id);
            if ($model != null) {
                $retVal = $model->delete();
            }
        }
        return $retVal;
    }

    /**
     * 
     * @param int $id
     * @return Model
     * @throws \Exception
     */
    public static function find($id)
    {
        $instance = new static();
        return $instance->where("`{$instance->getPrimaryKey()}` = {$instance->quoted($id)}")->first();
        
//        $retVal = false;
//        try {
//            $strSql = "SELECT * FROM `{$this->table}`  WHERE `{$this->getPrimaryKey()}` = {$this->quoted($id)}";            
//            $result = Database::getInstance()->doMySQLiQuery($strSql);
//            if ($result){
//                $row = $result->fetch_assoc();
//                //die(var_export($row, true));
//                if (!empty($row) && $row != null) {
//                    $this->data = $row;
//                    $retVal = true;
//                    $this->setExist(true);
//                } else {
//                    $this->data = array();
//                }
//            } else {
//                throw new \Exception("No result");
//            }
//        } catch (\Exception $exc) {
//            $retVal = false;
//        }
//        return $retVal;
    }
    
    public static function findOrNew($id)
    {
        if (!is_null($model = self::find($id))) {
            return $model;
        }
        return new static();
    }    
    
    public static function findBySlug($slug)
    {
        $instance = new static();
        return $instance->where("`" . $instance->getSlug() . "` = '$slug'")->first();
//        $retVal = false;
//        try {
//            $slug = filter_var($slug, FILTER_SANITIZE_STRIPPED);
//            $strSql = "SELECT * FROM `{$this->table}`  WHERE `" . $this->getSlug() . "` = '$slug'";
//            
//            $result = Database::getInstance()->doMySQLiQuery($strSql);
//            
//            if ($result){
//                $row = $result->fetch_assoc();
//                //die(var_export($row, true));
//                if (!empty($row) && $row != null) {
//                    $this->data = $row;
//                    $retVal = true;
//                    $this->setExist(true);
//                } else {
//                    $this->data = array();
//                }
//            } else {
//                throw new \Exception("No result");
//            }
//        } catch (\Exception $exc) {
//            $retVal = false;
//        }
//        return $retVal;
    }

    public function get()
    {
        try {
            
            $columns = "*";
            if (count($this->getColumns()) > 0) {
                $columns = implode(", ", $this->getColumns());
            }
            
            $strSql = "SELECT $columns FROM `{$this->table}`";
            
            //Debug::out($strSql . "\n", true);
            
            
            if ($this->getJoin()) {
                $strSql .= " " . $this->getJoin();
            }
            
            if ($this->getWhere() != ""){
                $strSql .= " WHERE " . $this->getWhere();
            }
            
            if ($this->getGroup() != ""){
                $strSql .= " GROUP BY " . $this->getGroup();
            }
            
            if ($this->getOrder() != ""){
                $strSql .= " ORDER BY " . $this->getOrder();
            }
            
            $this->setLastQuery($strSql);
            if($this->getPagination()){
                $result = Database::getInstance()->doMySQLiQuery($strSql);
                if ($result){
                    $this->setMaxRowsCount($result->num_rows);
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
            //Debug::out($this->lastQuery() . "\n", true);
            $result = Database::getInstance()->doMySQLiQuery($strSql);
            if ($result){
                $this->list = array();
                while ($row = $result->fetch_assoc()) {
                    $m = new static();
                    $m->merge($row);
                    //It is ok to call private or protected methods in current scope
                    $m->setExist(true);
                    $this->list[] = $m;
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
        return $this->setPagination(true)->setActivePage($pageNum)->pick($listCount)->offset(($pageNum - 1) * $listCount)->get();
    }
    
    public function getTop($count)
    {
        return $this->setPagination(false)->pick($count)->offset(0)->get();
    }
    
    public function getAll()
    {
        return $this->setPagination(false)->pick(0)->offset(0)->get();
    }
    
    public static function all()
    {
        $model = new static();
        $model->getAll();
        return $model;
    }

    public function join($foreignTable, $foreignKey, $primaryTable, $primaryKey, $operator = "=", $joinType = self::JOIN_INNER)
    {
        $this->join .= "$joinType JOIN `$primaryTable` ON `$foreignTable`.`$foreignKey` $operator `$primaryTable`.`$primaryKey`";
    }
    
    public function clearJoin()
    {
        $this->join = "";
    }

    public function hasOne($relatedModel, $foreignKey)
    {
        $model = new $relatedModel;
        if ($model instanceof Model){
            $model->where("`$foreignKey` = {$this->getPrimaryKeyValue()}"); 
            return $model->first();
        }
        throw new Exception("Class \"$relatedModel\" not found");
    }
    
    public function hasMany($relatedModel, $foreignKey)
    {
        $model = new $relatedModel;
        if ($model instanceof Model){
            $model->where("`$foreignKey` = {$this->getPrimaryKeyValue()}")->getAll();
            //Debug::out($model->lastQuery() . "\n", true);
            return $model;
        }
        throw new Exception("Class \"$relatedModel\" not found");
    }
    
    public function belongsTo($relatedModel, $foreignKey)
    {
        $model = $relatedModel::find($this->$foreignKey);
        if ($model != null){
            return $model;
        } else {
            return new $relatedModel;            
        }
        throw new \Exception("Class \"$relatedModel\" not found");
    }
    
    public function belongsToMany($relatedModel, $pivotTable, $foreignKey, $relationKey)
    {
        $pivotModel = new PivotModel($pivotTable, $relatedModel);
        $pivotModel->setRelationKey($relationKey);
        $pivotModel->setForeignKey($foreignKey);
        $pivotModel->setForeignKeyValue($this->getPrimaryKeyValue())->get();
        return $pivotModel;
        //throw new Exception("Class \"$relatedModel\" not found");
    }
    
    public function toArray()
    {
        return $this->data;
    }
    
    public function toJSON($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
    
    public function listToArray()
    {
        $retVal = array();
        foreach ($this->list as $value) {
            $retVal[] = $value->toArray();
        }
        return $retVal;
    }
    
    public function listToJSON($options = 0)
    {
        return json_encode($this->listToArray(), $options);
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
        return "$column IN (" . implode(",", array_values(Convert::quoted($values))) . ")";
    }
    
    public static function whereBetween($column, $begin, $end)
    {
        return "$column BETWEEN " . self::quoted($begin) . " AND " . self::quoted($end);
    }
    
    public function clearWhere()
    {
        $this->where = "";
    }

    public function orderBy($column, $order = "asc")
    {
        if (strlen($this->order) > 0){
            $this->order .= ", ";
        }
        
        $this->order .= $column . " " . strtoupper($order);
        return $this;
    }
    
    public function clearOrders()
    {
        $this->order = "";
    }

    public function groupBy($group)
    {
        if (strlen($this->group) > 0){
            $this->group .= ", ";
        }
        
        $this->group .= $group;
        return $this;
    }

    public function clearGroups()
    {
        $this->group = "";
    }
    
    public function columns($columns)
    {
        if (is_array($columns)){
            //$columns = array_map(function($value) {}, $columns);
        } else if (is_string($columns)) {
            $columns = array($columns);
        }
        
        $this->columns = array_merge($this->columns, $columns);
        
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

    public function getMaxRowsCount()
    {
        return $this->maxRowsCount;
    }

    protected function setMaxRowsCount($maxRowsCount)
    {
        $this->maxRowsCount = $maxRowsCount;
        return $this;
    }
    
    public function getJoin()
    {
        return $this->join;
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
    
    public function getSlug()
    {
        return $this->slugColumn;
    }

    public function setSlug($name)
    {
        $this->slugColumn = $name;
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
    
    public function getPageListSize()
    {
        return $this->limit;
    }
    
    public function pick($count)
    {
        $this->limit = $count;
        return $this;
    }
    
    public function getOffset()
    {
        return $this->offset;
    }
   
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }
           
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    protected function setPrimaryKey($name)
    {
        $this->primaryKey = $name;
        return $this;
    }
    
    public function getPrimaryKeyValue()
    {
        $key = $this->primaryKey;
        $value = $this->$key;
        if ($this->getPrimaryKeyType() == "string"){
            $value = "'$value'";
        } elseif ($this->getPrimaryKeyType() == "int"){
            $value = Convert::toInt($value);
        }
        return $value;
    }
    
    public function getPrimaryKeyType()
    {
        return $this->primaryKeyType;
    }

    public function setPrimaryKeyType($type)
    {
        $this->primaryKeyType = $type;
        return $this;
    }

    public function getIncreamentable()
    {
        return $this->increamentable;
    }

    protected function setIncreamentable($value)
    {
        $this->increamentable = (bool) $value;
        return $this;
    }

    public function exist()
    {
        return $this->exist;
    }

    protected function setExist($exist)
    {
        $this->exist = $exist;
        return $this;
    }
    
    public function getCreatedAtColumn()
    {
        return $this->created_at;
    }

    protected function setCreatedAtColumn($name)
    {
        $this->created_at = $name;
        return $this;
    }
    
    public function getModifiedAtColumn()
    {
        return $this->modifiedAt;
    }

    protected function setModifiedAtColumn($name)
    {
        $this->modifiedAt = $name;
        return $this;
    }

    public function getActivePage()
    {
        return $this->activePage;
    }

    protected function setActivePage($activePage)
    {
        $this->activePage = $activePage;
        return $this;
    }
    
    public function getStartIndex()
    {
        return (($this->getActivePage() - 1) * $this->getPageListSize()) + 1;
    }
    
    public function has($table, $foreignKey, $condition = 'and', $where = "", $operator = '>=', $count = 1, $callback = null)
    {
        $tblAlias = $table . "_" . md5($table);
        
        $query = "(SELECT COUNT(*) FROM $table as $tblAlias WHERE $tblAlias.$foreignKey = `{$this->getTable()}`.`{$this->getPrimaryKey()}` $where) $operator $count";
        
        if ($condition == "and") {
            $this->where($query);
        } elseif ($condition == "or") {
            $this->orWhere($query);
        }        
        
        return $this;
    }

    
    //ArrayAccess
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
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
    
    
    public static function quoted($value)
    {
        if (is_string($value)) {
            //$value = filter_var($value, FILTER_SANITIZE_STRING);
            $value = "'$value'";
        } elseif (is_int($value)) {
            $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
        } elseif (is_float($value)) {
            $value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
        } elseif (is_null($value)) {
            $value = "NULL";
        }
        return $value;
    }

    //PRIVATE METHODS 
    protected function insert()
    {
        $retVal = false;
        try {
            $values = array();
            $keys = array();
            foreach ($this->data as $key => $value) {
                if (!is_array($value)){
                    $keys[] = $key;
                    $values[] = "{$this->quoted($value)}";                    
                }
            }
            $strSql = "INSERT INTO `{$this->table}` (`" . implode("`, `", $keys) . "`) VALUES (" . implode(", ", $values) . ")";
            //\Libs\Debug::out($strSql . "\n", true);
            $result = Database::getInstance()->doMySQLiNonQuery($strSql);
            if ($result !== false) {
                if ($this->getIncreamentable()){
                    $id = Database::getInstance()->activeConnection()->insert_id;
                    $keyName = $this->getPrimaryKey();
                    $this->$keyName = $id;
                }
                $retVal = true;
                $this->setExist(true);
            } else {
                throw new \Exception("Not Inserted");
            }
        } catch (\Exception $ex) {
            $retVal = false;
        }
        return $retVal;
    }
    
    protected function update()
    {
        $retVal = false;
        try {
            $strSql = "UPDATE `{$this->table}` SET ";
            $values = array();
            foreach ($this->data as $key => $value) {
                if (!is_array($value)){
                    $values[] = "`$key` = {$this->quoted($value)}";
                }                
            }
            $strSql .= implode(", ", $values) . " WHERE `{$this->getPrimaryKey()}` = {$this->getPrimaryKeyValue()}";
            $result = Database::getInstance()->doMySQLiNonQuery($strSql);
            //\Libs\Debug::out($strSql . "\n", true);
            if ($result !== false) {
                $retVal = true;
            } else {
                throw new \Exception("Not updated");
            }
        } catch (\Exception $exc) {
            $retVal = false;
        }
        return $retVal;
    }
    
}

?>