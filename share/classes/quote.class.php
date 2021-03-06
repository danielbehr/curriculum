<?php
/**
* Quote Class
* 
* @abstract This file is part of curriculum - http://www.joachimdieterich.de
* @package core
* @filename quote.class.php
* @copyright 2018 Joachim Dieterich
* @author Joachim Dieterich
* @date 2018.06.04 21:58
* @license: 
*
* The MIT License (MIT)
* Copyright (c) 2018 Joachim Dieterich http://www.curriculumonline.de
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), 
* to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, 
* and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF 
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, 
* DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR 
* THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
class Quote {

    public $id;
    public $context_id; 
    public $reference_id; 
    public $reference_title;
    public $reference_object;   
    public $quote; 
    public $quote_link;
    public $creation_time; 
    public $creator_id; 
    public $creator; 
    public $curriculum; //for sorting
    public $terminal_objective_id; //for sorting
   
    public function load($id = null){
        if ($id == null){ $id = $this->id; }
        $db = DB::prepare('SELECT qu.* FROM quote AS qu WHERE qu.id = ?');
        $db->execute(array($id));
        $result     = $db->fetchObject();
        $user       = new User();
        
        if ($result){
            $this->id            = $result->id;
            $this->context_id    = $result->context_id;
            $this->reference_id  = $result->reference_id;
            $this->creation_time = $result->creation_time;
            $this->creator_id    = $result->creator_id;
            $this->creator       = $user->resolveUserId($result->creator_id);
            $this->quote         = $this->getQuote($_SESSION['CONTEXT'][$this->context_id]->context);
            return true;                                                        
        } else { 
            return false; 
        }
    }
    
    public function get($dependency, $reference_id, $ter_ids = null, $ena_ids = null){
        $user       = new User();
        $entrys     = array();
        if (empty($reference_id)){ return null; } //FIX 
        switch ($dependency) {
            case 'curriculum_content':
                        $db = DB::prepare('SELECT DISTINCT qus.quote_id, qus.context_id, qus.reference_id, qus.file_context, qus.status, qu.context_id AS qu_context_id, qu.reference_id AS content_id, ter.id AS terminal_objective_id FROM quote_subscriptions AS qus, quote AS qu, terminalObjectives AS ter
                                            WHERE qu.reference_id IN ('.implode(",", $reference_id).') AND qu.context_id = 15 AND qus.status = 1 AND qu.id = qus.quote_id
                                            AND ter.id = (SELECT id FROM terminalObjectives WHERE ((qus.context_id = 27 AND id = qus.reference_id) OR (qus.context_id = 12 AND id = (SELECT terminal_objective_id FROM enablingObjectives WHERE id = qus.reference_id))))
                                            AND ((qus.context_id = 27 AND qus.reference_id IN ('.implode(",", $ter_ids).'))
                                            OR  (qus.context_id = 12 AND qus.reference_id IN ('.implode(",", $ena_ids).'))) ORDER BY qu.reference_id, qus.quote_id, ter.id');
                        $db->execute(array());
                        while($result = $db->fetchObject()) { 
                            $this->id            = $result->quote_id;
                            $this->context_id    = $result->context_id;
                            $this->reference_id  = $result->reference_id;
                            $this->quote_link    = $result->content_id;
                            switch ($this->context_id) {
                                case 27:        $t                          = new TerminalObjective();
                                                $t->id                      = $this->reference_id;
                                                $t->load();
                                                $this->terminal_object      = $t;   
                                                $this->terminal_objective_id= $result->terminal_objective_id;   
                                    break;
                                case 12:        $e                          = new EnablingObjective();
                                                $e->id                      = $this->reference_id;
                                                $e->load(); 
                                                $this->enabling_object      = $e;
                                                $t                          = new TerminalObjective();
                                                $t->id                      = $e->terminal_objective_id;
                                                $t->load();
                                                $this->terminal_object      = $t;
                                                $this->terminal_objective_id= $result->terminal_objective_id;   
                                    break;

                                default:
                                    break;
                            }
                            //error_log($this->id.': '.$this->terminal_objective_id. ': '. $this->reference_id);
                            $this->quote         = $this->getQuote('curriculum_content', $_SESSION['CONTEXT'][$this->context_id]->context);
                            $entrys[]            = clone $this;        //it has to be clone, to get the object and not the reference
                        }
                break;


            default:    if ($ter_ids != null){
                            $cur_ids = array();
                            foreach ($ter_ids as $ter){
                                $t              = new TerminalObjective();
                                $t->id          = $ter;
                                $t->load();
                                array_push($cur_ids, $t->curriculum_id);
                            }
                            $db = DB::prepare('SELECT qu.* FROM quote AS qu, quote_subscriptions AS qus 
                                        WHERE qu.id = qus.quote_id AND qus.context_id = ? AND qus.reference_id = ? AND qu.reference_id IN (SELECT ct.id FROM content AS ct, content_subscriptions AS cts WHERE cts.context_id = 2
                                                        AND cts.reference_id IN ('.implode(",", $cur_ids).')
                                                        AND cts.content_id = ct.id)');
                            $db->execute(array($dependency, $reference_id));
                            unset($cur_ids);
                        } else {
                            $db = DB::prepare('SELECT qu.* FROM quote AS qu, quote_subscriptions AS qus 
                                            WHERE qu.id = qus.quote_id AND qus.context_id = ? AND qus.reference_id = ?');
                            $db->execute(array($dependency, $reference_id));
                        }
                        
                        while($result = $db->fetchObject()) { 
                            $this->id            = $result->id;
                            $this->context_id    = $result->context_id;
                            $this->reference_id  = $result->reference_id;
                            if ($this->context_id == 15){ //content subscribed in a curriculum context // other contextes are not available yet
                                $db1 = DB::prepare('SELECT cu.* FROM curriculum AS cu, content_subscriptions AS cts 
                                                        WHERE cu.id = cts.reference_id AND cts.context_id = ? AND cts.content_id = ?');
                                $db1->execute(array(2, $this->reference_id)); //2 --> curriculum
                                $cur_result     = $db1->fetchObject();
                                if ($cur_result){
                                    $this->reference_object = $cur_result;
                                    $this->curriculum       = $cur_result->curriculum;
                                }
                            }

                            $this->creation_time = $result->creation_time;
                            $this->creator_id    = $result->creator_id;
                            $this->creator       = $user->resolveUserId($result->creator_id);
                            $this->quote         = $this->getQuote($_SESSION['CONTEXT'][$this->context_id]->context);
                            $entrys[]            = clone $this;        //it has to be clone, to get the object and not the reference
                        }
                break;
        }
        if (!empty($entrys)) {                       
            return $entrys;
        } else {
            return false;
        }
        
    }
            
    public function getQuote($dependency = 'content'){
        
        switch ($dependency) {
            
            case 'content': $content = new Content();
                            $content->load('id', $this->reference_id);
                            $regex   = '#\<quote id="quote_'.$this->id.'"\>(.+?)\<\/quote\>#s';
                            preg_match($regex, $content->content, $matches);
                            //$matches[0] == with quote tag
                            //$matches[1] == quote only
                            $this->reference_title  = $content->title;
                            $this->quote_link       = $content->id;
                            if (isset($matches[1])){
                                return  $matches[1];
                            }
                break;
            case 'curriculum_content': 
                            $content = new Content();
                            $content->load('id', $this->quote_link);
                            $regex   = '#\<quote id="quote_'.$this->id.'"\>(.+?)\<\/quote\>#s';
                            preg_match($regex, $content->content, $matches);
                            //$matches[0] == with quote tag
                            //$matches[1] == quote only
                            $this->reference_title  = $content->title;
                            if (isset($matches[1])){
                                return  $matches[1];
                            }
                            
                break;
            default:
                break;
        } 
    }
    
    public function getQuoteSubscriptions(){
        $db = DB::prepare('SELECT cu.curriculum  FROM content_subscriptions AS cts, curriculum AS cu WHERE cts.content_id = ? AND cts.reference_id = cu.id AND cts.context_id = ?');
        $db->execute(array($this->reference_id, $_SESSION['CONTEXT']['curriculum']->context_id));
        $entrys = array();
        while($result = $db->fetchObject()) { 
            $titles         = new stdClass();
            $titles->curriculum  = $result->curriculum;
            $entrys[]       = clone $titles;        //it has to be clone, to get the object and not the reference
        }
        if (!empty($entrys)) {                       
            return $entrys;
        } else {
            return false;
        }
    }
      
    
}