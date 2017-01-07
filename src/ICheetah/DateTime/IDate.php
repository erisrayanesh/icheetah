<?php
namespace ICheetah\DateTime;

interface IDate 
{
    public function now();
    public function yesterday();
    public function tomorow();
    public function days();
    public function firstDayOfWeekIndex();
    public function firstDayOfWeekName();
    public function dayName($index = null);
    public function dayShortName($index = null);
    public function dateInfo();
    public function months();
    public function monthName($index = null);
    public function monthShortName($index = null);
    public function year();
    
}

?>