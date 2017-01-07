<?php

namespace ICheetah\Database\Drivers;

use PDO;
use \ICheetah\Tools\Arr;

abstract class DatabaseDriver 
{
    
    /**
     * The default PDO connection options.
     *
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    public function create($dsn, array $config, array $options = null)
    {
        $username = Arr::get($config, 'username', null);
        $password = Arr::get($config, 'password', null);
        return new PDO($dsn, $username, $password, $options);
    }
    
    public function getOptions(array $config)
    {
        $options = Arr::get($config, 'options', []);
        return array_diff_key($this->options, $options) + $options;
    }

}

?>