<?php

namespace ICheetah\UI;

class Paginator extends HtmlElement
{

    private $listSize;
    private $activePage;
    private $totalItems;
    private $linksCount = 3;
    private $link = "";
    private $onclick = "";
    
    private $firstText = "<<";
    private $previoustText = "<";
    private $nextText = ">";
    private $lastText = ">>";

    public function __construct($listSize, $totalItems, $activePage = 1, $strID = "")
    {
        parent::__construct("ul", $strID);
        $this->listSize = $listSize;
        $this->activePage = $activePage;
        $this->totalItems = $totalItems;
        $this->addCSSClass("pagination");
    }
    
    public function getListSize()
    {
        return $this->listSize;
    }

    public function getActivePage()
    {
        return $this->activePage;
    }

    public function getTotalItems()
    {
        return $this->totalItems;
    }

    public function setListSize($listSize)
    {
        $this->listSize = $listSize;
        return $this;
    }

    public function setActivePage($activePage)
    {
        $this->activePage = $activePage;
        return $this;
    }

    public function setTotalItems($totalItems)
    {
        $this->totalItems = $totalItems;
        return $this;
    }

    public function getLinksCount()
    {
        return $this->linksCount;
    }

    public function setLinksCount($linksCount)
    {
        $this->linksCount = $linksCount;
        return $this;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }
    
    public function getOnclick()
    {
        return $this->onclick;
    }

    public function setOnclick($onclick)
    {
        $this->onclick = $onclick;
        return $this;
    }    
    
    public function getFirstText()
    {
        return $this->firstText;
    }

    public function getPrevioustText()
    {
        return $this->previoustText;
    }

    public function getNextText()
    {
        return $this->nextText;
    }

    public function getLastText()
    {
        return $this->lastText;
    }

    public function setFirstText($firstText)
    {
        $this->firstText = $firstText;
        return $this;
    }

    public function setPrevioustText($previoustText)
    {
        $this->previoustText = $previoustText;
        return $this;
    }

    public function setNextText($nextText)
    {
        $this->nextText = $nextText;
        return $this;
    }

    public function setLastText($lastText)
    {
        $this->lastText = $lastText;
        return $this;
    }

    protected function createLink($text, $pageNum, $class = "", $active = false, $enabled = true)
    {
        $element = new HtmlElement("li");
        
        $a = null;
        
        if ($enabled && !$active){
            
            $a = new HtmlElement("a");
            
            if (!empty($this->getLink())) {
                $a->href = str_replace("@page", $pageNum, $this->getLink());
            } else {
                $a->href = "#";                
            }
            
            if (!empty($this->getOnclick())) {
                $a->onclick = str_replace("@page", $pageNum, $this->getOnclick());
            }
            
        } else {
            
            $a = new HtmlElement("span");
            if ($active){
                $element->addCSSClass("active");                
            } else {
                $element->addCSSClass("disabled");                
            }
        }
                
        if (!empty($class)) {
            $element->addCSSClass($class);            
        }
                
        $a->setContent("<span aria-hidden=\"true\">$text</span>");
        
        $element->setContent($a->element());
        
        return $element->element();
    }

    protected function render()
    {
        $strContent = "";
        
        $last = ceil( $this->getTotalItems() / $this->getListSize() );
        $start = (($this->getActivePage() - $this->getLinksCount() ) > 0 ) ? $this->getActivePage() - $this->getLinksCount() : 1;
        $end = (($this->getActivePage() + $this->getLinksCount() ) < $last ) ? $this->getActivePage() + $this->getLinksCount() : $last;
        
        $strContent .= $this->createLink($this->getFirstText(), 1, "", false,($this->getActivePage() != 1));
        $strContent .= $this->createLink($this->getPrevioustText(), ($this->getActivePage() - 1), "", false, ($this->getActivePage() - 1) >= 1);
        
        if ($start > 1) {
            $class = "disable";
            $strContent .= '<li class="disabled"><span>...</span></li>';
        }

        for ($i = $start; $i <= $end; $i++) {
            $class = ( $this->getActivePage() == $i ) ? "active" : "";
            $strContent .= $this->createLink($i, $i, $class, ( $this->getActivePage() == $i ), true);
        }

        if ($end < $last) {
            $strContent .= '<li class="disabled"><span>...</span></li>';
        }
        
        $strContent .= $this->createLink($this->getNextText(), ($this->getActivePage() + 1), "", false, ($this->getActivePage() + 1 <= $last));
        $strContent .= $this->createLink($this->getLastText(), $last, "", false, ($this->getActivePage() < $last));
        
        $this->setContent($strContent);
        return parent::render();
    }

    
    

}

?>