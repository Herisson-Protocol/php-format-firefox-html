<?php

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
