<?php

namespace ICheetah\Database\Connections;

use \ICheetah\Database\Query\Grammar\MySqlGrammar;

class MySqlConnection extends Connection implements IConnection
{

    public function __construct(array $configuration = null)
    {
        parent::__construct($configuration);
    }
    
    protected function setupQueryGrammar()
    {
        $this->setQueryGrammar(new MySqlGrammar());
    }
    
    
}

?>