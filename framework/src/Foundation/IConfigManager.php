<?php

namespace ICheetah\Foundation;

interface IConfigManager
{
    public static function get($key, $defaults);
    public static function getInt($key, $default);
    public static function getFloat($key, $default);
    public static function add($key, $value);
    public static function remove($key);
}

?>