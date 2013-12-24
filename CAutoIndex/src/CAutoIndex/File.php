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
 * CAutoIndex File class
 */
class File extends Element
{
    /**
     * File extension
     * @var string
     */
    protected $_extension;

    /**
     * File size
     * @var integer
     */
    protected $_size;
     
    /**
     * Return the file extension
     * @return string
     */
    public function getExtension()
    {
        if ($this->_extension === null) {
            $this->_extension = strtolower(pathinfo($this->_path, PATHINFO_EXTENSION));
        }

        return $this->_extension;
    } 

    /**
     * Return the file size in bytes
     * @return integer
     */
    public function getSize()
    {
        if ($this->_size === null) {
            $this->_size = is_file($this->_path) ? filesize($this->_path) : 0;
        }

        return $this->_size;
    } 

    /**
     * Return the file size formatted
     * @return string
     */
    public function getSizeString()
    {
        return $this->_formatFileSize($this->getSize());
    }  

    /**
     * Return true if the file is a image
     * @return boolean
     */
    public function isImage()
    {
        return in_array($this->getExtension(), array('jpg', 'jpeg', 'gif', 'png'));
    }

    /**
     * Return the source code of the file
     * @return string
     */
    public function getSource()
    {
        $contents = file_get_contents($this->_path);
        
        if (!empty($contents)) {
            $contents = mb_convert_encoding($contents, 'UTF-8', mb_detect_encoding($contents));
        }
        
        return $contents;
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
}