<?php
namespace Library;

class TemplateEngine
{
    private $file;
    private $words = array();

    public function __construct($path)
    {
        $this->file = file_get_contents($path);
    }
    public function addVariable($brackets, $replacement)
    {
            $this->words[$brackets] = $replacement;
    }

    public function countTemplates()
    {
        return preg_match_all("/{{.+}}/", $this->file);
    }

    public function render()
    {
        $newHtml = $this->file;
        
        foreach ($this->words as $brackets => $replacement) {
            $brackets = "{{" . $brackets . "}}";
            $newHtml = str_replace($brackets, $replacement, $newHtml);
        }
        return preg_replace("/{{.+}}/", "", $newHtml);
    }
}