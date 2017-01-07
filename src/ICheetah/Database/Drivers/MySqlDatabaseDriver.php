<?php

namespace ICheetah\Database\Drivers;

use \ICheetah\Tools\Arr;

class MySqlDatabaseDriver extends DatabaseDriver implements IDatabaseDriver
{
    
    /**
     * 
     * @param array $config
     * @return \PDO
     * 
     */
    public function connect(array $config = null)
    {
        $options = $this->getOptions($config);
        $pdo = $this->create($this->pdoDSN($config), $config, $options);
        
        if (isset($config['unix_socket'])) {
            $pdo->exec("use `{$config['database']}`;");
        }

        $collation = Arr::get($config, "collation", null);
        $charset = Arr::get($config, "charset", null);
        $names = "set names '$charset'". (!is_null($collation) ? " collate '$collation'" : '');
        $pdo->prepare($names)->execute();

        $timezone = Arr::get($config, "timezone", null);
        if (!is_null($timezone)) {
            $pdo->prepare('set time_zone="'.$timezone.'"')->execute();
        }

        $this->setModes($pdo, $config);

        return $pdo;
    }
        
    public function pdoDSN(array $config)
    {
        return $this->hasSocket($config) ? $this->getSocket($config) : $this->getHost($config);
    }
    
    
    /**
     * Determine if the given configuration array has a UNIX socket value.
     *
     * @param  array  $config
     * @return bool
     */
    protected function hasSocket(array $config)
    {
        return isset($config['unix_socket']) && ! empty($config['unix_socket']);
    }

    /**
     * Get the DSN string for a socket configuration.
     *
     * @param  array  $config
     * @return string
     */
    protected function getSocket(array $config)
    {
        return "mysql:unix_socket={$config['unix_socket']};dbname={$config['database']}";
    }

    /**
     * Get the DSN string for a host / port configuration.
     *
     * @param  array  $config
     * @return string
     */
    protected function getHost(array $config)
    {
        extract($config, EXTR_SKIP);
        return isset($port)
                        ? "mysql:host={$host};port={$port};dbname={$database}"
                        : "mysql:host={$host};dbname={$database}";
    }

    /**
     * Set the modes for the connection.
     *
     * @param  \PDO  $pdo
     * @param  array  $config
     * @return void
     */
    protected function setModes(\PDO &$pdo, array $config)
    {
        if (isset($config['modes'])) {
            $modes = implode(',', $config['modes']);
            $pdo->prepare("set session sql_mode='".$modes."'")->execute();
        } elseif (isset($config['strict'])) {
            if ($config['strict']) {
                $pdo->prepare("set session sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'")->execute();
            } else {
                $pdo->prepare("set session sql_mode='NO_ENGINE_SUBSTITUTION'")->execute();
            }
        }
    }

}

?>