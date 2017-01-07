<?php
namespace ICheetah\UI;

class BootstrapUI
{
    public static function dialog($id = "", $header = "", $body = "", $footer = "", $options = array(), $attributes = null)
    {
        $element = new HtmlElement("div", $id);
        $element->setForceClose(false);
        $element->mergeAttributes($attributes);
        
        $mainContent = "";
        
        if (!empty($header)){
            $head = new HtmlElement("div");
            $head->setContent($header);
            if (isset($options["header-attributes"])){
                $head->mergeAttributes($options["header-attributes"]);                
            }
            $head->addCSSClass("modal-header");
            $mainContent .= $head->element();
        }
        
        $body = new HtmlElement("div");
        if (isset($options["body-attributes"])){
            $body->mergeAttributes($options["body-attributes"]);                
        }
        $body->addCSSClass("modal-body");
        $mainContent .= $body->element();
        
        if (!empty($footer)){
            $foot = new HtmlElement("div");
            $foot->setContent($header);
            if (isset($options["footer-attributes"])){
                $foot->mergeAttributes($options["footer-attributes"]);                
            }
            $foot->addCSSClass("modal-header");
            $mainContent .= $foot->element();
        }
        
        $content = new HtmlElement("div");
        $content->setContent($mainContent);
        if (isset($options["content-attributes"])){
            $content->mergeAttributes($options["content-attributes"]);                
        }
        $content->addCSSClass("modal-content");            
        
        $dialog = new HtmlElement("div");
        $dialog->setContent($content->element());
        if (isset($options["dialog-attributes"])){
            $dialog->mergeAttributes($options["dialog-attributes"]);                
        }
        $dialog->addCSSClass("modal-dialog");
        
        $container = new HtmlElement("div");
        $container->setContent($dialog->element());
        if (isset($options["container-attributes"])){
            $container->mergeAttributes($options["container-attributes"]);                
        }
        
        return $container;
    }
    
    public static function modalDialog($id = "", $header = "", $body = "", $footer = "", $options = array(), $attributes = null)
    {
        if (!is_null($attributes)){
            if (isset($attributes["container-attributes"])){
                $attributes["container-attributes"]["class"] = "modal " . $attributes["container-attributes"]["class"]; 
            } else {
                $attributes["container-attributes"]["class"] = "modal";
            }
        }
        return self::dialog($id, $header, $body, $footer, $options, $attributes);
    }
}

?>