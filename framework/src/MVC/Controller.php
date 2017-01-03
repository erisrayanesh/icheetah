<?php

namespace ICheetah\MVC;

use ICheetah\Tools\Convert;

abstract class Controller
{

    public function __construct()
    {
        
    }
    
    public static function getName()
    {
        return array_pop(explode("\\", static::class));
    }
       

}
?>