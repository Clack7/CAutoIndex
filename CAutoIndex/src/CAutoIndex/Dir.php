<?php

/**
 * This file is part of CAutoIndex
 * 
 * Copyright (C) 2013 Claudio Andrés Rivero <riveroclaudio@ymail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @author    Claudio Andrés Rivero <riveroclaudio@ymail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright (C) 2013 Claudio Andrés Rivero <riveroclaudio@ymail.com>
 */
namespace CAutoIndex;

/**
 * CAutoIndex Dir class
 */
class Dir
{
    /**
     * System directory name
     * @var string
     */
    protected $_sysDir;
    
    /**
     * System subdirectory
     * @var string 
     */
    protected $_subDir;
    
    /**
     * Explorable directory path
     * @var string 
     */
    protected $_testPath;
    
    /**
     * Current working path
     * @var string 
     */
    protected $_path;
    
    /**
     * Segments of current path
     * @var array 
     */
    protected $_parts;
    
    /**
     * Files list of current path
     * @var array 
     */
    protected $_files;
    
    /**
     * Root name
     * @var string
     */
    protected $_rootName;

    /**
     * Constructor
     */
    public function __construct($subDir = '')
    {
        $this->_sysDir   = basename(realpath(__DIR__ . '/../../'));
        $this->_subDir   = $subDir;
        $this->_testPath = realpath(__DIR__ . '/../../../');
    }
    
    /**
     * Set and check a valid path
     * @param string $path
     * @return boolean
     */
    public function setPath($path)
    {
        $path = utf8_decode($path);
        $test = $this->_testPath . DIRECTORY_SEPARATOR;
        $this->_path = realpath($test . $path);

        if (!$this->_path || strpos($this->_path, $this->_testPath) !== 0
          || strpos($this->_path . DIRECTORY_SEPARATOR, $test . $this->_sysDir . DIRECTORY_SEPARATOR) !== false) {
            return false;
        }

        return true;
    }
    
    /**
     * Get current url path
     * @return string
     */
    public function getUrlPath()
    {
        if ($this->_path) {
            $url = str_replace('\\', '/', str_replace($this->_testPath, '', $this->_path));
            return $url ? $url : '/';
        }
        
        return '/';
    }
    
    /**
     * Set a root name
     * @var string $name
     * @return \Index\Dir
     */
    public function setRootName($name)
    {
        $this->_rootName = $name;
        
        return $this;
    }
    
    /**
     * Get root dir (for breadcrumbs)
     * @return string
     */
    public function getRootName()
    {
        if (!$this->_rootName) { 
            if ($this->_subDir) {
                $parts = explode('/', trim($this->_subDir, '/'));
                $rootName = end($parts);
                $this->_rootName = $rootName ? $rootName : $_SERVER['SERVER_NAME'];
            } else {
                $this->_rootName = $_SERVER['SERVER_NAME'];
            }
        }
        
        return $this->_rootName;
    }
    
    /**
     * Return current url parts
     * @return array
     */
    public function getParts()
    {
        return $this->_parts;
    }
    
    /**
     * Get files
     * @return array
     */
    public function getFiles()
    {
        return $this->_files;
    }

    /**
     * Get system dir name
     * @return string
     */
    public function getSysDir()
    {
        return $this->_sysDir;
    }
    
