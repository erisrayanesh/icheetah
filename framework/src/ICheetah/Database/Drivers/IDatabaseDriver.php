<?php

namespace ICheetah\Database\Drivers;

interface IDatabaseDriver
{
    /**
     * Build a database connection.
     *
     * @param  array  $configuration
     * @return \PDO
     */
    public function connect(array $configuration = null);
        
}

?>