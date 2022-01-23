<?php


namespace envPHP\libs;


class PluginFileGenerator
{
    protected $content;
    protected $path;
    public function __construct($path)
    {
        $this->path = $path;
    }

    public function loadContent() {
        $content = @file_get_contents($this->path);
        if($content === false) {
            throw new \Exception("Error reading file {$this->path}");
        }
        $this->content = $content;
        return $this;
    }

    public function pasteAfterString($findString, $pasteString) {
        $this->content = str_replace($findString, $findString . $pasteString, $this->content);
        return $this;
    }
    public function pasteBeforeString($findString, $pasteString) {
        $this->content = str_replace($findString, $pasteString. $findString , $this->content);
        return $this;
    }
    public function removeString($string_to_remove) {
        $this->content = str_replace($string_to_remove, '' , $this->content);
        return $this;
    }
    public function removeAfterString($findString, $removeString) {
        $this->content = str_replace($findString . $removeString, $findString, $this->content);
        return $this;
    }
    public function removeByLineNumber($line_number) {
        $content_arr = explode('\n',$this->content);


    }
    public function save() {
        if(@file_put_contents($this->path, $this->content) === false) {
            throw new \Exception("Error write file {$this->path}. Directory not exists or access is denied");
        };
    }
}