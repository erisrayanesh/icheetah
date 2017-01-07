<?php

namespace ICheetah\Http;

use ICheetah\Application\Application;
use ICheetah\Security\Users;

class Session extends \ICheetah\Foundation\Singleton
{
    
    protected static $instance = null;
    
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

    public function initSession()
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

    
        
    //====== SESSION HANDLER ==========
    //
    //====== ISessionHandler =======
    
    public function isSessionExists($session_id)
    {
        return \Models\Sessions::find($session_id) != null;
    }

    public function shutdownSession()
    {
        @session_write_close();
    }

    //====== SessionHandlerInterface =======
    
    public function closeSession()
    {
        // do nothing
        return true;
    }

    public function destroySession($session_id)
    {   
        $model = \Models\Sessions::find($session_id);
        if ($model != null) {
            $model->delete();
            return true;
        } else {
            return false;
        }
    }

    public function gcSession($maxlifetime)
    {
        $old = time() - $maxlifetime;
        $model = new \Models\Sessions();
        $model->where(sprintf("access < %d", $old))->getAll();
        foreach ($model as $value) {
            $value->delete();
        }
        return true;
    }

    public function openSession($save_path, $session_id)
    {
        // do nothing
        return true;
    }

    public function readSession($session_id)
    {
        $model = \Models\Sessions::find($session_id);
        if ($model != null){
            return $model->data;
        } else {
            return "";
        }
    }

    public function writeSession($session_id, $session_data)
    {
        if ($this->isSessionExists($session_id)){
            //Update record          
            $model = \Models\Sessions::find($session_id);
            if ($model != null){
                $model->app = Application::getAppName();
                $model->user_id = Users::getInstance()->activeUserID();
                $model->access = time();
                $model->data = $session_data;
                $model->save();
                //Debug::out("DB updated \n", true);
            }            
        } else {
            //Create record
            $model = new \Models\Sessions();
            $model->session_id = $session_id;
            $model->user_id = Users::getInstance()->activeUserID();
            $model->app = Application::getAppName();
            $model->access = time();
            $model->data = $session_data;
            $model->create();            
            //Debug::out("DB inserted\n", true);
        }       
        return true;
    }
    
    public function __destruct()
    {
        $this->shutdownSession();
    }
}

?>