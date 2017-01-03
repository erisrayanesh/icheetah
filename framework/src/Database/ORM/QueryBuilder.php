<?php
namespace Rafael\Database\ORM;

use Rafael\Database\Grammar\Grammar;

class QueryBuilder
{
    
    /**
     *
     * @var Grammar
     */
    private $grammar;



    /**
     *List of parameters.
     * @var ArrayList
     */
    private $parameters;

    /**
     *Set of flages.
     * @var Set
     */
    private $lstFlags;
    
    private $strLastQuery = "";


    /**
     *Generates the output SQL statement.
     * @return String 
     */
    public function get()
    {
        return "";
    }
    
    /**
     *Constructor of an SQL statement base class.
     */
    public function __construct ()        
    {
        $this->parameters = new ArrayList("Rafael\Database\ORM\Parameter");
        $this->lstFlags = new \Set(\Core::VAR_INTEGER);
    }

    /**
     * 
     * @param String $name Column name.
     * @param int $type Column data type
     * @param int $size Column size
     * @param mixed $value Column value.
     * @return SQLParameter
     */
    public function addParameter($name, $type, $size = null, $value = null)
    {
        $this->parameters->add(new SQLParameter($name, $type, $size, $value));
        return $this->parameters->last();
    }

    /**
     *Returns List of parameters.
     *@return ArrayList
     */
    public function parameters ()
    {
        return $this->parameters;
    }

    public function __toString()
    {
        return $this->generate();
    }

    public function toString()
    {
        return $this->generate();
    }
    
    
    //========= PROTECTED METHODS ===============
    
    
    /**
     *Modifies a string of SQL statement by replacing the parameter placeholders with appropriate values.
     * @param String $strCmd String to modify.
     */
    protected function replaceParameters (&$strCmd)        
    {
        
        $arrParams = array();
        foreach ($this->parameters() as $param) {
            $param instanceof SQLParameter;
            $arrParams[$param->name()] = $param->get();
        }
        //print_r($arrParams);
        //$strCmd = str_replace(array_keys($arrParams), array_values($arrParams), $strCmd);
        foreach ($arrParams as $key => $value) {
            $newCmd = preg_replace("/{$key}\b/", $value, $strCmd);
            if ($newCmd != null && $newCmd != false)
                $strCmd = $newCmd;
        }
        
        return $strCmd != false ;
    }
    
    
    //========= PROPERTIES ===============
    
    /**
     *Returns set of flags.
     *@return Set 
     */
    public function flags ()   
    {
        return $this->lstFlags;
    }
    
    public function lastQuery()
    {
        return $this->strLastQuery;
    }

    protected function setLastQuery($strLastQuery)
    {
        $this->strLastQuery = $strLastQuery;
        return $this;
    }
    
    
    
    
}
?>