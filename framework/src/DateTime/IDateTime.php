<?php
namespace ICheetah\DateTime;

interface IDateTime extends IDate, ITime
{
    public function now();
    public function makeTimestamp($hour, $minute, $second, $year, $month, $day);
    public function datetime();
    
    public function add($interval);
    public function sub($interval);
    public function diff(AbstractDateTime $date);
    
    public static function create($timestamp = null, $format = null, $timezone = null);
    public static function getNow($timestamp = null, $format = null, $timezone = null);
}

?>