<?php
/** This file is part of curriculum - http://www.joachimdieterich.de
* 
* @package core
* @filename Backup.php
* @copyright 2013 Joachim Dieterich
* @author Joachim Dieterich
* @date 2013.03.08 13:26
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
global $CFG, $USER, $TEMPLATE, $PAGE;
$TEMPLATE->assign('breadcrumb',  array('Sicherungen' => 'index.php?action=backup'));
$TEMPLATE->assign('page_title', 'Sicherungen erstellen');

$backups            = new File();

/* load backups and courses */
if (checkCapabilities('backup:getAllBackups', $USER->role_id, false)) {                          // Administrators
    $backup_list    = $backups->getFiles('context', 8, 'fileBackupPaginator');  
} else if (checkCapabilities('backup:getMyBackups', $USER->role_id, false)) {                    // Teacher and Tutor
    $backup_list    = $backups->getFiles('backup',  $USER->id, 'fileBackupPaginator');
} 

$TEMPLATE->assign('web_backup_path', $CFG->web_backup_path);  

$p_options = array('download'     => array('href' => "../share/accessfile.php?id=__id__",
                                           'icon' => 'fa fa-download'),
                   'xml'          => array('href' => "../share/accessfile.php?id=__id__&type=xml",
                                           'icon' => 'fa fa-file-code-o')); 
$p_config = array('id' => 'checkbox',
                  'title'         => 'Titel', 
                  'description'   => 'Beschreibung',
                  'creation_time' => 'Datum',
                  'author'        => 'Erstellt durch',
                  'p_options'     => $p_options);
setPaginator('fileBackupPaginator', $TEMPLATE, $backup_list, 'fb_val', 'index.php?action=backup', $p_config);      