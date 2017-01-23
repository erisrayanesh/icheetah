<?php

namespace ICheetah\View;

use \ICheetah\Tools;

class BlackSpots
{
    
    /**
     *Directories to look for views
     * @var array
     */
    protected $repositories = array();
    
    protected $stack = [
        "extends" => "",
        "sections" => [],
    ];
    
    /**
     * Stack for holding sections order
     * @var array 
     */
    protected $sections = [];


    /**
     * The extended view name
     * @var string
     */
    protected $extend;


    /**
     * Place-holders contents
     * @var array
     */
    protected $contents = [];
    
    
    protected $data;

    public function __construct(array $repositories = [])
    {
        $this->initRepos();
        $this->repositories = array_merge($this->repositories, $repositories);
    }    
    
    public function render(View $view)
    {
        if (empty($this->data)){
            $this->data = $view->getData();            
        }
        //find view file
        if (($viewFile = $this->findView($view->getName())) == false){
            throw new Exceptions\ViewNotFoundException();
        }
        //Check cache system
        if (!$cachedFile = $this->getCached($view->getName(), $viewFile)){
            //If no file found in cache
            //Then try to parse the view
            $middleCode = $this->parseView($viewFile);
            // Save as file to cache system
            $cachedFile = cache()->saveView($view->getName(), $middleCode);
            $viewFile = $this->findView($view->getName());
            touch($cachedFile, filemtime($viewFile));
        }
        
        return $this->renderFile($cachedFile);
    }

    public function addRepository($path)
    {
        $this->repositories[] = $path;
    }

    public function getRepositories()
    {
        return $this->repositories;
    }

    public function setRepositories($repositories)
    {
        $this->repositories = $repositories;
        return $this;
    }

    protected function initRepos()
    {
        $repos = config("view.repositories", []);
        if (is_string($repos)){
            $repos = [$repos];
        }
        
        if (is_array($repos)){
            $this->repositories = $repos;
        }
    }

    /**
     * Finds a view in repository
     * @param string $viewName
     */
    protected function findView($viewName)
    {
        if (strpos($viewName, ".")){
            $relativePath = Tools\Path::switchSeparator($viewName, ".", DIRECTORY_SEPARATOR);
        }
        
        foreach ($this->repositories as $repo) {
            $file = $repo . DIRECTORY_SEPARATOR . $relativePath . ".php";
            if (Tools\Findder::isFile($file)){
                return $file;
            }
        }
        return false;
    }
    
    protected function parseView($viewFile)
    {
        try {
            // get called view content
            $fileContent = Tools\Findder::getContents($viewFile);
        } catch (Tools\Exceptions\FileNotFoundException $exc) {
            throw new Exceptions\ViewNotFoundException();
        }
        
        //replace predefined tags with middle code
        $content = preg_replace_callback("/<web:(?'begin'\w+).+?(?=>)>|<\/web:(?'end'\w+)(?=>)>/", [$this, "parseTags"], $fileContent);
        
        return $content;
    }
    
    protected function getCached($viewName, $viewFile)
    {
        //find cache file
        $cached = cache()->getView($viewName);
        if (!$viewFile || !$cached){
            return false;
        }
        //check file modified time
        if (filemtime($viewFile) === filemtime($cached)){
            return $cached;
        }
        return false;            
    }
    
    protected function parseTags($match)
    {
        // (?<=\s)(\S+)=['"](\S+)['"]
        
        //<web:(?'begin'\w+).+?(?=>)>|<\/web:(?'end'\w+)(?=>)>
        //https://regex101.com/r/xryTgJ/1
        //https://regex101.com/delete/9QP1BNS66KZi2wmaWVLiUEtE
//        return print_r($matches, true);
        
//        $this->matches .= print_r($matches, true);
        
        $tag = $this->isEndTag($match)? ucfirst($match['end']) . "End" : ucfirst($match['begin']) . "Begin";
        
        if (empty($tag)){
            throw new Exceptions\ViewUnknownTagException();
        }
        
        $methodName = "parse" . $tag . "Tag";
        if (method_exists($this, $methodName)){
            return call_user_func([$this, $methodName], $match);
        } else {
            return $match[0];
        }
    }
    
    protected function getTagName(array $match)
    {
        $this->isEndTag($match)? $match['end'] : $match['begin'];
    }

    protected function isEndTag(array $match)
    {
        return empty($match['begin']) && !empty($match['end']);
    }
    
    /**
     * 
     * @param string $tag
     * @return Tools\Collection
     */
    protected function getTagAttributes($tag)
    {
        $matches = [];
        $collection = new Tools\Collection();
        preg_match_all("/(?<=\s)(\S+)=[\'\"](\S+)?[\'\"]/", $tag, $matches, PREG_SET_ORDER);
        foreach ($matches as $value) {
            if (!empty($value[1])){
                $collection->set($value[1], (isset($value[2])? $value[2] : null));
            }
        }
        return $collection;
    }
    
    protected static function getTagAttrValue(array $attrs, $name)
    {
        
        return null;
    }

    protected function renderFile($file)
    {
        if (!Tools\Findder::isFile($file)){
            return "";
        }
        //Extract data from array to variable name
        extract($this->data);
        
        ob_start();
        include $file;
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    //Parsers
    
    protected function parseExtendBeginTag($match)
    {
        $attrs = $this->getTagAttributes($match[0]);
        if (!$view = $attrs->get("view")){
            $this->stack["extends"][$view] = $attrs;
        }
        return "<?php \$this->beginExtend('{$view}'); ?>";
    }
    
    protected function parseExtendEndTag($match)
    {
        return "<?php \$this->endExtend(); ?>";        
    }
    
    protected function parseSectionBeginTag($match)
    {
        $attrs = $this->getTagAttributes($match[0]);
        return "<?php \$this->beginSection('{$attrs['id']}'); ?>";
    }
    
    protected function parseSectionEndTag($match)
    {
        return "<?php \$this->endSection(); ?>";
    }
    
    protected function parsePlaceholderBeginTag($match)
    {
        $attrs = $this->getTagAttributes($match[0]);
        return "<?php \$this->beginPlaceholder('{$attrs['id']}'); ?>";
    }
    
    //Middle code functionality methods
    
    protected function beginExtend($view)
    {
        if (!empty($this->extend)){
            throw new Exceptions\ViewParserException("Nested extenting is avoided");
        }
        
        if (ob_start()){
            $this->extend = $view;
        }
    }
    
    protected function endExtend()
    {
        if (empty($this->extend)){
            throw new Exceptions\ViewParserException("End extend with no matching start extend");
        }
        
        $view = new View($this->extend, $this->data);
        echo $this->render($view);
    }

    protected function beginSection($id)
    {
        if (ob_start()){
            $this->sections[] = $id;
        }
    }
    
    protected function endSection()
    {
        //Get last section
        $section = array_pop($this->sections);
        if (empty($section)){
            throw new Exceptions\ViewParserException("End section with no matching start section");
        }
        //Get buffered content
        $contents = ob_get_contents();
        //Clean any output
        ob_end_clean();
        //Send buffered content to output storage
        if (isset($this->contents[$section])){
            $this->contents[$section] .= $contents;
        } else {
            $this->contents[$section] = $contents;            
        }
    }    
    
    protected function beginPlaceholder($id)
    {
        if (isset($this->contents[$id])){
            echo $this->contents[$id];            
        }
    }    
    
    
}
