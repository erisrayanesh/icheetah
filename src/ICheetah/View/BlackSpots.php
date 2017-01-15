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

    public function __construct(array $repositories = [])
    {
        $this->initRepos();
        $this->repositories = array_merge($this->repositories, $repositories);
    }    
    
    public function render(View $view)
    {
        if (($file = $this->findView($view->getName())) == false){
            throw new ViewNotFoundException();
        }
        
        $content = $this->parseView($view, $file);
        
        return $content;
    }
    
    public function parseView(View $view, $file)
    {
        ob_start();
        require_once $file;
        $content = $this->parseViewContent(ob_get_contents());
        ob_end_clean();
        return $content;
    }
    
    public function parseViewContent($content)
    {
        
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
        
        $file = "";
        
        foreach ($this->repositories as $repo) {
            $file = $repo . DIRECTORY_SEPARATOR . $relativePath;
            if (Tools\Findder::getAbsPath($file)){
                break;
            }
        }
        
                
        if (!Tools\Findder::fileExist("$file.php")){
            return false;            
        }
        
        return "$file.php";
    }
    
}
