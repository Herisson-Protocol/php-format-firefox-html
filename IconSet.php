<?php

class IconSet
{

    protected $icons;
    protected static $counter;
    
    public function IconSet()
    {
        $icons = array();
    }
    
    public function add($hash, $content)
    {
        if (isset($this->icons[$hash])) return;
        $this->icons[$hash] = $content;
    }
    public function get($hash)
    {
        return $this->icons[$hash];
    }
    public function writeIcons()
    {
    
        foreach ($this->icons as $hash => $icon) {
            $fp = fopen('icons/'.$hash.'.ico', 'w+');
            fwrite($fp, base64_decode(substr($icon, strpos($icon, ','), strlen($icon))));
            fclose($fp);
        }
    }
}
