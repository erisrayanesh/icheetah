<?php
namespace Rafael\Database\ORM;

class ConstraintScheme
{
    /**
     * Constraint name
     * @var string
     */
    private $strName = "";
    
    /**
     * Constraint table name
     * @var string
     */
    private $strTableName = "";
    
    /**
     * Constraint column name
     * @var string
     */
    private $strColumnName = "";
    
    /**
     * Constraint referenced table name
     * @var string
     */
    private $strRefTableName = "";
    
    /**
     * Constraint referenced column name
     * @var string
     */
    private $strRefColumnName = "";
    
    
    
    public function __construct($strName)
    {
        $this->setName($strName);
    }
    
    
    public function name()
    {
        return $this->strName;
    }
    
    public function setName($strName)
    {
        $this->strName = $strName;
        return $this;
    }

    public function tableName()
    {
        return $this->strTableName;
    }

    public function setTableName($strTableName)
    {
        $this->strTableName = $strTableName;
        return $this;
    }
    
    public function columnName()
    {
        return $this->strColumnName;
    }
    
    public function setColumnName($strColumnName)
    {
        $this->strColumnName = $strColumnName;
        return $this;
    }

    public function refTableName()
    {
        return $this->strRefTableName;
    }
    
    public function setRefTableName($strRefTableName)
    {
        $this->strRefTableName = $strRefTableName;
        return $this;
    }

    public function refColumnName()
    {
        return $this->strRefColumnName;
    }

    public function setRefColumnName($strRefColumnName)
    {
        $this->strRefColumnName = $strRefColumnName;
        return $this;
    }
    
}

?>