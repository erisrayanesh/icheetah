<?php
namespace ICheetah\Tools;

class Log
{
    
    use \ICheetah\Traits\Singleton;
    
    /**
     *
     * @var Monolog\Logger 
     */
    protected $logger;

    protected function __construct()
    {
        $this->logger = new \Monolog\Logger('ICheetahLog');
        $this->logger->setTimezone(new \DateTimeZone(config("application.timezone", "asia/tehran")));
        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler(config("application.log"), \Monolog\Logger::DEBUG));
    }       
    
    public function alert($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }
    
    public function critical($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }
    
    public function debug($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }
    
    public function emergency($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }
    
    public function info($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }
    
    public function notice($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }
    
    public function warning($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }
        
    protected function log($level, $message, array $context = array())
    {
        if (is_array($message)){
            $message = print_r($message, true);
        }
        
        if (!is_string($message)){
            $message = var_export($message, true);
        }
        
        $this->logger->$level($message, $context);
        
        return $this;
    }
    
}