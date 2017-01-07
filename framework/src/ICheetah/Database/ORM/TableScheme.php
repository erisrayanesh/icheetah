<?php
namespace Rafael\Database\ORM;

class TableScheme
{
    
    /**
     * Table name
     * @var string
     */
    private $strName = "";
        
    /**
     * Table content collation
     * @var string
     */
    private $strCollation = "utf8_general_ci";
    
    /**
     * List of table constratints
     * @var ArrayList
     */
    private $lstConstraints;
    
    
    /**
     *
     * @var Map
     */
    private $lstColumns;

    public function __construct()
    {
        $this->lstColumns = new Map("ColumnScheme");
        $this->lstConstraints = new ArrayList("ConstraintScheme");
    }
    
    /**
     * Adds a new column scheme
     * @param string $strName Column name
     * @param string $strType Column type
     * @param array $options Other column attributes
     * @return ColumnScheme
     */
    public function addColumn($strName, $strType, array $options = array())
    {
        $this->columns()->setItem($strName, new ColumnScheme($strName, $strType, $options));
        return $this->columns()->last();
    }
    
    /**
     * 
     * @return Map
     */
    public function columns()
    {
        return $this->lstColumns;
    }
    
    /**
     * Returns a column scheme
     * @param string $strName Column name
     * @return ColumnScheme
     */
    public function column($strName)
    {
        return $this->columns()->item($strName);
    }

    public function columnsName()
    {
        return $this->columns()->keys();
    }
    
}

?>