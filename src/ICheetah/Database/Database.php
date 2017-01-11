<?php

namespace ICheetah\Database;

use \PDO;
use \ICheetah\Tools\Collection;

class Database
{

    use \ICheetah\Traits\Singleton;
    
    
    /**
     *
     * @var Collection
     */
    private $lstConnections;
    
    /**
     * Active connection key
     * @var mixed
     */
    private $strActiveConnection;
    
    /**
     * 
     * @return Database
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }
    
    public static function __callStatic($name, $arguments)
    {
        $connection = self::getInstance()->activeConnection();
        return call_user_method_array($name, $connection, $arguments);
    }
        
    /**
     * Database Constructor
     */
    protected function __construct()
    {
        $this->lstConnections = new Collection();
        
        $default = "database.connections.".config("default", "mysql");
        
        if (config($default) == null){
            //No default connection information available
            return;
        }
        
        $dbConfig = array (
            "driver"    => config::DATABASE_DRIVER,
            "host"    => \Config::DATABASE_SERVER,
            "database"  => \Config::DATABASE_NAME,
            "username"  => \Config::DATABASE_USER,
            "password"  => \Config::DATABASE_PASSWORD,
            "charset"   => \Config::DATABASE_CHARSET,
            "collation" => \Config::DATABASE_COLLATION
        );
        
        $connection = null;
        
        switch (\Config::DATABASE_DRIVER) {
            case "mysql":
                $connection = new Connections\MySqlConnection($dbConfig);
                break;
        }
        
        $connection->open();
        
        Database::getInstance()->addConnection("main", $connection, true);
        
    }

    /**
     * Adds new connection.
     * @param type $key Connection key name.
     * @param type $objConnection Connection object.
     * @param type $boolActive If true, Connection object will be default
     * @return boolean
     */
    public function addConnection($key, Connections\IConnection $objConnection, $boolActive = false)
    {
        $this->lstConnections->set($key, $objConnection);
        
        if ($boolActive){
            $this->setActiveConnection($key);
        }
        return $objConnection;
    }

    /**
     * Returns an connection
     * @return mysqli
     */
    public function connection($key)
    {
        return $this->lstConnections->get($key, null);
    }

    /**
     * Change which database connection is actively used for the next operation
     * @param int the new connection id
     * @return void
     */
    public function setActiveConnection($strKey)
    {
        $this->strActiveConnection = $strKey;
    }
    
    /**
     * Returns active connection key name.
     * @return string
     */
    public function activeConnectionKey()
    {
        return $this->strActiveConnection;
    }
    
    /**
     * Returns active connection object.
     * @return \PDO
     */
    public function activeConnection()
    {
        return $this->connection($this->activeConnectionKey());
    }
            
    public function backupDatabase($strDBName, $strUserName, $strPassword = null, $strHostName = "localhost")
    {
        
        ob_start();

        // if mysqldump is on the system path you do not need to specify the full path
        // simply use "mysqldump --add-drop-table ..." in this case
        //$command = "C:\\xampp\\mysql\\bin\\mysqldump --add-drop-table --host=$hostname --user=$username ";
        $command = "mysqldump --add-drop-table --host=$strHostName --user=$strUserName ";
        if ($strPassword != null){
            $command.= "--password=" . $strPassword . " ";
        }
        $command.= $strDBName;
        @system($command);

        $dump = ob_get_contents();
        ob_end_clean();
        
        return $dump;
    }
    
    /**
     * Deconstruct the object
     * close all of the database connections
     */
    public function __deconstruct() 
    {
//        foreach ($this->lstConnections as $connection) {
//            $connection->close();
//        }
    }
}
?>