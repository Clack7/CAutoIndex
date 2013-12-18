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
        $path = utf8_decode($path);

        $name   = utf8_encode(basename($path));

        $path = realpath($path);

        $ep = Config::get('explorablePath'); $ds = DIRECTORY_SEPARATOR;
        
        if (!$path || strpos($path, $ep) !== 0 || 
            strpos($path . $ds, $ep . $ds . Config::get('sysDirName') . $ds) !== false ||
            strpos($path . $ds, $ep . $ds . '.git' . $ds) !== false ||
            in_array($path, array(
                $ep . $ds . '.htaccess', 
                $ep . $ds . '.gitignore', 
            )) || ($this->isDir() && !is_dir($path))) {
            throw new \Exception('Invalid path.');
        }

        $this->_path   = $path;
        $this->_name   = $name;
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
     * Return relative URL of the element
     * @return string
     */
    public function getUrl()
    {
        return '/' . Config::get('subDir') . trim(utf8_encode(strtr(
            $this->_path,
            array(
                Config::get('explorablePath') => '',
                DIRECTORY_SEPARATOR => '/',
            )
        )), '/');
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
        
        if (file_exists(Config::get('explorablePath') . '/' . Config::get('sysDirName') . '/web/img/icons/ico-' . $this->getExtension() . '.png')) {
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
    private function isLink()
    {
        return $this->_isLink;
    }
}