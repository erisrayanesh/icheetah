<?php

namespace ICheetah\Database\Connections;

interface IConnection
{
    
    public function open();
    public function close();
    public function execute($query, $bindings = []);
    public function select($query, $bindings = []);
    public function insert($query, $bindings = []);
    public function update($query, $bindings = []);
    public function delete($query, $bindings = []);
    public function lastInsertID();
    
    public function beginTransaction();
    public function commit();
    public function rollBack();
    public function transactionLevel();
    
    

}

?>