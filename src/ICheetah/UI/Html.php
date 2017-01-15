<?php
namespace ICheetah\UI;

class Html
{
    
    public static function label($id = "", $name = "", $for = "", $content = "", $attributes = null)
    {
        $element = new HtmlElement("label", $id);
        $element->for = $for;
        $element->setForceClose(true);
        $element->setName($name);
        $element->setContent($content);
        $element->mergeAttributes($attributes);
        return $element;
    }
    
    public static function textbox($id = "", $name = "", $value = "", $attributes = null)
    {
        $element = new HtmlElement("input", $id);
        $element->type = "text";
        $element->value = $value;
        $element->setForceClose(false);
        $element->setName($name);
        $element->mergeAttributes($attributes);
        return $element;
    }
    
    public static function textarea($id = "", $name = "", $content = "", $attributes = null)
    {
        $element = new HtmlElement("textarea", $id);
        $element->setForceClose(true);
        $element->setName($name);
        $element->setContent($content);        
        $element->mergeAttributes($attributes);
        return $element;
    }
    
    public static function checkbox($id = "", $name = "", $value = "", $attributes = null)
    {
        $element = new HtmlElement("input", $id);
        $element->type = "checkbox";
        $element->value = $value;
        $element->setForceClose(false);
        $element->setName($name);
        $element->mergeAttributes($attributes);
        return $element;
    }
    
    public static function submit($id = "", $name = "", $value = "", $attributes = null)
    {
        $element = new HtmlElement("input", $id);
        $element->type = "submit";
        $element->value = $value;
        $element->setForceClose(false);
        $element->setName($name);
        $element->mergeAttributes($attributes);
        return $element;
    }
    
    public static function radiobox($id = "", $name = "", $value = "", $attributes = null)
    {
        $element = new HtmlElement("input", $id);
        $element->type = "radio";
        $element->value = $value;
        $element->setForceClose(false);
        $element->setName($name);
        $element->mergeAttributes($attributes);
        return $element;
    }
    
    /**
     * 
     * @param type $id
     * @param type $name
     * @param array $options
     * @param type $attributes
     * @return \Libs\HtmlElement
     */
    public static function select($id = "", $name = "", array $options = array(), $selectedValue = null, $attributes = null)
    {
        $element = new DropDownList($id, $name);
        $element->mergeAttributes($attributes);
        foreach ($options as $value) {
            //Debug::out($value["value"] . " " . $selectedValue . "\n", true);
            $element->addItem($value["content"], $value["value"], $value["value"] == $selectedValue);
        }
        return $element;
    }
    
    public static function option($value = "", $content = "", $attributes = null)
    {
        $element = new HtmlElement("option");
        $element->setForceClose(true);
        $element->value = $value;
        $element->setContent($content);        
        $element->mergeAttributes($attributes);
        return $element;
    }
    
    public static function hr($id = "", $attributes = null)
    {
        $element = new HtmlElement("hr", $id);
        $element->setForceClose(false);
        $element->mergeAttributes($attributes);
        return $element;
    }
        
    public static function iframe($id = "", $href = "", $attributes = null)
    {
        $element = new HtmlElement("iframe", $id);
        $element->setForceClose(true);
        $element->href = $href;
        $element->mergeAttributes($attributes);
        return $element;
    }
    
}