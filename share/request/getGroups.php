<?php
/** This file is part of curriculum - http://www.joachimdieterich.de
* 
* @package core
* @filename getGroups.php
* @copyright 2015 Joachim Dieterich
* @author Joachim Dieterich
* @date 2015.11.24 10:12
* @license: 
*
* The MIT License (MIT)
* Copyright (c) 2012 Joachim Dieterich http://www.curriculumonline.de
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
$base_url   = dirname(__FILE__).'/../';
include($base_url.'setup.php');  //Läd Klassen, DB Zugriff und Funktionen
include(dirname(__FILE__).'/../login-check.php');  //check login status and reset idletimer
global $USER;
$USER   = $_SESSION['USER'];

$group  = new Group();
$groups = $group->getGroups('institution', filter_input(INPUT_GET, 'institution_id', FILTER_VALIDATE_INT));

if (filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING)){
    echo '<label>Lerngruppe: </label><select name="'.filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING).'">';
} else {
    echo '<label>Lerngruppe: </label><select name="group">';
}
for($i = 0; $i < count($groups); $i++) {  
  echo  '<option label="'.$groups[$i]->group.'" value="'.$groups[$i]->id.'"';
  if (filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING)){
     if ($groups[$i]->id == filter_input(INPUT_GET, 'group_id', FILTER_VALIDATE_INT)) {
        echo ' selected="selected"'; 
     }
  }
  echo '>'.$groups[$i]->group.'</option>';
}
echo '</select>';