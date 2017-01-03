<?php
namespace ICheetah\UI;

class Template extends \ICheetah\Foundation\Singleton
{
//    JSON_UNESCAPED_UNICODE
    CONST RESPONSE_TYPE_TEXT_HTML = "Content-type: text/html; charset=utf-8";
    CONST RESPONSE_TYPE_APP_PDF = "Content-Type: application/pdf";
    CONST RESPONSE_TYPE_IMAGE_JPEG = "Content-Type: image/jpeg";
    CONST RESPONSE_TYPE_IMAGE_PNG = "Content-Type: image/png";
    
    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    protected static $instance = null;
    private $plc = array();
    private $layout = "";
    private $breadcrumbs = array();
    private $pageTitle = array();
    private $headTags = array();
    
    private $notFoundPage = "404.php";

    protected function __construct()
    {
        parent::__construct();
        
        define("APP_PATH_TEMPLATES", APP_PATH_BASE . DS . "assets" . DS . "Templates");
    }
    
    /**
     * 
     * @return Template
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    public function render()
    {
        $content = "";
        
        header_remove();
        @ob_end_clean();
        
        ob_start();
        $layout = $this->getLayout();
        $layout = implode(DS, explode(".", $layout));
        include_once  APP_PATH_TEMPLATES . DS . $layout . ".master.php";
        $content = ob_get_contents();
        ob_end_clean();

        $this->setData("head", implode("\n", $this->headTags));        
        $this->setData("title", implode(" - ", $this->pageTitle));
        
        $breadcrumbs = new HtmlElement("ol");
        $breadcrumbs->addCSSClass("breadcrumb");
        $lstBreadcrumbs = array();
        foreach ($this->breadcrumbs as $value) {
            $class = $value[1]? " class=\"active\" " : "";
            $lstBreadcrumbs[] = "<li$class>$value[0]</li>";
        }
        $breadcrumbs->setContent(implode("", $lstBreadcrumbs));
        $this->setData("breadcrumbs", $breadcrumbs->element());

        foreach ($this->plc as $key => $value) {
            $content = str_replace("@$key", $value, $content);
        }

        return $content;
    }
    
    public function renderNotFoundPage()
    {
        $this->setLayout("404");
    }

    public function setData($plc, $content)
    {
        $this->plc[$plc] = $content;
    }

    public function getData($plc)
    {
        if (isset($this->plc[$plc])) {
            return $this->plc[$plc];
        } else {
            return "";
        }
    }
    
    public function hasData($plc)
    {
        return !empty($this->getData($plc));
    }

    public function getLayout()
    {
        return $this->layout;
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }
    
    public function getNotFoundPage()
    {
        return $this->notFoundPage;
    }

    public function setNotFoundPage($notFoundPage)
    {
        $this->notFoundPage = $notFoundPage;
        return $this;
    }
    
    public function addPageTitle($title)
    {
        $this->pageTitle[] = $title;
    }

    public function removePageTitle($index)
    {
        if (isset($this->pageTitle[$index])) {
            unset($this->pageTitle[$index]);
        }
    }
    
    public function pageTitleCount()
    {
        return count($this->pageTitle);
    }

    public function addBreadcrumb($title, $href = null, $active = false)
    {
        $content = $title;
        
        if (!empty($href)){
            $a = new HtmlElement("a");
            $a->href = $href;
            $a->setContent($title);
            $content = $a->element();
        }
        
        $this->breadcrumbs[] = array($content, $active);
    }

    public function removeBreadcrumb($index)
    {
        if (isset($this->breadcrumbs[$index])) {
            unset($this->breadcrumbs[$index]);
        }
    }
    
    public function breadcrumbCount()
    {
        return count($this->breadcrumbs);
    }
    
    public function addJS ($strJS)
    {
        $tag = new HtmlElement("script");
        $tag->type = "text/javascript";
        $tag->setContent($strJS);
        $this->headTags[] = $tag->element();
    }
    
    public function addJSFile ($strPath)
    {
        $tag = new HtmlElement("script");
        $tag->type = "text/javascript";
        $tag->src = $strPath;
        $tag->setForceClose(true);
        $this->headTags[] = $tag->element();
    }
    
    public function addCSS ($strCSS, $strMedia = null, $strType = "text/css")
    {
        $tag = new HtmlElement("style");
        $tag->type = $strType;
        if (strlen($strMedia) > 0){
            $tag->media = $strMedia;
        }
        $tag->setContent($strCSS);
        $this->headTags[] = $tag->element();
    }
    
    public function addCSSFile ($strPath, $strMedia = null, $strType = "text/css")
    {
        $tag = new HtmlElement("link");
        $tag->rel = "stylesheet";
        $tag->type = $strType;
        $tag->href = $strPath;
        if (strlen($strMedia) > 0){
            $tag->media = $strMedia;
        }
        $this->headTags[] = $tag->element();
    }
    
    public function addMetaTag ($strName, $strContent, array $attributes = array())
    {
        $tag = new HtmlElement("meta");
        foreach ($attributes as $key => $value) {
            if (is_string($key)){
                $tag->$key = strval($value);
            }
        }
        $tag->name = $strName;
        $tag->content = $strContent;
        $this->headTags[] = $tag->element();
    }
    
    public function addHeader($value)
    {
        $this->headers[] = $value;
    }
    
    
    public function headers()
    {
        return $this->headers;
    }

}

?>