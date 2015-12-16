<?php
/**
 * exception class for curriculum exception messages
 * 
 * @abstract This file is part of curriculum - http://www.joachimdieterich.de
 * @package core
 * @filename exception.class.php
 * @copyright 2013 Joachim Dieterich
 * @author Joachim Dieterich
 * @date 2013.11.27 08:14
 * @license 
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by  
 * the Free Software Foundation; either version 3 of the License, or (at your option) any later version.                                   
 *                                                                       
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of        
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details:                          
 *                                                                       
 * http://www.gnu.org/copyleft/gpl.html      
 */
class CurriculumException extends Exception  {
    /**
     * class constructor
     * @param string $message
     * @param int $code 
     */
    public function __construct($message, $code = 0) {
        global $USER, $PAGE, $LOG;
        $LOG->add($USER->id, 'CurriculumException', $PAGE->url,  'Browser: '.$PAGE->browser. ' View: '.$PAGE->action); 
        parent::__construct($message, $code, NULL);
    }
    
    /**
     * Generate String
     * @return string 
     */
    public function __toString() {
        return __CLASS__ . ":[{$this->code}]: {$this->message}\n";
    }
}