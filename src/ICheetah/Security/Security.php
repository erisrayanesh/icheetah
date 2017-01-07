<?php

namespace ICheetah\Security;

use ICheetah\Http\Inputs;
use ICheetah\UI\HtmlElement;

class Security extends \ICheetah\Foundation\Singleton
{
    
    const CAPTCHA = "captcha";
    protected $token = "";

    protected static $instance = null;
    
    protected function __construct()
    {
        parent::__construct();
        $this->token = md5(time(). uniqid(mt_rand()));
    }
    
    
    /**
     * 
     * @return Security
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    public function tokenField()
    {
        $input = new HtmlElement("input");
        $input->setName("token")->value = $this->token;
        $input->type = "hidden";
        return $input->element();
    }
    
    public static function getRandCode($intCount = 32)
    {
        return substr(md5(time() . uniqid(mt_rand())), 0, $intCount);
    }

    public static function saveCaptchaCode($strCode)
    {
        Inputs::set(self::CAPTCHA, $strCode, $_SESSION);
    }
    
    public static function getCaptchaCode()
    {
        return Inputs::get(self::CAPTCHA, null, $_SESSION);
    }

    public static function isCaptchaValid($strCode)
    {
        $sessCaptcha = self::getCaptchaCode();
        return $sessCaptcha === $strCode;
    }
    
}

?>