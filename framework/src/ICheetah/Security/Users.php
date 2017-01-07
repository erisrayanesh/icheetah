<?php

namespace ICheetah\Security;

use ICheetah\Http\Inputs;
use ICheetah\Tools\Convert;

class Users extends \ICheetah\Foundation\Singleton
{
    protected static $instance = null;
    
    /**
     * 
     * @return Users
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }
    
    public static function login($username, $password)
    {
        $retVal = false;
        try {
            $user = new \Models\Users();
            //$username = filter_var($username, FILTER_SANITIZE_MAGIC_QUOTES);
            $user->where("username = '$username'");
            if ($user->first()){
                if ($user->first()->password === md5($password)){
                    //Router::sessionRestart();
                    $retVal =  Convert::toInt($user->first()->id);
                    $_SESSION["user_id"] = $retVal;                    
                    $retVal =  $retVal > 0;
                }
            }
        } catch (\Exception $exc) {
            $retVal = false;
        }
        return $retVal;
    }
    
    public static function logout()
    {
        Inputs::set("user_id", 0, $_SESSION);
    }
    
    public static function activeUserID()
    {
        $user_id = Inputs::get("user_id", 0, $_SESSION);
        return Convert::toInt($user_id);
    }
    
    public static function whoIsActive()
    {
        $retVal = null;
        $user = self::getActiveUser();
        if ($user){
            $retVal = $user->name;
        }
        return $retVal;
    }
    
    /**
     * 
     * @return \Models\Users | null
     */
    public static function getActiveUser()
    {
        return self::getUser(self::activeUserID());
    }
    
    
    /**
     * 
     * @return \Models\Users | null
     */
    public static function getUser($id)
    {
        $retVal = new \Models\Users();
        if ($id >= 0){
            $user = \Models\Users::find($id);
            if ($user != null){
                $retVal = $user;
            }
        }
        return $retVal;
    }
    
}

?>