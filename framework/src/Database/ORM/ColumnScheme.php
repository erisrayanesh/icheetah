<?php
namespace Rafael\Database\ORM;

class ColumnScheme
{
    const INDEX_PRIMARY = 1;
    const INDEX_UNIQUE = 2;
    const INDEX_INDEX = 3;
    const INDEX_FULLTEXT = 4;
            
    /**
     * Column name
     * @var string
     */
    private $strName = "";

    /**
     * Column type
     * @var string
     */
    private $strType = "";
    
    /**
     * Column content length
     * @var int
     */
    private $intLength = 0;
    
    /**
     *
     * @var Set
     */
    private $lstValues = array();

    /**
     * Column content collation
     * @var string
     */
    private $strCollation = "utf8_general_ci";
    
    /**
     * Column attributes
     * @var string
     */
    private $strAttributes = "";
    
    /**
     * Column accepts null or not
     * @var boolean
     */
    private $nullable = false;

    /**
     * Column default value
     * @var string
     */
    private $strDefault = null;
    
    /**
     * Column extra attributes
     * @var string
     */
    private $strExtra = "";
    
    /**
     * Column index option
     * @var int
     */
    private $intIndex = 0;
    
    /**
     * Column comment
     * @var string
     */
    private $strComments = "";
    
    /**
     * Column auto increment status
     * @var boolean
     */
    private $autoIncrement = false;
    
    /**
     * Indicate column is of a string type
     * @var boolean
     */
    private $boolString = false;
    
    /**
     * Indicate column is of a numeric type
     * @var boolean
     */
    private $boolNumeric = false;
    
    /**
     * Indicate column is of a date/time type
     * @var boolean
     */
    private $boolDateTime = false;
        
    public function __construct($strName = "", $strType = "", array $options = array())
    {
        $this->lstValues = new Set();
        $this->setName($strName);
        $this->setType($strType);
        if (!empty($options)){
            foreach ($options as $key => $value) {
                switch ($key) {
                    case "values":
                        if (is_array($value)){
                            $value = array_values($value);
                        }                        
                        $col->values()->addRange($value);
                        break;
                    case "default":
                        $col->setDefault($value);
                        break;
                    case "collation":
                        $col->setCollation($value);
                        break;
                    case "attrs":
                    case "attributes":
                        $col->setAttributes($value);
                        break;
                    case "is_null":
                    case "is_nullable":
                    case "isnullable":
                    case "nullable":
                        $col->setNullable($value);
                        break;
                    case "index":
                        $col->setIndex($value);
                        break;
                    case "auto_increment":
                    case "autoincrement":
                    case "ai":
                        $col->setAutoIncrement($value);
                        break;
                    case "comment":
                    case "comments":
                        $col->setComments($value);
                        break;
                }
            }
        }
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
    
    public function type()
    {
        return $this->strType;
    }

    public function setType($strType)
    {
        $this->strType = $strType;
        
        $lstStringTypes = array(
            "CHAR",
            "VARCHAR",
            "TINYTEXT",
            "TEXT",
            "MEDIUMTEXT",
            "LONGTEXT",
            "BINARY",
            "VARBINARYCHAR",
            "VARCHAR",
            "TINYTEXT",
            "TEXT",
            "MEDIUMTEXT",
            "LONGTEXT",
            "BINARY",
            "VARBINARY"
        );
        
        if (in_array(strtoupper($this->type()), $lstStringTypes)) {
            $this->boolString = true;
            return $this;
        }
        
        $lstNumericTypes = array(
            "BIT",
            "TINYINT",
            "SMALLINT",
            "MEDIUMINT",
            "INT",
            "INTEGER",
            "BIGINT",
            "DECIMAL",
            "DEC",
            "NUMERIC",
            "FIXED",
            "FLOAT",
            "DOUBLE",
            "REAL",
            "FLOAT",
            "BOOL",
            "BOOLEAN"
        );
        
        if (in_array(strtoupper($this->type()), $lstNumericTypes)){
            $this->boolNumeric = true;
            return $this;
        }
        
        $lstDateTimeTypes = array(
            "DATE",
            "DATETIME",
            "TIMESTAMP",
            "TIME",
            "YEAR"
        );
        
        if (in_array(strtoupper($this->type()), $lstDateTimeTypes)){
            $this->boolDateTime = true;
            return $this;
        }
        
    }
    
    public function length() 
    {
        return $this->intLength;
    }
    
    public function setLength($intLength)
    {
        $this->intLength = $intLength;
        return $this;
    }

    public function values()
    {
        return $this->lstValues;
    }

    public function collation()
    {
        return $this->strCollation;
    }
    
    public function setCollation($strCollation)
    {
        $this->strCollation = $strCollation;
        return $this;
    }

    public function attributes()
    {
        return $this->strAttributes;
    }
    
    public function setAttributes($strAttributes)
    {
        $this->strAttributes = $strAttributes;
        return $this;
    }

    public function isNullable()
    {
        return $this->nullable;
    }
    
    public function setNullable($nullable)
    {
        $this->nullable = (bool) $nullable;
        return $this;
    }

    public function defaultValue()
    {
        return $this->strDefault;
    }
    
    public function setDefault($strDefault)
    {
        $this->strDefault = $strDefault;
        return $this;
    }

    public function extra()
    {
        return $this->strExtra;
    }

    public function setExtra($strExtra)
    {
        $this->strExtra = $strExtra;
        return $this;
    }
    
    public function index()
    {
        return $this->intIndex;
    }

    public function setIndex($intIndex)
    {
        $this->intIndex = $intIndex;
        return $this;
    }
    
    public function comments()
    {
        return $this->strComments;
    }

    public function setComments($strComments)
    {
        $this->strComments = $strComments;
        return $this;
    }
    
    public function isAutoIncrement()
    {
        return $this->autoIncrement;
    }

    public function setAutoIncrement($autoIncrement)
    {
        $this->autoIncrement = (bool) $autoIncrement;
        return $this;
    }
    
    //=========== METHODS =================
    
    public function isString()
    {
        return $this->boolString;
    }

    public function isNumeric()
    {
        return $this->boolNumeric;            
    }

    public function isDateTime()
    {
        
    }

}

?>