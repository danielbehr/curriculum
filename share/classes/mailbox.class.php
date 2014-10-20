<?php
require_once 'mail.class.php';

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}
/** 
 * This file is part of curriculum - http://www.joachimdieterich.de
 * 
 * @package core
 * @filename mailbox.class.php
 * @copyright 2013 Joachim Dieterich
 * @author Joachim Dieterich
 * @date 2013.05.09 21:21
 * @license
 * This program is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by  
 * the Free Software Foundation; either version 3 of the License, or     
 * (at your option) any later version.                                   
 *                                                                       
 * This program is distributed in the hope that it will be useful,       
 * but WITHOUT ANY WARRANTY; without even the implied warranty of        
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         
 * GNU General Public License for more details:                          
 *                                                                       
 * http://www.gnu.org/copyleft/gpl.html      
 */

class Mailbox {
    /**
     * inbox array
     * @var array 
     */
    public $inbox;//array();
     /**
     * outbox array
     * @var array 
     */
    public $outbox;//array();
     /**
     * inbox array
     * @var array 
     */
    public $deleted_messages;//array();
    
    /**
     * class constructor 
     */
    public function __construct() {
       
    }
    
    /**
     * load inbox of a user
     * @param int $user_id 
     */
    public function loadInbox($user_id){
        global $USER; 
        if (checkCapabilities('mail:loadInbox', $USER->role_id)){ //check capability
            $this->loadMailbox($user_id, 'receiver_id');
        }
    }
    /**
     * load new messages of a user
     * @param int $user_id 
     */
    public function loadNewMessages($user_id){
        global $USER; 
        if (checkCapabilities('mail:loadInbox', $USER->role_id)){ //check capability
            $this->loadMailbox($user_id, 'receiver_id', 'new');
        }
    }
    /**
     * load outbox of a user
     * @param int $user_id 
     */
    public function loadOutbox($user_id){
        global $USER; 
        if (checkCapabilities('mail:loadOutbox', $USER->role_id)){ //check capability
            $this->loadMailbox($user_id, 'sender_id');
            
        }
    }
    /**
     * load deleted messages of a user
     * @param int $user_id 
     */
    public function loadDeletedMessages($user_id){
        global $USER; 
        if (checkCapabilities('mail:loadDeletedMessages', $USER->role_id)){ //check capability
            $this->loadMailbox($user_id, 'deleted');
        }    
    }
    /**
     * load a mailbox
     * in = inbox
     * out = outbox
     * deleted = all deleted mails of a user
     * @param int $user_id
     * @param string $mailbox 
     */
    private function loadMailbox($user_id, $mailbox = 'delete', $selector = ''){ 
        if ($mailbox != 'deleted' AND $selector != 'new'){
            $db = DB::prepare('SELECT * FROM message WHERE '.$mailbox.' = ? ORDER BY id DESC');
            $db->execute(array($user_id));
        } elseif ($selector == 'new') {
            $db = DB::prepare('SELECT msg.* FROM message AS msg WHERE msg.receiver_id = ?
                        AND msg.status = 0 ORDER BY msg.id DESC');
            $db->execute(array($user_id));        
        } else {
            $db = DB::prepare('SELECT msg.* FROM message AS msg WHERE msg.sender_id = ? OR msg.receiver_id = ?
                        AND msg.status = -1 ORDER BY msg.id DESC');
            $db->execute(array($user_id, $user_id));        
        }
       
        while ($result = $db->fetchObject()) {
            $getMail = new Mail();
            $getMail->id                 = $result->id;
            $getMail->sender_id          = $result->sender_id;

            $db_01 = DB::prepare('SELECT username, firstname, lastname FROM users WHERE id = ?');
            $db_01->execute(array($getMail->sender_id));
            $sender = $db_01->fetchObject();
            if ($sender){
                $getMail->sender_username    = $sender->username;
                $getMail->sender_firstname   = $sender->firstname;
                $getMail->sender_lastname    = $sender->lastname;
            }
            $getMail->receiver_id        = $result->receiver_id;
            $db_02 = DB::prepare('SELECT username, firstname, lastname FROM users WHERE id = ?');
            $db_02->execute(array($getMail->sender_id)); 
            $receiver = $db_02->fetchObject();
            if ($receiver){
                $getMail->receiver_username  = $receiver->username;
                $getMail->receiver_firstname = $receiver->firstname;
                $getMail->receiver_lastname  = $receiver->lastname;
            }
            $getMail->subject            = $result->subject;
            $getMail->message            = $result->message;
            $getMail->creation_time      = $result->creation_time;
            $getMail->status             = $result->status;
            switch ($mailbox) {
                case 'receiver_id': //inbox
                                    $this->inbox[]            = $getMail;
                                    break;
                case 'sender_id': // outbox
                                    $this->outbox[]           = $getMail;
                                    break;
                case 'deleted':   // deleted messages
                                    $this->deleted_messages[] = $getMail;
                                    break;
                default:            break;
            }
        }
    }    
}
?>