<?php
/**
 * enabling objective class can add, update, delete and get data from curriculum db
 * 
 * @example
 * // Add new objective <br>
 * $new_objective = new Objective(); <br>
 * 
 * @abstract This file is part of curriculum - http://www.joachimdieterich.de
 * @package core
 * @filename objective.class.php
 * @copyright 2013 Joachim Dieterich
 * @author Joachim Dieterich
 * @date 2013.06.11 21:00
 * @license 
 *
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
class EnablingObjective {
    /**
     * ID of enabling objective
     * @var int
     */
    public $id;
    /**
     * enabling Objective
     * @var string 
     */
    public $enabling_objective;
    /**
     * Description of enabling objective
     * @var string
     */
    public $description; 
    /**
     * id of curriculum
     * @var int 
     */
    public $curriculum_id;
    /**
     * curriculum name - used for accomplished objectives on dashboard
     * @var string 
     */
    public $curriculum; 
    /**
     * id of terminal objective
     * @var int
     */
    public $terminal_objective_id; 
    /**
     * name of terminal objective
     * @var string 
     */
    public $terminal_objective; 
    /**
     * Timestamp when Grade was created
     * @var timestamp
     */
    public $creation_time; 
    /**
     * ID of User who created this Grade
     * @var int
     */
    public $creator_id; 
    /**
     * repeat interval
     * @var int 
     */
    public $repeat_interval;
    /**
     * Position of enabling_objective  within terminal_objective
     * @var type 
     */
    public $order_id; 
    /**
     * id of current accomplish status
     * @var int
     */
    public $accomplished_status_id; 
    /**
     * timestamp of last accomplish status change
     * @var timestamp
     */
    public $accomplished_time; 
    /**
     * id of teacher who set last accomplished status 
     * @var type 
     */
    public $accomplished_teacher_id; 
    /**
     * name of teacher who set accomplished status
     * @var string
     */
    public $accomplished_teacher; 
    /**
     * number of enroled users
     * @var int
     */
    public $enroled_users;
    /**
     * number of users who accomplished objective
     * @var int
     */
    public $accomplished_users; 
    /**
     * percent value - number of  users who accomplished objective
     * @var int 
     */
    public $accomplished_percent; 
    /**
     * array of files of current enabling objective
     * @var array of file object
     */
    public $files; 
            
            
    /**
     * add objective
     * @return mixed 
     */
    public function add(){
        global $USER;
        if (checkCapabilities('objectives:addEnablingObjective', $USER->role_id)){
            $db = DB::prepare('SELECT MAX(order_id)AS max FROM enablingObjectives WHERE terminal_objective_id = ?');
            $db->execute(array($this->terminal_objective_id));
            $result = $db->fetchObject();
            $this->order_id = $result->max+1;
            $db = DB::prepare('INSERT INTO enablingObjectives 
                        (enabling_objective,description,terminal_objective_id,curriculum_id,repeat_interval,order_id,creator_id) 
                        VALUES (?,?,?,?,?,?,?)');        
            $db->execute(array($this->enabling_objective, $this->description, $this->terminal_objective_id, $this->curriculum_id, $this->repeat_interval, $this->order_id, $this->creator_id));
            return DB::lastInsertId(); //returns id 
        }
    }
    
    /**
     * Update objective
     * @return boolean 
     */
    public function update(){
        global $USER;
        if (checkCapabilities('objectives:updateEnablingObjectives', $USER->role_id)){
            $db = DB::prepare('UPDATE enablingObjectives SET enabling_objective = ?, description = ?, repeat_interval = ? 
                        WHERE id = ?');
            return $db->execute(array($this->enabling_objective, $this->description, $this->repeat_interval, $this->id));
        }
    }
    
    /**
     * delete enabling objective
     * @return boolean 
     */
    public function delete(){
        global $USER;
        if (checkCapabilities('objectives:deleteEnablingObjectives', $USER->role_id)){
            $db = DB::prepare('DELETE FROM enablingObjectives WHERE id = ?');
            return $db->execute(array($this->id));
        }
    } 
    
    /**
     * Load enabling objectives from db 
     */
    public function load(){
        $db = DB::prepare('SELECT * FROM enablingObjectives WHERE id = ?');
        $db->execute(array($this->id));
        while($result = $db->fetchObject()) { 
            $this->id                    = $result->id;
            $this->enabling_objective    = $result->enabling_objective;
            $this->description           = $result->description;
            $this->curriculum_id         = $result->curriculum_id;
            $this->terminal_objective_id = $result->terminal_objective_id;
            $this->order_id              = $result->order_id;
            $this->repeat_interval       = $result->repeat_interval;
            $this->creation_time         = $result->creation_time;
            $this->creator_id            = $result->creator_id;
        }   
    }
    /**
     * get objectives depending on dependency
     * @global int $USER
     * @param string $dependency
     * @param int $id
     * @param int $group
     * @return array of EnablingObjective objects|boolean 
     */
    public function getObjectives($dependency = null, $id = null, $group = null) {
        global $USER; 
        switch ($dependency) {
                case 'user':  $db = DB::prepare('SELECT en.*, ua.status_id, ua.accomplished_time, ua.creator_id AS teacher_id
                                            FROM enablingObjectives AS en 
                                            LEFT JOIN user_accomplished AS ua ON en.id = ua.enabling_objectives_id AND ua.user_id = (SELECT id FROM users WHERE id = ?)
                                            WHERE en.curriculum_id = ?
                                            ORDER by en.terminal_objective_id, en.order_id');
                                $db->execute(array($id, $this->curriculum_id));

                                while($result = $db->fetchObject()) { 
                                    $this->id                      = $result->id;
                                    $this->enabling_objective      = $result->enabling_objective;
                                    $this->description             = $result->description;
                                    $this->curriculum_id           = $result->curriculum_id;
                                    $this->terminal_objective_id   = $result->terminal_objective_id;
                                    $this->order_id                = $result->order_id;
                                    $this->repeat_interval_id      = $result->repeat_interval;
                                    $this->creation_time           = $result->creation_time;
                                    $this->creator_id              = $result->creator_id;   
                                    $this->accomplished_status_id  = $result->status_id;   
                                    $this->accomplished_time       = $result->accomplished_time;   
                                    $this->accomplished_teacher_id = $result->teacher_id;   
                                    $objectives[]                  = clone $this; 
                                } 
                break;
            case 'curriculum':  $db = DB::prepare('SELECT en.* FROM enablingObjectives AS en 
                                        WHERE en.curriculum_id = ? ORDER by en.terminal_objective_id, en.order_id');
                                $db->execute(array($this->curriculum_id));
                            
                                while($result = $db->fetchObject()) { 
                                    $this->id                      = $result->id;
                                    $this->enabling_objective      = $result->enabling_objective;
                                    $this->description             = $result->description;
                                    $this->curriculum_id           = $result->curriculum_id;
                                    $this->terminal_objective_id   = $result->terminal_objective_id;
                                    $this->order_id                = $result->order_id;
                                    $this->repeat_interval_id      = $result->repeat_interval;
                                    $this->creation_time           = $result->creation_time;
                                    $this->creator_id              = $result->creator_id;      
                                    $objectives[]               = clone $this; 
                                }  
                break;   
             
            
             case 'terminal_objective': $files = new File(); 
                                $db = DB::prepare('SELECT en.* FROM enablingObjectives AS en 
                                    WHERE en.terminal_objective_id = ?
                                    ORDER by en.terminal_objective_id, en.order_id');
                                $db->execute(array($id));
                                while($result = $db->fetchObject()) { 
                                    $this->id                      = $result->id;
                                    $this->enabling_objective      = $result->enabling_objective;
                                    $this->description             = $result->description;
                                    $this->curriculum_id           = $result->curriculum_id;
                                    $this->terminal_objective_id   = $result->terminal_objective_id;
                                    $this->order_id                = $result->order_id;
                                    $this->repeat_interval_id      = $result->repeat_interval;
                                    $this->creation_time           = $result->creation_time;
                                    $this->creator_id              = $result->creator_id;     
                                    $this->files                   = $files->getFiles('enabling_objective', $this->id);
                                    $objectives[]                  = clone $this; 
                                }   
                break;    
            
            case 'course':
            case 'group':       $db = DB::prepare('SELECT en.*, te.terminal_objective, cu.curriculum, ua.status_id, ua.accomplished_time, ua.creator_id AS teacher_id
                                                        FROM enablingObjectives AS en 
                                                        INNER JOIN terminalObjectives AS te ON en.terminal_objective_id = te.id
                                                        INNER JOIN curriculum AS cu ON en.curriculum_id = cu.id 
                                                        LEFT JOIN user_accomplished AS ua ON en.id = ua.enabling_objectives_id AND ua.user_id = ?
                                                        WHERE en.curriculum_id = ?
                                                        ORDER by en.terminal_objective_id, en.order_id');
                                $db->execute(array($USER->id, $id));
                                while($result = $db->fetchObject()) { //Prozentberechnung - Wie viele Teilnehmer (in %) waren erfolgreich
                                    $db_01 = DB::prepare('SELECT COUNT(gr.user_id) AS cntEnroled
                                                        FROM groups_enrolments AS gr, users AS us
                                                        WHERE gr.status = 1
                                                        AND gr.group_id = ?
                                                        AND gr.user_id = us.id
                                                        AND us.role_id = 0');
                                    $db_01->execute(array($group));
                                    $cntEnroled = $db_01->fetchObject();
                                    //Anzahl der Teilnehmer, die das Ziel erfolgreich abgeschlossen haben. 
                                    $db_02 = DB::prepare('SELECT COUNT(ua.enabling_objectives_id) AS anzAccomplished
                                                        FROM user_accomplished AS ua
                                                        INNER JOIN groups_enrolments AS gr ON gr.user_id = ua.user_id 
                                                        INNER JOIN users AS us ON gr.user_id = us.id
                                                        WHERE ua.enabling_objectives_id = ?
                                                        AND gr.group_id = ?
                                                        AND gr.status = 1
                                                        AND ua.status_id = 1
                                                        AND us.role_id = 0');
                                    $db_02->execute(array($result->id, $group));
                                    $anz = $db_02->fetchObject();
                                    $this->id                      = $result->id;
                                    $this->enabling_objective      = $result->enabling_objective;
                                    $this->description             = $result->description;
                                    $this->curriculum_id           = $result->curriculum_id;
                                    $this->terminal_objective_id   = $result->terminal_objective_id;
                                    $this->order_id                = $result->order_id;
                                    $this->repeat_interval_id      = $result->repeat_interval;
                                    $this->creation_time           = $result->creation_time;
                                    $this->creator_id              = $result->creator_id;   
                                    $this->accomplished_status_id  = $result->status_id;   
                                    $this->accomplished_time       = $result->accomplished_time;   
                                    $this->accomplished_teacher_id = $result->teacher_id;   
                                    $this->enroled_users           = $cntEnroled->cntEnroled;   
                                    $this->accomplished_users      = $anz->anzAccomplished;  
                                    if ($cntEnroled->cntEnroled == 0){
                                        $this->accomplished_percent= 0;
                                    } else {
                                        $this->accomplished_percent= round($anz->anzAccomplished/$cntEnroled->cntEnroled*100, 2);     
                                    }
                                    $objectives[]                  = clone $this;     
                                }
                break;
            case 'terminal_objective': //checks if there are enabling objectives under a terminal objective
                                    $db = DB::prepare('SELECT id  FROM enablingObjectives WHERE terminal_objective_id = ?');
                                    if ($db->execute($id)){
                                        return true; 
                                    } else {return false;} 
                break;
            default:
                break;
        }
        if (isset($objectives)){
            return $objectives;
        } else { return false;}
        
    }  
    
    /**
     * change order of objectives
     * @global int $USER
     * @param string $direction 
     */
    public function order($direction = null){
        global $USER;
        if (checkCapabilities('objectives:orderEnablingObjectives', $USER->role_id)){
            switch ($direction) {
                case 'down': if ($this->order_id == 1){
                                // order_id kann nicht kleiner sein
                                } else {
                                    $db = DB::prepare('SELECT id FROM enablingObjectives 
                                                        WHERE terminal_objective_id = ? AND order_id = ?');
                                    $db->execute(array($this->terminal_objective_id, ($this->order_id-1)));
                                    $result = $db->fetchObject();
                                    $db = DB::prepare('UPDATE enablingObjectives SET order_id = ? WHERE id = ?');
                                    $db->execute(array($this->order_id, $result->id));

                                    $db = DB::prepare('UPDATE enablingObjectives SET order_id = ? WHERE id = ?');
                                    $db->execute(array(($this->order_id-1), $this->id));
                                }
                    break;
                case 'up':      $db = DB::prepare('SELECT MAX(order_id) as max FROM enablingObjectives WHERE terminal_objective_id = ?');
                                $db->execute(array($this->terminal_objective_id));
                                $result = $db->fetchObject();
                                if ($this->order_id == $result->max){
                                // order_id darf nicht größer als maximale order_id sein
                                } else {
                                    $db = DB::prepare('SELECT id FROM enablingObjectives WHERE terminal_objective_id = ? AND order_id = ?');
                                    $db->execute(array($this->terminal_objective_id, ($this->order_id+1)));
                                    $result = $db->fetchObject();
                                    $replace_id = $result->id;

                                    $db = DB::prepare('UPDATE enablingObjectives SET order_id = ? WHERE id = ?');
                                    $db->execute(array($this->order_id, $replace_id));

                                    $db = DB::prepare('UPDATE enablingObjectives SET order_id = ? WHERE id = ?');
                                    $db->execute(array(($this->order_id+1), $this->id));
                                }
                    break;

                default:
                    break;
            }     
        }
    }
    
    
    /**
     * get last enabling objectives depending on users accomplished days
     * @global int $USER
     * @return mixed 
     */
    public function getLastEnablingObjectives(){
        global $USER;
        $db = DB::prepare('SELECT ena.*, SUBSTRING(cur.curriculum, 1, 20) AS curriculum, usa.status_id as status_id, 
                            usa.accomplished_time as accomplished_time, usa.creator_id as teacher_id, us.firstname, us.lastname
                        FROM enablingObjectives AS ena, user_accomplished AS usa, curriculum AS cur, users AS us
                        WHERE ena.id = usa.enabling_objectives_id
                        AND us.id = usa.user_id
                        AND ena.curriculum_id = cur.id AND usa.user_id = ? AND usa.status_id = 1
                        AND usa.accomplished_time > DATE_SUB(now(), INTERVAL ? DAY)');
        $db->execute(array($USER->id, $USER->acc_days));
        while($result = $db->fetchObject()) { 
            $this->id                      = $result->id;
            $this->enabling_objective      = $result->enabling_objective;
            $this->description             = $result->description;
            $this->curriculum_id           = $result->curriculum_id;
            $this->curriculum              = $result->curriculum;
            $this->terminal_objective_id   = $result->terminal_objective_id;
            $this->order_id                = $result->order_id;
            $this->repeat_interval_id      = $result->repeat_interval;
            $this->creation_time           = $result->creation_time;
            $this->creator_id              = $result->creator_id;   
            $this->accomplished_status_id  = $result->status_id;   
            $this->accomplished_time       = $result->accomplished_time;   
            $this->accomplished_teacher_id = $result->teacher_id;   
            $this->accomplished_teacher = $result->firstname.' '.$result->lastname;   
            /*$this->enroled_users           = $cntEnroled["cntEnroled"];   
            $this->accomplished_users      = $anz["anzAccomplished"];   
            $this->accomplished_percent    = round($anz["anzAccomplished"]/$cntEnroled["cntEnroled"]*100, 2);   */
            $objectives[]                  = clone $this; 
        }
    if (isset($objectives)){
    } else {
        $objectives = NULL;
        }
    return $objectives;
    }
    
    /**
     * get data for user report
     * @global int $USER
     * @param int $id
     * @return object 
     */
    public function getReport($id = null){     
        $db = DB::prepare('SELECT * FROM user_accomplished WHERE user_id = ? AND status_id = 1 ORDER BY accomplished_time');
        if ($id == null) {
            global $USER;
            $db->execute(array($USER->id));
        } else {
            $db->execute(array($id));
        }
        while($result = $db->fetchObject()) { 
            $this->id                      = $result->enabling_objectives_id;
            $this->accomplished_status_id  = $result->status_id;   
            $this->accomplished_time       = $result->accomplished_time;    
            
            $objectives[]                  = clone $this; 
        }
    if (isset($objectives)){
    } else {
        $objectives = NULL;
        }
    return $objectives;
    }
    
    /**
     * get percentage of completion
     * @param int $cur
     * @param int $id
     * @return int 
     */
    public function getPercentageOfCompletion($cur = null, $id = null){
    $db = DB::prepare('SELECT COUNT(id) FROM enablingObjectives WHERE curriculum_id = ?');
    $db->execute(array($cur));
    $ena_count = $db->fetchColumn();
    
    $db = DB::prepare('SELECT COUNT(en.id) FROM enablingObjectives AS en, user_accomplished AS ua 
        WHERE en.curriculum_id = ? AND ua.user_id = ? AND ua.status_id = 1 AND ua.enabling_objectives_id = en.id');
    $db->execute(array($cur,$id));
    $ena_acc_count =  $db->fetchColumn();
    return round($ena_acc_count/$ena_count*100,2); 
    }
    
    /**
    * get repeat interval 
    * @param int $repeat_id
    * @return array 
    */
    public function getRepeatInterval($repeat_id) {
        $db = DB::prepare('SELECT repeat_interval FROM repeat_interval WHERE id = ?');
        $db->execute(array($repeat_id));
        while($result = $db->fetchObject()) { 
                $value = $result->repeat_interval;
        } 
        if (isset($value)) {    
            return $value;
        }
    }
    
    /**
     * get repeating objectives
     * @return array of EnablingObjective objects|boolean 
     */
    public function getRepeatingObjectives(){
        $db = DB::prepare('SELECT ua.*, ena.repeat_interval 
                        FROM user_accomplished AS ua, enablingObjectives AS ena
                        WHERE ua.status_id <> 2
                        AND ua.enabling_objectives_id = ena.id
                        AND ena.repeat_interval <> -1');
        $db->execute();

        while($result = $db->fetchObject()) { 
            $this->id                       = $result->enabling_objectives_id;
            $this->load();
            $this->repeat_interval          = $result->repeat_interval;
            $this->accomplished_users       = $result->user_id;
            $this->accomplished_status_id   = $result->status_id;
            $this->accomplished_time        = $result->accomplished_time;
            $this->accomplished_teacher_id  = $result->creator_id;
            $objectives[] = clone $this; 
        }
        if (isset($objectives)){
            return $objectives; 
        } else {return false;}  
    }
    
    /**
     * get accomplished users
     * @param int $group
     * @return array 
     */
    public function getAccomplishedUsers($group){
        $db = DB::prepare('SELECT ua.user_id
                              FROM user_accomplished AS ua
                              INNER JOIN groups_enrolments AS gr ON gr.user_id = ua.user_id 
                                    WHERE ua.enabling_objectives_id = ? AND gr.group_id = ?
                                    AND gr.status = 1 AND ua.status_id = 1');
                                    $db->execute(array($this->id, $group));
                                    while($result = $db->fetchObject()) {
                                        $users[] = $result->user_id; 
                                    }
                                    
                                    if (isset($users)){
                                        return $users;
                                    } else {return false;}
    }
    /**
     * set accomplished status of enabling objective in db
     * @global int $USER
     * @param string $dependency
     * @param int $user_id
     * @param int $creator_id
     * @param int $status
     * @return type 
     */
    public function setAccomplishedStatus($dependency = null, $user_id = null, $creator_id = null, $status = 2) {
        global $USER;
        switch ($dependency) {
            case 'cron':    $db = DB::prepare('UPDATE user_accomplished SET status_id = ? WHERE enabling_objectives_id = ?');
                            return $db->execute(array($status, $this->id));
                            break;
            case 'teacher': if(checkCapabilities('objectives:setStatus', $USER->role_id)){
                                $db = DB::prepare('SELECT COUNT(id) FROM user_accomplished WHERE enabling_objectives_id = ? AND user_id = ?');
                                $db->execute(array($this->id, $user_id));
                                if($db->fetchColumn() >= 1) { 
                                    $db = DB::prepare('UPDATE user_accomplished SET status_id = ?, creator_id = ? WHERE enabling_objectives_id = ? AND user_id = ?');
                                    return $db->execute(array($status, $creator_id, $this->id, $user_id));
                                } else {
                                        $db = DB::prepare('INSERT INTO user_accomplished(enabling_objectives_id,user_id,status_id,creator_id) VALUES (?,?,?,?)');
                                        return $db->execute(array($this->id, $user_id, $status, $creator_id));
                                }
                            }
                            break;
            default:        break;
        } 
    }
    
    /**
    * function used during the install process to set up creator id to new admin
    * @return boolean
    */
    public function dedicate(){ // only use during install
        $db = DB::prepare('UPDATE enablingObjectives SET creator_id = ?');
        return $db->execute(array($this->creator_id));
    }
}
?>