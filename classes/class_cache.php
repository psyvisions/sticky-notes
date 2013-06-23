<?php
/**
* Sticky Notes pastebin
* @ver 0.4
* @license BSD License - www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2013 Sayak Banerjee <mail@sayakbanerjee.com>
* All rights reserved. Do not remove this copyright notice.
*/

/**
* This is a very specific implementation of caching. There is no TTL
* logic as none was needed for the context of its use.
*/

class cache
{
    // Global vars
    var $cache_dir;
    var $is_available;
    
    // Constructor
    function __construct()
    {
        $this->cache_dir = realpath('./cache') . '/';
        $this->is_available = is_readable($this->cache_dir) && is_writable($this->cache_dir);
    }
    
    // Function to add items to the cache
    function set($key, $value)
    {
        if ($this->is_available)
        {
            $filename = $this->cache_dir . 's_' . md5($key) . '.html';

            $fp = fopen($filename, 'w');
            fwrite($fp, $value);
            fclose($fp);
        }
    }
    
    // Function to get data from the cache
    function get($key)
    {
        if ($this->is_available)
        {
            $filename = $this->cache_dir . 's_' . md5($key) . '.html';
            
            if (file_exists($filename))
            {
                return file_get_contents($filename);
            }
        }
        
        return false;
    }
    
    // Garbage collector
    function _gc()
    {
        if ($this->is_available)
        {
            $dp = opendir($this->cache_dir);
            
            while (($file = readdir($dp)) !== false) 
            { 
               if ($file != '.' && $file != '..' && substr($file, 0, 2) == 's_')
               {  
                    $age = time() - filectime("{$this->cache_dir}{$file}");

                    // Delete files older than a day
                    if ($age > 86400)
                    {
                        @unlink("{$this->cache_dir}{$file}");
                    }
               } 
           }

           closedir($dp);
        }
    }
}

?>
