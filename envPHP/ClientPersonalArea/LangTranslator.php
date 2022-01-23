<?php
namespace envPHP\ClientPersonalArea;

class LangTranslator
{
    protected $langDir;
    protected $choosedLang;
    protected $langTranslations = [];
    function __construct($langDir = './../langs')
    {
        $this->langDir = $langDir;
    }
    function setLang($lang) {
        $this->choosedLang = $lang;
        $this->loadTransation();
        return $this;
    }
    function isTranslateExists($lang) {
        return file_exists("{$this->langDir}/{$lang}.yml") && yaml_parse_file("{$this->langDir}/{$lang}.yml");
    }

    protected  function loadTransation() {
        if(file_exists("{$this->langDir}/{$this->choosedLang}.yml")) {
            $this->langTranslations = yaml_parse_file("{$this->langDir}/{$this->choosedLang}.yml");
            if(!$this->langTranslations) {
                throw new \Exception("File {$this->choosedLang}.yml is empty or corrupted");
            }
        }
        return $this;
    }
    function parse($html) {
        foreach ($this->langTranslations as $key => $val) {
            $html = str_replace("{{{$key}}}", $val, $html);
        }
        return $html;
    }
}