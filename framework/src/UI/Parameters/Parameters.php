<?php

namespace ICheetah\UI\Parameters;

use \ICheetah\Tools as Tools;

class Parameters
{
    
    /**
     * Prepares settings form fields
     * @param string $category settings file name
     * @param string $options if true, individual data options will loaded
     * @return array
     */
    public static function getFields($file)
    {
        $fields = array();
        $doc = Tools\XML::openDOMDoc($file);
        if ($doc !== false){
            foreach ($doc->documentElement->childNodes as $item){
                if ($item instanceof \DOMElement){
                    $field = array();
                    $field = array("name" => $item->getAttribute("name"), 
                                    "type" => $item->getAttribute("type"), 
                                    "label" => $item->getAttribute("label"), 
                                    "default" => $item->getAttribute("default"),
                                    "class" => $item->getAttribute("class")
                                    );

                    switch ($field["type"]) {
                        case "select":
                            foreach ($item->childNodes as $option) {
                                if ($option instanceof \DOMElement){
                                    $field["options"][] = array("value" => $option->getAttribute("value"), "content" => $option->nodeValue);
                                }
                            }
                            break;
                        case "text":
                            break;
                    }

                    $fields[] = (object) $field;
                }
            }            
        }
        return $fields;
    }
    
    /**
     * 
     * @param type $field
     * @param type $IDPrefix
     * @param type $namePrefix
     * @return \Libs\HtmlElement|null
     */
    public static function getElement($field, $IDPrefix = "", $namePrefix = "", $value = null, $showDefaultOption = false)
    {
        $element = null;
        $name = empty($namePrefix)? $field->name : $namePrefix."[".$field->name."]";
        switch ($field->type) {
            case "text":
                $element = \Libs\Html::textbox($IDPrefix.$field->name, $name, $value);
                break;
            case "textarea":
                $element = \Libs\Html::textarea($IDPrefix.$field->name, $name, $value);
                break;
            case "select":
                if ($showDefaultOption){
                    array_unshift($field->options, array("value" => "", "content" => "پیش فرض"));
                }
                $element = \Libs\Html::select($IDPrefix.$field->name, $name, $field->options, $value);
                break;
            case "checkbox":
                $element = \Libs\Html::checkbox($IDPrefix.$field->name, $name, $value);
                break;
            case "media":
                $element = \Libs\UI::media($IDPrefix.$field->name, $name, $value);
                break;
            case "divider":
                $element = \Libs\Html::hr();
                break;
        }
        
        $element->addCSSClass($field->class);
        
        return $element;
    }
    
    public static function getDefaultValues($file)
    {
        $defaults = array();
        $doc = Tools\XML::openDOMDoc($file);
        if ($doc !== false){
            foreach ($doc->documentElement->childNodes as $item){
                if ($item instanceof \DOMElement && $item->getAttribute("type") != "divider"){
                    $defaults[$item->getAttribute("name")] = $item->getAttribute("default");
                }
            }            
        }
        return $defaults;
    }
    
}

?>