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
 * CAutoIndex Element abstract class
 */
abstract class Element
{
    /**
     * Current path
     * @var string 
     */
    protected $_path;

    /**
     * Basename
     * @var string 
     */
    protected $_name;

    /**
     * Tells whether the element is a link to another element
     * @var boolean 
     */
    protected $_isLink = false;

    /**
     * Status
     * @var string
     */
    protected $_status;

    /**
     * Element order
     * @var array
     */
    protected $_order = array();
    
    /**
     * Constructor
     * @param string  $path
     * @param boolean $relativePath
     */
    public function __construct($path, $relativePath = false)
    {
        $this->_setPath(($relativePath ? Config::get('explorablePath') . '/' : '') . $path);
    }
    
    /**
     * Set and check a valid path
     * @param string $path
     */
    protected function _setPath($path)
    {
        $ep = Config::get('explorablePath'); $ds = DIRECTORY_SEPARATOR;

        $isLink = is_link($path) || realpath($path) != $this->_convertAbsolutePath($path);

        if ($isLink && !realpath($path)) {
            $path = $this->_convertAbsolutePath($path);
        } else {
            $path = realpath($path);
        }

        $ignoreElements = array_merge(array(
            $ep . $ds . Config::get('sysDirName'),
            $ep . $ds . '.htaccess',
        ), Config::get('ignoreElements'));

        $ignored = false;
        foreach ($ignoreElements as $ign) {
            $ign = realpath($ign);
            if ((is_dir($ign) && strpos($path . $ds, $ign . $ds) !== false) || 
                $path == $ign) {
                $ignored = true;
                break;
            }
        }
        
        if (!$path || strpos($path, $ep) !== 0 || $ignored || 
            ($this->isDir() && !is_dir($path))) {
            throw new \Exception('Invalid path.');
        }

        $this->_path   = $path;
        $this->_isLink = $isLink;
    }
    
    /**
     * Return Element Status
     * @return string
     */
    public function getStatus()
    {
        if ($this->_status === null) {            
            if ($this->isLink()) {
                if (realpath($this->_path) === false) {
                    if (!$this->isDir()) {
                        $this->_size = 0;
                    }

                    $this->_status = 'error';
                } else {
                    $this->_status = 'symlink';
                }
            } else {
                $this->_status = false;
            }
        }

        return $this->_status;
    }

    /**
     * Return the URL of the element
     * @param  boolean $subDir add the subdir
     * @param  boolean $encode URL encoded
     * @return string
     */
    public function getUrl($subDir = true, $encode = true)
    {
        $url = mb_convert_encoding('/' . ($subDir ? Config::get('subDir') : '') . trim(strtr(
            $this->_path,
            array(
                Config::get('explorablePath') => '',
                DIRECTORY_SEPARATOR => '/',
            )
        ), '/'), 'UTF-8', Config::get('fileSystemEncoding'));

        if ($encode) {
            $url = str_replace('%2F', '/', rawurlencode($url));
        }

        return $url;
    } 

    /**
     * Return true if the element is a CAutoIndex\Dir instance
     * @return boolean
     */
    public function isDir()
    {
        return get_called_class() == 'CAutoIndex\Dir';
    }

    /**
     * Return the basename of the element
     * @return string
     */
    public function getName()
    {
        if ($this->_name === null) {
            $this->_name  = mb_convert_encoding(array_pop(
                explode(DIRECTORY_SEPARATOR, $this->_path)
            ), 'UTF-8', Config::get('fileSystemEncoding'));
        }

        return $this->_name;
    }

    /**
     * Return the icon image name
     * @return string
     */
    public function getIcon()
    {
        if ($this->isDir()) {
            return 'dir';
        }

        $icons = array(
            '3gp', '7z', 'ace', 'ai', 'aif', 'aiff', 'amr', 'asf', 'asx', 'bat', 
            'bin', 'bmp', 'bup', 'cab', 'cbr', 'cda', 'cdl', 'cdr', 'chm', 
            'css', 'dat', 'dir', 'divx', 'dll', 'dmg', 'doc', 'dss', 'dvf', 
            'dwg', 'eml', 'eps', 'exe', 'file', 'fla', 'flv', 'gif', 'gz', 
            'hqx', 'htaccess', 'htm', 'html', 'ifo', 'indd', 'iso', 'jar', 
            'jpeg', 'jpg', 'js', 'json', 'lnk', 'log', 'm4a', 'm4b', 'm4p', 
            'm4v', 'mcd', 'mdb', 'mid', 'mov', 'mp2', 'mp4', 'mpeg', 'mpg', 
            'msi', 'mswmm', 'ogg', 'pdf', 'php', 'png', 'pps', 'ps', 'psd', 
            'pst', 'ptb', 'pub', 'qbb', 'qbw', 'qxd', 'ram', 'rar', 'rm', 
            'rmvb', 'rtf', 'sea', 'ses', 'sit', 'sitx', 'ss', 'swf', 'tgz', 
            'thm', 'tif', 'tmp', 'torrent', 'ttf', 'txt', 'vcd', 'vob', 'wav', 
            'wma', 'wmv', 'wps', 'xls', 'xpi', 'zip'
        );
        
        if (in_array($this->getExtension(), $icons)) {
            return $this->getExtension();
        }
        
        switch ($this->getExtension()) {
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
     * Set a element order
     * @param integer $order
     * @param string  $type
     */
    public function setOrder($order, $type)
    {
        $this->_order[$type] = $order;
    }

    /**
     * Get a element order
     * @param  string $type
     * @return integer      
     */
    public function getOrder($type)
    {
        return isset($this->_order[$type]) ? $this->_order[$type] : 0;
    }

    /**
     * Return the order key
     * @param  string $order Order type
     * @return string        Order key
     */
    public function getOrderKey($order = 'ext')
    {
        if ($order == 'size' && !$this->isDir()) {
            $key = str_pad($this->getSize(), 25, '0', STR_PAD_LEFT);
        } else {
            $key = strtolower($this->isDir() ? $this->getName() : pathinfo($this->getName(), PATHINFO_FILENAME));

            $key = str_replace('-', '|', $key);

            //Numeric natural order
            $key = preg_replace_callback( '/\d+/', function ($matches) {
                    return strlen((int) $matches[0]) . '||' . $matches[0];
            }, $key);

            $ext = $this->isDir() ? '' : $this->getExtension();
            $key = ($order == 'ext' ? $ext . ' ' : '') . $key . ' .' . $ext;
        }
        
        $key = ($this->isDir() ? '0' : '1') . $key  . ' ' . uniqid();

        return $key;
    }
    
    /**
     * Tells whether the element is a link to another element
     * @return boolean
     */
    public function isLink()
    {
        return $this->_isLink;
    }

    /**
     * Convert $path to a absolute path
     * @param  string $path 
     * @return string
     */
    protected function _convertAbsolutePath($path)
    {
        $ds = DIRECTORY_SEPARATOR;

        $path   = rtrim(str_replace($ds, '/', $path), '/');
        $parent = realpath(substr($path, 0, strrpos($path, '/')));
        $name   = array_pop(explode('/', $path));

        return $parent ? $parent . $ds . $name : false;
    }
}