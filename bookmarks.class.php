<?php
/**
 * Firefox bookmarks parser
 *
 * @source http://www.polak.ro/firefox-bookmarks-parser.html
 * @license http://www.polak.ro/licencing-projects.html See the link
 */

function recomputeSpacer(&$s, $n)
{
    $s = '';
    for ($i = 0; $i<$n ; $i++) {
        $s .= "\t";
    }
}

function shortname($name, $maxlength=120, $separator='...')
{
    // shortname v2.0
    $seplen = strlen($separator);
    if (strlen($name) > $maxlength) {
        $name = substr($name, 0, ($maxlength-$seplen)/2).$separator.substr($name, strlen($name)-($maxlength-$seplen)/2, strlen($name));
    }
    return $name;
}

class Item
{

    public $_id;
    public $_parent;
    public $_isFolder;
    public $depth;
    public $name;    
    public $ADD_DATE;
    public $LAST_MODIFIED;    
    public $BID;

}
class Bookmark extends Item
{

    public $HREF;
    public $LAST_VISIT;
    public $LAST_CHARSET;
    
}

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

class Bookmarks
{
    
    public $bookmarks;
    public $currentElement;
    protected $totalElements;
    public $bookmarksFileMd5;
    public $iconset;
    
    public function Bookmarks()
    {
        $this->currentElement = 0;
        $this->totalElements = 0;
        $this->bookmarks = array();
        $this->iconset = new IconSet();
    }
    
    public function hasMoreItems()
    {
        if ($this->currentElement<($this->totalElements)) return true;
        return false;
    }
    public function getNextElement()
    {
        return $this->bookmarks[$this->currentElement++];
    }
    
    public function parse($filename)
    {
        
        
        $fp = fopen($filename, 'r');
        $contents = fread($fp, filesize($filename));
        fclose($fp);
        
                
        $lines = explode("\n", $contents);
        
        $folders = array();
        $currentDirectory = 1;
        $id_counter = 1;
        $depth = 0;
        
        array_push($folders, $id_counter);

        foreach ($lines as $val) {
        
            if (preg_match("#<DL>#", $val)) {
            
                /* goes trought one directory */
                array_push($folders, $id_counter);
                $currentDirectory = $id_counter;
                ++$depth;
                            
            } elseif (preg_match("#</DL>#", $val)) {
            
                /* leaves directory */
                array_pop($folders);
                $currentDirectory = array_pop($folders);
                $currentDirectory = ($currentDirectory == null ? 1 : $currentDirectory);
                --$depth;
                
            } elseif (preg_match("#<H3.*>(.*)</H3>#", $val, $title)) {
            
                /* registeres a directory */
                ++$id_counter;
                
                /* Creating new Item */
                //Item $item = new Item();
                
                $item->_id = $id_counter;
                $item->_parent = $currentDirectory;        
                $item->_isFolder = true;
                $item->depth = $depth;        
                $item->name = $title[1];
                $item->ADD_DATE = '';
                $item->LAST_MODIFIED = '';
                $item->BID = '';
                
                //echo '<strong>'.$item->name.' id:'.$item->_id.' p:'.$item->_parent.' d:'.$item->depth."</strong><br>\n";
                
                /* Adding folder */
                $this->add($item);
                /* Removing from memory */
                unset($item);
                
            } elseif (preg_match('#<A HREF="(.*)".*>(.*)</A>#', $val, $title)) {
                /* adds an item */
                ++$id_counter;                
                /* Creating new Item */
                //Bookmark $bookmark = new Bookmark();
                $HREF = substr($title[1], 0, strpos($title[1], '"'));            
                $title[1] = substr($title[1], strpos($title[1], 'ADD_DATE')+10, strlen($title[1]));
                
                $ADD_DATE = substr($title[1], 0, strpos($title[1], '"'));
                $title[1] = substr($title[1], strpos($title[1], 'LAST_VISIT')+12, strlen($title[1]));
                
                
                $LAST_VISIT = substr($title[1], 0, strpos($title[1], '"'));
                
                // print_r($title[1]);
                if (preg_match('#ICON_URI#', $title[1])) {        
                    $title[1] = substr($title[1], strpos($title[1], 'ICON_URI')+10, strlen($title[1]));                
                    $ICON_URI= substr($title[1], 0, strpos($title[1], '"'));
                } else {
                    $ICON_URI = '';
                }
                
                if (preg_match('#ICON#', $title[1])) {        
                    $title[1] = substr($title[1], strpos($title[1], 'ICON')+6, strlen($title[1]));                
                    $ICON = substr($title[1], 0, strpos($title[1], '"'));
                } else {
                    $ICON = '';
                }
                
                $title[1] = substr($title[1], strpos($title[1], 'LAST_CHARSET')+14, strlen($title[1]));
                
                $LAST_CHARSET = substr($title[1], 0, strpos($title[1], '"'));
                
                $ID = substr($title[1], strpos($title[1], 'ID')+4, strlen($title[1]));
                                
                $bookmark->_id = $id_counter;
                $bookmark->_parent = $currentDirectory;
                $bookmark->_isFolder = false;
                $bookmark->depth = $depth;        
                $bookmark->name = $title[2];
                $bookmarkm->ADD_DATE = $ADD_DATE;
                $bookmark->LAST_VISIT = $LAST_VISIT;
                
                $bookmark->ICON_URI = $ICON_URI;
                $bookmark->ICON = false;
                $bookmark->ICON_DATA = false;
                if (strlen($ICON)>5) {
                    $bookmark->ICON_DATA = $ICON;
                
                    $iconhash = md5($ICON);
                    $this->iconset->add($iconhash, $ICON);                
                    $bookmark->ICON = $iconhash;
                }                
                $bookmark->HREF = $HREF;                
                $bookmark->LAST_CHARSET = $LAST_CHARSET ;    
                $bookmark->BID = $ID;
                
                //echo '<em>'.$bookmark->name.' p:'.$bookmark->_parent.' <strong>d:'.$bookmark->depth."</strong></em><br>\n";
                        
                /* Adding folder */
                $this->add($bookmark);
                /* Removing from memory */
                unset($bookmark);            
            }
        }
        //die();
        $this->totalElements = count($this->bookmarks);
        
    }
    
    protected function add($item)
    {
        array_push($this->bookmarks, $item);
    }
}
?>
