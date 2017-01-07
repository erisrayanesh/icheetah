<?php

namespace ICheetah\Foundation;

interface IConvertable
{
    public function toString();
    public function toInt();
    public function toInteger();
    public function toFloat();
    public function toBool();
}

?>