<?php

namespace ICheetah\Tools;


class Debug
{
    public static function out($data, $append = false)
    {
        file_put_contents(APP_PATH_BASE . DS . "log.txt", $data, $append? FILE_APPEND : 0);
    }
}

?>