    /**
     * Explore path
     * @param string  $path
     * @param string  $order
     * @param boolean $asc
     * @return boolean
     */
    public function explorePath($path, $order = 'ext', $asc = true)
    {
        if (!$this->setPath($path)) { return false; }

        $this->_parts = array_filter(explode('/', trim($path, '/')));

        $files = array_merge(glob($this->_path . DIRECTORY_SEPARATOR . '.*'), glob($this->_path . DIRECTORY_SEPARATOR . '*'));

        $this->_files = array();
        $c = 0; $tempTestPath = $this->_testPath . DIRECTORY_SEPARATOR;
        foreach ($files as $file) {
            if ($file == $tempTestPath . $this->_sysDir || $file == $tempTestPath . '.htaccess'
             || $file == $tempTestPath . '.gitignore'|| $file == $tempTestPath . '.git') { continue; }
            
            $name = utf8_encode(basename($file));
            
            if (in_array($name, array('.', '..'))) { continue; }
            
            $isLink = $this->_isLink($file);
            $isDir  = is_dir($file);            
            
            if ($isLink) {
                if (realpath($file) === false) {
                    $size   = 0;
                    $status = 'error';
                } else {
                    $file   = realpath($file);
                    $size   = $isDir ? 0 : filesize($file);
                    $status = 'symlink';
                }
            } else {
                $size   = $isDir ? 0 : filesize($file);
                $status = null;
            }

            $ext   = $isDir ? '' : strtolower(pathinfo($file, PATHINFO_EXTENSION));
            
            if ($order == 'size' && !$isDir) {
                $key = $isDir ?  : str_pad($size, 25, '0', STR_PAD_LEFT);
            } else {
                $key = strtolower($isDir ? $name : pathinfo($name, PATHINFO_FILENAME));

                $key = str_replace('-', '|', $key);

                //Numeric natural order
                $key = preg_replace_callback( '/\d+/', function ($matches) {
                        return strlen((int) $matches[0]) . '||' . $matches[0];
                }, $key);

                $key = ($order == 'ext' ? $ext . ' ' : '') . $key . ' .' . $ext;
            }
            
            $key = ($isDir ? '0' : '1') . $key  . ' ' . uniqid();
            
            $isImage = in_array($ext, array('jpg', 'jpeg', 'gif', 'png'));
            
            $url = trim(utf8_encode(str_replace('\\', '/', str_replace($this->_testPath, '', $file))), '/');
            
            $this->_files[$key] = array(
                'name'    => $name,
                'url'     => '/' . $this->_subDir . $url,
                'relUrl'  => '/' . $url,
                'icon'    => $this->_getIconName($file),
                'ext'     => $ext,
                'size'    => $isDir ? null : $this->_formatFileSize($size),
                'isDir'   => $isDir,
                'isImage' => $isImage,
                'status'  => $status,
            );
            $c++;
        }
        
        if ($asc) {
           ksort($this->_files); 
        } else {
           krsort($this->_files);
        }
        
        return true;
    }
    
    /**
     * Get source code
     * @param string $path
     * @return mixed
     */
    public function getSource($path)
    {
        if (!$this->setPath($path) || !is_file($this->_path)) { return false; }

        if ($this->_path == $this->_testPath . DIRECTORY_SEPARATOR . '.htaccess'
         || $this->_path == $this->_testPath . DIRECTORY_SEPARATOR . '.gitignore'
         || strpos($this->_path, $this->_testPath . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR) !== false) { return false; }

        $contents = file_get_contents($this->_path);
        
        if (!empty($contents)) {
            $contents = mb_convert_encoding($contents, 'UTF-8', mb_detect_encoding($contents));
        }
        
        return $contents;
    }
       
    /**
     * Return a icon name
     * @param string $file
     * @return string
     */
    private function _getIconName($file)
    {
        if (is_dir($file)) {
            return 'dir';
        }
        
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        if (file_exists($this->_testPath . '/' . $this->_sysDir . '/web/img/icons/ico-' . $ext . '.png')) {
            return $ext;
        }
        
        switch ($ext) {
            case 'docx':
                return 'doc';
                break;
            
            case 'xlsx':
                return 'xls';
                break;
        }
        
        return 'file';
    }

    /**
     * Format a file size
     * @param integer $size
     * @return string
     */
    private function _formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' B';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' B';
        } else {
            $bytes = '0 B';
        }

        return $bytes;
    }
    
    /**
     * Check if the file is a link
     * @param string $file
     * @return boolean
     */
    private function _isLink($file)
    {
        return is_link($file) || realpath($file) != $file;
    }
}