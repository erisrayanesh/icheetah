<?php

namespace ICheetah\Http\Session;

class Session
{
    
    use \ICheetah\Traits\Singleton;
    
    protected $maxLifeTime = 10;

    protected $sessionName;

    /**
     * 
     * @return Session
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }
    
    public function sessionStart()
    {
        if (session_status() == PHP_SESSION_NONE) {
            //Start session
            session_start();
            //Debug::out("Started\n", true);
        }
    }
    
    public function sessionPause()
    {
        
    }

    public function sessionRestart()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
            self::sessionStart();
        }
    }
    
    private function createSession($name)
    {
        //Debug::out("Create new\n", true);
        //Stop any available session.
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
            
            
        }
        
        //Expire cookie
        $sessionCookie = Request::cookie($name, 0);
        if (strlen($sessionCookie)) {
            Request::setCookie($name, "", time()-(3600*24)*2, "/");
        }

        //Set new session ID.
        $strSessionID = (md5(time(). uniqid(mt_rand())));
        session_id($strSessionID);

        //New cookie values
        Request::setCookie($name, $strSessionID, 0, "/");

        $this->sessionStart();
        
        //Set start and last access time to current;
        $appendix = Request::session("sys_appendix", array());
        $appendix["START_TIME"] = time();
        $appendix["LAST_ACCESS_TIME"] = time();
        Request::set("sys_appendix", $appendix, $_SESSION);
        
    }

    public function init()
    {
                                
        session_set_save_handler(array($this, 'openSession'),
                                 array($this, 'closeSession'),
                                 array($this, 'readSession'),
                                 array($this, 'writeSession'),
                                 array($this, 'destroySession'),
                                 array($this, 'gcSession')
                                );
        session_register_shutdown($this->shutdownSession());


        ini_set('session.gc_maxlifetime', $this->getMaxLifeTime() * 60);

        //Don't allow java script to use session cookie.
        ini_set('session.cookie_httponly', true);

        //set default sessios save handler
        //ini_set('session.save_handler', 'files');

        //disabling transparent session id support
        ini_set('session.use_trans_sid', '0');
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);
        session_cache_limiter('none');

        if (!empty($this->getSessionName())){
            session_name($this->getSessionName());
        }
                
        //Stop any auto started session
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        //if found and loaded successfully
        $strSessID = Request::cookie($this->getSessionName(), null);
        if (!is_null($strSessID)){
            session_id($strSessID);
            //Debug::out("Exist\n", true);
            //Start session
            $this->sessionStart();
            
            //check if session expired
            $expired = true;
            $timeOut = $this->getMaxLifeTime() * 60;
            $appendix = Request::session("sys_appendix", null);
            if (is_array($appendix)){
                $startTime = isset($appendix["START_TIME"])? $appendix["START_TIME"] : 0;
                if ($startTime > 0){
                    //echo $start_time . " , " . $last_activity;
                    $lastAccess = isset($appendix["LAST_ACCESS_TIME"])? $appendix["LAST_ACCESS_TIME"] : 0;
                    $times_diff = time() - $lastAccess;
                    if ($times_diff >= $timeOut) {
                        $expired = true;
                    } else {
                        $expired = false;
                    }
                } else {
                    $expired = true;
                }
            } else {
                $expired = true;
            }

            if ($expired){
                //Debug::out("Expired\n", true);
                //Session expired
                $this->createSession($this->getSessionName());
            } else {
                //Debug::out("Continue session\n", true);
                //echo "continue ";
                //Session not expired
                $appendix = Request::session("sys_appendix", array());
                $appendix["LAST_ACCESS_TIME"] = time();
                Request::set("sys_appendix", $appendix, $_SESSION);
            }
        } else {
            //echo "start new ";
            //Debug::out("Not exist\n", true);
            //New session
            $this->createSession($this->getSessionName());
        }
        
    }
    
    public function getMaxLifeTime()
    {
        return $this->maxLifeTime;
    }

    public function setMaxLifeTime($lifeTime)
    {
        $this->maxLifeTime = $lifeTime;
        return $this;
    }
    
    public function getSessionName()
    {
        return $this->sessionName;
    }

    public function setSessionName($sessionName)
    {
        $this->sessionName = $sessionName;
        return $this;
    }

     
}

?>