<?php

namespace ICheetah\Foundation\Exceptions;

class QueryException extends \PDOException
{
    
    protected $query;
    
    protected $bindings;    
    
    public function __construct($query, array $bindings, $previous)
    {
        parent::__construct('', 0, $previous);
        
        $this->query = $query;
        $this->bindings = $bindings;
        $this->previous = $previous;
        $this->code = $previous->getCode();
        $this->message = $this->prepareMessage($query, $bindings, $previous);

        if ($previous instanceof PDOException) {
            $this->errorInfo = $previous->errorInfo;
        }
    }
    
    protected function prepareMessage($query, array $bindings, $previous)
    {
        return $previous->getMessage().' (Query: '.str_replace_array('\?', $bindings, $query).')';
    }
    
    
}

?>