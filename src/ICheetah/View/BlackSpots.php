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
        "page" => [],
        "sections" => [],
    ];
    
    protected $matches = "";

    public function __construct(array $repositories = [])
    {
        $this->initRepos();
        $this->repositories = array_merge($this->repositories, $repositories);
    }    
    
    public function render(View $view)
    {
        //Check cache system
        $cachedFile = $this->isCached($view);
        if (!$cachedFile){
            //If no file found in cache
            //Then try to parse the view
            $content = $this->parseView($view);
            // Save as file to cache system
            $cachedFile = cache()->saveView($view->getName(), $content);
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
     * @param string $name
     */
    protected function findView($name)
    {
        if (strpos($name, ".")){
            $relativePath = Tools\Path::switchSeparator($name, ".", DIRECTORY_SEPARATOR);
        }
        
        foreach ($this->repositories as $repo) {
            $file = $repo . DIRECTORY_SEPARATOR . $relativePath . ".php";
            if (Tools\Findder::isFile($file)){
                return $file;
            }
        }
        return false;
    }
    
    protected function parseView(View $view)
    {
        if (($file = $this->findView($view->getName())) == false){
            throw new Exceptions\ViewNotFoundException();
        }
        
        try {
            // get called view content
            $fileContent = Tools\Findder::getContents($file);
        } catch (Tools\Exceptions\FileNotFoundException $exc) {
            throw new Exceptions\ViewNotFoundException();
        }
        
        $content = preg_replace_callback("/<web:(?'begin'\w+).+?(?=>)>|<\/web:(?'end'\w+)(?=>)>/", [$this, "parseTags"], $fileContent);
        
//        return $this->matches;
        return $content;
    }
    
    protected function isCached(View $view)
    {
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
    
    protected function getTagAttributes($tag)
    {
        $matches = [];
        preg_match_all("/(?<=\s)(\S+)=[\'\"](\S+)[\'\"]/", $tag, $matches);
        logger($matches);
        return $matches;
    }

    protected function renderFile($file)
    {
        if (!Tools\Findder::isFile($file)){
            return "";
        }
        
        ob_start();
        include $file;
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
    
    //Parsers
    
    protected function parsePageBeginTag($match)
    {
        $attrs = $this->getTagAttributes($match[0]);
        return "<?php \$this->beginPage('{$attrs['extends']}'); ?>";
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
    
    //Middle code functionality methods
    
    protected function beginPage($view)
    {
        
    }
    
    protected function beginSection($id)
    {
        
    }
    
    protected function endSection()
    {
        
    }    

    
    
    
    
    
}
