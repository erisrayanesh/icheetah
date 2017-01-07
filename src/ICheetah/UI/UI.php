<?php

namespace ICheetah\UI;

class UI
{
    public static function media($id = "", $name = "", $value = "", $attributes = null)
    {
        $dialogAttrs = isset($attributes["dialog_attributes"])? $attributes["dialog_attributes"] : null;
        $inputAttrs = isset($attributes["input_attributes"])? $attributes["input_attributes"] : null;
        $retVal = array();
        $retVal[] = BootstrapUI::modalDialog("", "", Html::iframe("", APP_URI . "admin/media"), "", null, $dialogAttrs);
        $retVal [] = Html::textbox("", "", Html::iframe("", APP_URI . "admin/media"), "", null, $inputAttrs);
        return $retVal;
    }
    
    public static function inputGroup($id = "", $name = "", $value = "", $attributes = null)
    {
        
    }
}

?>