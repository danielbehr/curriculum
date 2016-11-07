<?php
/** This file is part of curriculum - http://www.joachimdieterich.de
* 
* @package core
* @filename f_material.php
* @copyright 2016 Joachim Dieterich
* @author Joachim Dieterich
* @date 2016.04.20 08:57
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
global $USER, $PAGE, $CFG;
$USER       = $_SESSION['USER'];
$edit       = checkCapabilities('file:editMaterial',    $USER->role_id, false); // DELETE / edit anzeigen
$header     = 'Material';

$file       = new File(); 
switch (filter_input(INPUT_GET, 'func', FILTER_UNSAFE_RAW)) {
    case 'ena': $files  = $file->getFiles('enabling_objective', filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT), '', array('externalFiles' => true));
        break;
    case 'ter': $files  = $file->getFiles('terminal_objective', filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT), '', array('externalFiles' => true));
        break;
    case 'id' : $files  = $file->getFiles('id', filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT), '', array('externalFiles' => false, 'user_id' => filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT)));
                $header = 'Lösungen / Dateien des Users';
                $func   = 'solution';
                $edit   = false;    //don't show delete button in solution window
        break;
    default:
        break;
}


$f_content  = null;
$content    = null; 
$m_boxes    = '';

if (!$files){
    $content .= 'Es gibt leider kein Material zum gewählten Lernziel.';
} else {
    
    
    
    $file_context = 1;
    
    for($i = 0; $i < count($files); $i++) {
        /* reset vars */
        $m_footer       = '';
        $m_player       = null;
        $m_icon_class   = null;
        $m_preview      = null;
        $m_delete       = null;
        
        $m_content    = ''; 
        $file_context_count[1] = 0; // counter for file_context 1
        $file_context_count[2] = 0; // counter for file_context 2
        $file_context_count[3] = 0; // counter for file_context 3
        $file_context_count[4] = 0; // counter for file_context 4
        $file_context_count[5] = 0; // counter for file_context 5
        if ($files[$i]->file_context >= $file_context){ 
            switch ($files[$i]->file_context) {
                case 1: $level_header = 'Globale Dateien'; 
                        $file_context_count[1]++; 
                   break;
                case 2: $level_header = 'Dateien meiner Instution(en)'; 
                        $file_context_count[2]++;
                   break;
                case 3: $level_header = 'Dateien meiner Gruppe(n)';
                        $file_context_count[3]++;
                    break;
                case 4: $level_header = 'Meine Dateien'; 
                        $file_context_count[4]++;
                    break;
                case 5: $level_header = 'Externe Medien'; 
                        $file_context_count[5]++;
                    break;
                default: break;
            } $file_context       = $files[$i]->file_context+1; //file_context auf nächstes Level setzen
        }
        
        $m_title    = '';
        $m_url      = '';
        $m_onclick  = '';
        /* Icon */ 
        if ($files[$i]->file_version != false){
            $icon = 0; 
            $f_versions = '';
            foreach ($files[$i]->file_version as $key => $value) {
                foreach ($value as $k => $v) {
                    if ($k == 'filename'){  $filename = $v; }
                    if ($k == 'size')    {      $size = $v; }
                }
                if ($files[$i]->type != 'external'){
                    $f_versions .= '<a class="pull-right" href="'.$CFG->access_file.$files[$i]->context_path.$files[$i]->path.$filename .'" target="_blank">'.translate_size($key).' ('.$size.')</a><br>'; 
                }        
                if ($key == 't'){
                    if ($files[$i]->type == 'external'){
                        $preview =  $filename ;
                    } else {
                        $preview =  $CFG->access_file.$files[$i]->context_path.$files[$i]->path.$filename;
                    }
                    $icon ++;
                }   
            }
        }
        /* . Icon */

        if ($files[$i]->type != 'external'){ $m_onclick      = 'updateFileHits('.$files[$i]->id.')'; }
        $m_title        = $files[$i]->title;
        $m_description  = $files[$i]->description;
        
        switch ($files[$i]->type) {
            case '.url':      $m_url = $files[$i]->path;       
                break;
            case 'external':  $m_url = $files[$i]->filename;
                break;
            case '.mp3':    /* Player*/  
                            $m_player =  '<audio width="100%" controls preload="none">
                                            <source src="'.$CFG->access_file.$files[$i]->context_path.$files[$i]->path.$files[$i]->filename.'" type="audio/mpeg" />
                                        Your browser does not support the audio element.</audio>';
                break;
            case '.mp4':    /* Player*/ 
            case '.mov':    $m_player =  '<video width="100%" controls>
                                            <source src="'.$CFG->access_file.$files[$i]->context_path.$files[$i]->path.$files[$i]->filename.'&video=true"  type="video/mp4"/>
                                            <!--source src="http://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4" /-->
                                        Your browser does not support the video element.</video>';
                break;
            default:        $m_url = $CFG->access_file.$files[$i]->context_path.$files[$i]->path. $files[$i]->filename;
                break;
        }
        
        if (checkCapabilities('file:showHits', $USER->role_id, false)  && $files[$i]->type != 'external'){ // für Externe Medien können keine Zugriffszahlen erfasst werden
            $m_hits     = $files[$i]->hits;
        }  
            
        /*  Lizenzform */
        $license = new License();
        if ($files[$i]->license != '' && isset($files[$i]->license)){
            if ($files[$i]->file_context == 5){
                $license->license = $files[$i]->license;  //Externes Medium
            } else { 
                $license->get($files[$i]->license);
            }
        } /* . Lizenzform */
    
        /* Materialbox */
        $m_id = $files[$i]->id;
        if (isset($preview)){
            $m_preview = $preview;
        } else {
            $m_icon_class = resolveFileType($files[$i]->type);
        }     
        
        if (checkCapabilities('file:editMaterial', $USER->role_id, false) && $edit && ($files[$i]->type != 'external')){
            $m_delete = true;
        }

        /* Material footer */
        /* End Material footer*/
        if ($files[$i]->type != 'external'){

            $m_footer .= '<div class="info-box-text" style="padding-top:10px;white-space:normal; text-transform:none; display:block;">
                           <div class="row">
                    <div class="col-xs-4 text-center" style="border-right: 1px solid #f4f4f4">
                      <div id="sparkline-1"></div><div class="knob-label">';
            if (isset($license->license)){ $m_footer .= $license->license; }
            $m_footer .= '</div></div><!-- ./col -->
                
                    <div class="col-xs-3 text-center" style="border-right: 1px solid #f4f4f4">
                      <div id="sparkline-2"></div><div class="knob-label">';
            if (isset($m_hits)){ $m_footer .= ' '.$m_hits.' Aufrufe'; }
            $m_footer .= '</div>
                    </div><!-- ./col -->
                    
                    <div class="col-xs-4 text-center">
                      <div id="sparkline-3"></div>
                      <div class="knob-label">';
            if (isset($f_versions)){ $m_footer .= $f_versions; }
            $m_footer .= '</div>
                      </div><!-- ./col -->
                      </div><!-- ./row -->
                      </div><!-- ./info-box-text -->';
            
        } else { // Bei Externen Medien nur die Lizenz zeigen
            $m_footer .= '<div class="info-box-text">
                           <div class="row">   
                            <div class="col-xs-12 text-center">
                                <div class="knob-label" style="padding-top:10px;white-space:normal; text-transform:none; display:block;">';
            if (isset($license->license)){ $m_footer .= $license->license; }
            $m_footer .= '     </div>
                            </div><!-- ./col -->
                           </div><!-- ./row -->
                          </div><!-- ./info-box-text -->';
        }   
        
        $m_boxes .= Form::info_box(array('id'          => $m_id,
                                         'preview'     => $m_preview,
                                         'icon_class'  => $m_icon_class,
                                         'delete'      => $m_delete,
                                         'url'         => $m_url,
                                         'onclick'     => $m_onclick,
                                         'title'       => $m_title,
                                         'description' => $m_description,
                                         'player'      => $m_player,
                                         'content'     => $m_content, 
                                         'footer'      => $m_footer));
        unset($m_id, $preview, $m_preview, $m_icon_class, $m_delete, $m_url, $m_onclick, $m_title, $m_description, $m_player, $m_content, $m_footer, $m_hits, $f_versions, $license);
        
        
        /* Tab header */
    $content .= '<div class="nav-tabs-custom">';
    $content .= '<ul class="nav nav-tabs">
                 <li class="active"><a href="#f_context_1" data-toggle="tab" aria-expanded="false" >Global <span class="label label-primary">'.$file_context_count[1].'</span></a></li>
                 <li class=""><a href="#f_context_2" data-toggle="tab" aria-expanded="false" >Institution <span class="label label-primary">'.$file_context_count[2].'</span></a></li>
                 <li class=""><a href="#f_context_3" data-toggle="tab" aria-expanded="false" >Gruppe <span class="label label-primary">'.$file_context_count[3].'</span></a></li>
                 <li class=""><a href="#f_context_4" data-toggle="tab" aria-expanded="false" >Persönlich <span class="label label-primary">'.$file_context_count[4].'</span></a></li>
                 <li class=""><a href="#f_context_5" data-toggle="tab" aria-expanded="false" >Externe Medien <span class="label label-primary">'.$file_context_count[5].'</span></a></li>';
    $content .='</ul>';
    /* tab content*/
    $content .='<div class="tab-content">';
        
        /* context box */   
        /* generate tabs for each file context*/
        $close = false;
        if (count($files) == ($i+1)){ 
            $close = true;
        } else {
            if ($files[$i+1]->file_context >= $file_context){ $close = true; }  
        }
        
        if ($close == true AND $m_boxes != ''){ //close file_context box // only generate tab-pane when there are files (m_boxes)
            $content   .='<div class="tab-pane';
            if (($file_context-1) == 1){
                $content   .=' active';
            }
            $content   .='" id="f_context_'.($file_context-1).'">'.$m_boxes.'</div><!-- /.tab-pane -->';
            unset($m_boxes);
            $m_boxes = '';
        }

    }
    $content   .='</div><!-- /.tab-content -->
                        </div><!-- /.nav-tab-custom -->';
}


if (filter_input(INPUT_GET, 'target', FILTER_SANITIZE_STRING)){
    $target = filter_input(INPUT_GET, 'target', FILTER_SANITIZE_STRING);
} else { $target = 'popup'; }
$html     = Form::modal(array('target' => $target,
                                'title' => $header, 
                            'content' => $content, 
                            'background' => '#ecf0f5'));  
echo json_encode(array('html'=> $html, 'target' => $target));