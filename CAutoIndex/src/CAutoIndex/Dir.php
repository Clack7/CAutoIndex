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
class Dir extends Element
{
    /**
     * Child elements 
     * @var array
     */
    private $_elements;
    
    /**
     * Explore path
     * @param string  $order
     * @param boolean $asc
     */
    public function explore($order = 'ext', $asc = true)
    {
        $paths = array_merge(glob($this->_path . DIRECTORY_SEPARATOR . '.*'), glob($this->_path . DIRECTORY_SEPARATOR . '*'));

        $this->_elements = array();
        $explorablePath = Config::get('explorablePath') . DIRECTORY_SEPARATOR;
        foreach ($paths as $path) {
            if (in_array(basename($path), array('.', '..'))) { continue; }
            
            try {
                $element = is_dir($path) ? new Dir($path) 
                                         : new File($path);
            } catch (\Exception $e) {
                continue;
            }

            $this->_elements[$element->getOrderKey($order)] = $element;
        }
        
        if ($asc) {
           ksort($this->_elements); 
        } else {
           krsort($this->_elements);
        }
    }

    /**
     * Return current url parts
     * @return array
     */
    public function getParts()
    {

        $parts = trim(strtr(
            $this->_path,
            array(
                Config::get('explorablePath') => '',
                DIRECTORY_SEPARATOR => '/',
            )
        ), '/');

        return array_filter(explode('/', $parts));
    }

    /**
     * Get elements
     * @return array
     */
    public function getElements()
    {
        if ($this->_elements === null) {
            $this->explore();
        }

        return $this->_elements;
    }
}