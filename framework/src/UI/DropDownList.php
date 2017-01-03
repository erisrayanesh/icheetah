<?php
namespace ICheetah\UI;

use ICheetah\Tools\Convert;

class DropDownList extends HtmlElement
{

    private $options = array();

    protected function isInRange($index)
    {
        return is_int($index) && ($index < $this->count()) && ($index > -1);
    }

    public function __construct($strID = "", $strName = "")
    {
        parent::__construct("select", $strID);
        $this->setName($strName);
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
            return $this->insertItem(0, $title, $value, $selected);
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

    protected function render()
    {
        $retVal = array();
        foreach ($this->options as $value) {
            $selected = Convert::toBool($value["selected"])? "selected=\"selected\"" : "";
            $retVal[] = "<option $selected value=\"" . $value["value"] . "\" >" . $value["title"] . "</option>";
        }        
        $this->setContent(implode("", $retVal));
        return parent::render();
    }

}

?>