<?php

namespace ICheetah\Database\Connections;

use PDO;
use \ICheetah\Foundation\Exceptions\QueryException;
use ICheetah\Database\Query\Grammar\Grammar;
use \ICheetah\Tools\Collection;
use ICheetah\Database\Drivers;
use \ICheetah\Tools\Arr;

class Connection implements IConnection
{
    
    /**
     *
     * @var PDO
     */
    protected $pdo = null;
    
    /**
     *
     * @var array
     */
    protected $config;
    
    /**
     *
     * @var Grammar
     */
    protected $queryGrammar;
    
    /**
     * PDO Fetch style
     * @var int
     */
    protected $fetchStyle = PDO::FETCH_OBJ;


    public function __construct(array $config = null)
    {
        if (!is_null($config)){
            $this->setConfig($config);
        }
        
        $this->setupQueryGrammar();
    }        
    
    public function getPDO()
    {
        return $this->pdo;
    }

    public function setPDO($PDO)
    {
        $this->pdo = $PDO;
        return $this;
    }
    
    /**
     * 
     * @return array
     */
    public function config()
    {
        return $this->config;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
        return $this;
    }
    
    public function options()
    {
        return $this->options;
    }
    
    
    // IConnection interface
    
    public function beginTransaction()
    {
        
    }

    public function close()
    {
        $this->pdo = null;
    }

    public function commit()
    {
        
    }
    
    public function open()
    {
        switch (Arr::get($this->config(), "driver", "mysql")) {
            case "mysql":
                $driver = new Drivers\MySqlDatabaseDriver();
                break;
        }
        $pdo = $driver->connect($this->config());
        $this->setPDO($pdo);
    }

    public function delete($query, $bindings = array())
    {
        return $this->execute($query, $bindings)->rowCount();
    }

    /**
     * 
     * @param string $query
     * @param array $bindings
     * @return \PDOStatement
     */
    public function execute($query, $bindings = array())
    {
        try {
            $statement = $this->getPDO()->prepare($query);
            $statement->execute($this->prepareBindings($bindings));
            return $statement;
        } catch (\PDOException $exc) {
            throw new QueryException($query, $this->prepareBindings($bindings), $exc);
        }
    }
    
    public function insert($query, $bindings = array())
    {
        return $this->execute($query, $bindings)->rowCount();
    }
    
    public function lastInsertID()
    {
        return $this->getPDO()->lastInsertId();
    }

    public function rollBack()
    {
        
    }

    public function select($query, $bindings = array())
    {
        $statement = $this->execute($query, $bindings);
        try {
            //Fetch mode not specified
            return $statement->fetchAll($this->getFetchStyle());
        } catch (\PDOException $exc) {
            throw new QueryException($query, $this->prepareBindings($bindings), $exc);
        }
    }

    public function transactionLevel()
    {
        
    }

    public function update($query, $bindings = array())
    {
        return $this->execute($query, $bindings)->rowCount();
    }
    
    public function prepareBindings(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if (is_bool($value)) {
                $bindings[$key] = $value === true? 1 : 0;
            }
        }
        return $bindings;
    }
    
    
    public function table($table)
    {
        return $this->query()->from($table);
    }
    
    /**
     * 
     * @return \ICheetah\Database\Query\QueryBuilder
     */
    public function query()
    {
        return new \ICheetah\Database\Query\QueryBuilder($this);
    }
    
    public function getQueryGrammar()
    {
        return $this->queryGrammar;
    }

    public function setQueryGrammar(Grammar $queryGrammar)
    {
        $this->queryGrammar = $queryGrammar;
        $this->queryGrammar->setTablePrefix(Arr::get($this->config(), "prefix", null));
        $this->queryGrammar->setTablePostfix(Arr::get($this->config(), "postfix", null));
        return $this;
    }

    public function getFetchStyle()
    {
        return $this->fetchStyle;
    }

    public function setFetchStyle($fetchStyle)
    {
        $this->fetchStyle = $fetchStyle;
        return $this;
    }

        
    protected function setupQueryGrammar()
    {
        $this->setQueryGrammar(new Grammar());
    }
    
    
        
}

?>