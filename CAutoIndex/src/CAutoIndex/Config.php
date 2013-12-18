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
 * CAutoIndex Config class
 */
class Config
{
    /**
     * Object Instance
     * @var Config
     */
    private static $instance = null;

    /**
     * Array of config parameters
     * @var array
     */
    private $params = array();

    /**
     * Return the object instance
     * @return Config
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Config();
        }
        return self::$instance;
    }

    /**
     * Get a config parameter
     * @param  string $key Parameter name
     * @return mixed
     */
    public static function get($key)
    {
        $instance = self::getInstance();
        return isset($instance->params[$key]) ? $instance->params[$key] : null;
    }

    /**
     * Set a config parameter
     * @param string $key   Parameter name
     * @param mixed  $value Parameter value
     */
    public static function set($key, $value)
    {
        $instance =  self::getInstance();
        $instance->params[$key] = $value;
    }
}