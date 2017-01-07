<?php

namespace ICheetah\UI;

class CheckBoxGroup extends HtmlElement
{

    private $options = array();
    
    private  $labelClass = "";
    private  $controlClass = "";
    private  $inline = false;
    private $groupName = "";

    protected function isInRange($index)
    {
        return is_int($index) && ($index < $this->count()) && ($index > -1);
    }

    public function __construct($strID = "", $strGroupName = "")
    {
        parent::__construct("div", $strID);
        $this->setGroupName($strGroupName);
        $this->setForceClose(true);
    }

    public function addItem($title, $value, $selected = false)
    {
        $this->options[] = array("title" => $title, "value" => $value, "selected" => $selected);
    }

    public function insertItem($index, $title, $value, $selected = false)
    {
        $item = array("title" => $title, "value" => $value, "selected" => $selected);
        if ($this->isInRange($index)) {
            $lst = array_splice($this->options, $index, 0, array($item));
            return is_array($lst);
        } elseif ($index < 0) {
            return $this->insert(0, $title, $value, $selected);
        } elseif ($index >= $this->count()) {
            return $this->addItem($title, $value, $selected);
        }
    }

    public function removeItem($index)
    {
        if (isset($this->options[$index])) {
            unset($this->options[$index]);
        }
    }

    public function removeItemByValue($value)
    {
        $i = null;
        foreach ($this->options as $key => $item) {
            if (array_pop($item) == $value) {
                $i = $key;
                break;
            }
        }
        if ($i != null) {
            unset($this->options[$i]);
        }
    }

    public function replaceItem($index, $title, $value, $selected)
    {
        if (isset($this->options[$index])) {
            $this->options[$index] = array("title" => $title, "value" => $value, "selected" => $selected);
        }
    }

    public function count()
    {
        return count($this->options);
    }
    
    public function getLableClass()
    {
        return $this->labelClass;
    }

    public function getControlClass()
    {
        return $this->controlClass;
    }

    public function isInline()
    {
        return $this->inline;
    }

    public function setLableClass($lableClass)
    {
        $this->labelClass = $lableClass;
        return $this;
    }

    public function setControlClass($controlClass)
    {
        $this->controlClass = $controlClass;
        return $this;
    }

    public function setInline($inline)
    {
        $this->inline = $inline;
        return $this;
    }

    public function getGroupName()
    {
        return $this->groupName;
    }

    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;
        return $this;
    }
            
    protected function render()
    {
        $retVal = array();
                
        foreach ($this->options as $value) {
            
            $input = new HtmlElement("input");
            $label = new HtmlElement("label");
            
            $input->setAttribute("type", "checkbox");
            $input->addCSSClass($this->getControlClass());
            $input->setName($this->getGroupName()."[]");
            $input->setAttribute("value", $value["value"]);
            
            if (Convert::toBool($value["selected"])){
                $input->setAttribute("checked", "checked");
                $label->addCSSClass("active");
            }
            
            if(!$this->isInline()){
                $retVal[] = "<div class=\"checkbox\">";
            }
            
            $label->setContent($input->element() . "<span>{$value["title"]}</span>");
            
            if ((strlen($this->getLableClass()) > 0)){
                $label->addCSSClass($this->getLableClass());                
            }
            
            $retVal[] = $label->element();
            
            if(!$this->isInline()){
                $retVal[] = "</div>";
            }  
            
        }
        
        $this->setContent(implode("", $retVal));
        return parent::render();
    }

}

?>