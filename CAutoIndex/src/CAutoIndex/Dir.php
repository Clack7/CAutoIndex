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
    public function explore()
    {
        $paths = array_merge(glob($this->_path . DIRECTORY_SEPARATOR . '.*'), glob($this->_path . DIRECTORY_SEPARATOR . '*'));

        $this->_elements = array();
        $explorablePath = Config::get('explorablePath') . DIRECTORY_SEPARATOR;
        $order = array('n' => array(), 'e' => array(), 's' => array());
        foreach ($paths as $path) {
            if (in_array(basename($path), array('.', '..'))) { continue; }
            
            try {
                $element = is_dir($path) ? new Dir($path) 
                                         : new File($path);
            } catch (\Exception $e) {
                continue;
            }

            $order['n'][$element->getOrderKey('name')] = $element;
            $order['e'][$element->getOrderKey('ext')]  = $element;
            $order['s'][$element->getOrderKey('size')] = $element;

            $this->_elements[] = $element;
        }

        ksort($order['n']); $order['n'] = array_values($order['n']);
        ksort($order['e']); $order['e'] = array_values($order['e']);
        ksort($order['s']); $order['s'] = array_values($order['s']);

        foreach ($this->_elements as $element) {
            $element->setOrder(array_search($element, $order['n']), 'n');
            $element->setOrder(array_search($element, $order['e']), 'e');
            $element->setOrder(array_search($element, $order['s']), 's');
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