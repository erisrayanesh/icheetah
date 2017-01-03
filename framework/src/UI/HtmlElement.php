<?php

namespace ICheetah\UI;

class HtmlElement
{

    private $strTag;
    private $strContent;
    private  $forceClose = false;

    /**
     *
     * @var string
     */
    private $strStyle;

    /**
     * @var ArrayList
     */
    private $lstCssClass = array();

    /**
     * @var Boolean
     */
    private $isVisable = true;
    
    /**
     * @var Map
     */
    private $lstAttributes = array();

    protected function attrSerialize()
    {
        $retVal = "";

        $others = array();
        foreach ($this->attributes() as $key => $item) {
            if ($item != "" && $item != null)
                $others[] = "$key=\"$item\"";
        }

        //Proccess tag classes
        $cls = $this->cssClass();
        if ($cls != "") {
            $others[] = "class" . "=\"$cls\"";
        }

        //Concat all attributes
        $retVal .= implode(" ", $others);

        //Proccess style attribute and visibility
        if ($this->style() || !$this->visible()) {
            $retVal .= " style=\"";
            if ($this->style())
                $retVal .= "{$this->style()} ";
            if (!$this->visible())
                $retVal .= "visiblity : Hidden";
            $retVal .= "\"";
        }

        return $retVal;
    }
    
    protected function render()
    {
        $tag = array();
        $tag[] = "<{$this->strTag} {$this->attrSerialize()}";
        if ($this->forceClose() || strlen($this->content()) > 0) {
            $tag[] = ">";
            $tag[] = $this->content();
        } 
        
        if ($this->forceClose() || strlen($this->content()) > 0){
            $tag[] = "</{$this->strTag}>";
        } else {
            $tag[] = "/>";
        }
        
        return implode("", $tag);
    }

    public function __construct($strTag, $strID = "")
    {
        $this->setID($strID);
        $this->setTag($strTag);
    }
    
    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    public function __get($name)
    {
        return $this->attribute($name);
    }

    public function __isset($name)
    {
        return $this->hasAttribute($name);
    }

    public function __unset($name)
    {
        $this->removeAttribute($name);
    }

    public function tag()
    {
        return $this->strTag;
    }

    public function setTag($strTag)
    {
        $this->strTag = $strTag;
        return $this;
    }
    
    public function forceClose()
    {
        return $this->forceClose;
    }

    public function setForceClose($forceClose)
    {
        $this->forceClose = $forceClose;
        return $this;
    }
    
    public function content()
    {
        return $this->strContent;
    }

    public function setContent($strContent)
    {
        $this->strContent = $strContent;
        return $this;
    }

    /**
     * Returns a map of tag attributes.
     * @return array
     */
    public function attributes()
    {
        return $this->lstAttributes;
    }

    /**
     * Returns an attribute value otherwise false.
     * @param string $strKey
     * @return boolean|string
     */
    public function attribute($strKey)
    {
        if (isset($this->lstAttributes[$strKey])) {
            return $this->lstAttributes[$strKey];
        } else {
            return null;
        }
    }

    /**
     * Sets an attribute value.
     * @param string $strKey Attribute name.
     * @param any $value Attribute value.
     */
    public function setAttribute($strKey, $value)
    {
        $this->lstAttributes[$strKey] = $value;
        return $this;
    }
    
    public function hasAttribute($strKey)
    {
        return array_key_exists($strKey, $this->lstAttributes);
    }
    
    public function removeAttribute($strKey)
    {
        if ($this->hasAttribute($strKey)){
            unset($this->lstAttributes[$strKey]);
            return true;
        } else {
            return false;
        }
    }
    
    public function mergeAttributes($attributes)
    {
        if (!is_null($attributes) && is_array($attributes)){
            $this->lstAttributes = array_merge($this->lstAttributes, $attributes);
        }
    }

    public function ID()
    {
        return $this->attribute("id");
    }

    public function setID($strID)
    {
        $this->setAttribute("id", $strID);
        return $this;
    }

    public function name()
    {
        return $this->attribute("name");
        ;
    }

    public function setName($strName)
    {
        $this->setAttribute("name", $strName);
        return $this;
    }

    public function style()
    {
        return $this->strStyle;
    }

    public function setStyle($strStyle, $append = false)
    {
        if ($append)
            $this->strStyle = Convert::toString($this->style()) . $strStyle;
        else
            $this->strStyle = $strStyle;

        return $this;
    }

    public function cssClass()
    {
        return implode(" ", $this->lstCssClass);
    }

    protected function cssClasses()
    {
        return $this->lstCssClass;
    }

    public function addCSSClass($strClass)
    {
        $classes = explode(" ", $strClass);
        foreach ($classes as $value) {
            if (!in_array($value, $this->lstCssClass)){
                $this->lstCssClass[] = $value;
            }
        }
        return $this;
    }

    public function clearCSSClass()
    {
        $this->lstCssClass = array();
        return $this;
    }

    public function removeCSSClass($strClass)
    {
        $classes = explode(" ", $strClass);
        foreach ($classes as $value) {
            $index = array_search($value, $this->lstCssClass);
            if ($index) {
                unset($this->lstCssClass[$index]);
            }
        }
        return $this;
    }

    public function switchCSSClass($strClass, $replacement)
    {
        $index = array_search($strClass, $this->lstCssClass);
        if ($index) {
            $this->lstCssClass[$index] = $replacement;
        }
        return $this;
    }

    public function toggleCSSClass($strClass)
    {
        $index = array_search($strClass, $this->lstCssClass);
        if ($index) {
            unset($this->lstCssClass[$index]);
        } else {
            $this->lstCssClass[] = $strClass;
        }
       
        return $this;
    }

    public function toolTip()
    {
        return $this->attribute("title");
    }

    public function setToolTip($strTip)
    {
        $this->setAttribute("title", $strTip);
        return $this;
    }

    public function enable()
    {
        return $this->attribute("disabled") ? false : true;
    }

    public function setEnable($value)
    {
        if (Convert::toBool($value))
            $this->setAttribute("disabled", "disabled");
        else
            $this->attributes()->remove("disabled");
        return $this;
    }

    public function visible()
    {
        return $this->isVisable;
    }

    public function setVisible($value)
    {
        $this->isVisable = Convert::toBoolean($value);
        return $this;
    }

    public function element()
    {
        return $this->render();
    }

    public function __toString()
    {
        return $this->element();
    }

}

?>